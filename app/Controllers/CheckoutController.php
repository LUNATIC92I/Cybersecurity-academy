<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Payments\StripeProvider;
use App\Services\PaymentService;

final class CheckoutController extends Controller
{
    public function create(Request $request): Response
    {
        $data = $this->validate($request, [
            'item_type' => 'required|in:plan,course',
            'item_id' => 'required|integer',
            'coupon' => 'max:40',
        ]);
        $this->throttle('checkout:' . $this->userId(), 20, 3600);
        $provider = PaymentService::provider();
        if (!$provider->isConfigured()) {
            flash('error', __('checkout.unconfigured'));
            return redirect(route('pricing'));
        }
        try {
            $order = PaymentService::createOrder($this->userId(), $data['item_type'], (int) $data['item_id'], $data['coupon'] ?: null);
            $url = $provider->createCheckout($order, $this->user(), $order['description']);
        } catch (\DomainException $e) {
            flash('error', __('checkout.error.' . $e->getMessage()));
            return redirect(route('pricing'));
        } catch (\RuntimeException) {
            flash('error', __('checkout.error.provider'));
            return redirect(route('pricing'));
        }
        return redirect($url);
    }

    public function demo(Request $request): Response
    {
        $order = $this->ownOrder((string) $request->param('reference'));
        if ($order['provider'] !== 'demo' || !config('app.demo_mode')) {
            throw HttpException::notFound();
        }
        return view('checkout/demo', [
            'order' => $order,
            'meta' => $this->meta(__('checkout.demo_title')) + ['robots' => 'noindex'],
        ]);
    }

    public function demoComplete(Request $request): Response
    {
        $order = $this->ownOrder((string) $request->param('reference'));
        if ($order['provider'] !== 'demo' || !config('app.demo_mode') || $order['status'] !== 'pending') {
            throw HttpException::forbidden();
        }
        PaymentService::markPaid('demo', $order['provider_session'], 'demo_txn_' . $order['reference'], (int) $order['total_cents'], $order['currency']);
        return redirect(route('checkout.return', ['reference' => $order['reference']]));
    }

    /**
     * Landing page after the provider redirect. It only displays the order
     * status; it never marks anything as paid.
     */
    public function returned(Request $request): Response
    {
        $order = $this->ownOrder((string) $request->param('reference'));
        return view('checkout/return', [
            'order' => $order,
            'meta' => $this->meta(__('checkout.return_title')) + ['robots' => 'noindex'],
        ]);
    }

    public function stripeWebhook(Request $request): Response
    {
        $provider = PaymentService::provider();
        if (!$provider instanceof StripeProvider || !$provider->isConfigured()) {
            return Response::json(['error' => 'not_configured'], 404);
        }
        $event = $provider->parseWebhook($request);
        if ($event === null) {
            app('logger')->warning('Rejected webhook with invalid signature', ['ip' => $request->ip()]);
            return Response::json(['error' => 'invalid_signature'], 400);
        }
        $inserted = db()->query(
            'INSERT IGNORE INTO webhook_events (provider, event_id, event_type) VALUES (?, ?, ?)',
            ['stripe', $event['id'], $event['type']]
        )->rowCount();
        if ($inserted === 0) {
            return Response::json(['received' => true, 'duplicate' => true]);
        }
        if ($event['paid'] && $event['session']) {
            PaymentService::markPaid('stripe', $event['session'], (string) $event['transaction'], $event['amount'], $event['currency']);
        }
        return Response::json(['received' => true]);
    }

    private function ownOrder(string $reference): array
    {
        $order = db()->fetch('SELECT * FROM orders WHERE reference = ? AND user_id = ?', [$reference, $this->userId()]);
        if (!$order) {
            throw HttpException::notFound();
        }
        return $order;
    }
}
