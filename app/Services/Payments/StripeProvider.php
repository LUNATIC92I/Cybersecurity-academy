<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\Request;

/**
 * Stripe Checkout integration using Stripe's REST API directly.
 *
 * Status: implemented against Stripe's documented API and webhook signature
 * scheme, covered by unit tests for signature verification. It has NOT been
 * exercised against a live Stripe account in this repository; configure
 * test-mode keys and run an end-to-end checkout before going live.
 */
final class StripeProvider implements PaymentProvider
{
    private const TOLERANCE_SECONDS = 300;

    public function __construct(private readonly array $config)
    {
    }

    public function name(): string
    {
        return 'stripe';
    }

    public function isConfigured(): bool
    {
        return ($this->config['secret_key'] ?? '') !== '' && ($this->config['webhook_secret'] ?? '') !== '';
    }

    public function createCheckout(array $order, array $user, string $description): string
    {
        $payload = [
            'mode' => 'payment',
            'client_reference_id' => $order['reference'],
            'customer_email' => $user['email'],
            'success_url' => absolute_route('checkout.return', ['reference' => $order['reference']]),
            'cancel_url' => absolute_route('pricing'),
            'line_items[0][quantity]' => 1,
            'line_items[0][price_data][currency]' => strtolower($order['currency']),
            'line_items[0][price_data][unit_amount]' => (int) $order['total_cents'],
            'line_items[0][price_data][product_data][name]' => mb_substr($description, 0, 120),
            'metadata[order_reference]' => $order['reference'],
        ];
        $response = $this->request('POST', '/checkout/sessions', $payload);
        if (empty($response['id']) || empty($response['url'])) {
            throw new \RuntimeException('Stripe checkout session could not be created.');
        }
        db()->update('orders', ['provider_session' => $response['id']], ['id' => (int) $order['id']]);
        return (string) $response['url'];
    }

    public function parseWebhook(Request $request): ?array
    {
        $payload = $request->rawBody();
        $header = (string) $request->header('Stripe-Signature');
        if (!self::verifySignature($payload, $header, (string) $this->config['webhook_secret'], time())) {
            return null;
        }
        $event = json_decode($payload, true);
        if (!is_array($event) || empty($event['id']) || empty($event['type'])) {
            return null;
        }
        $object = $event['data']['object'] ?? [];
        return [
            'id' => (string) $event['id'],
            'type' => (string) $event['type'],
            'session' => isset($object['id']) ? (string) $object['id'] : null,
            'transaction' => isset($object['payment_intent']) ? (string) $object['payment_intent'] : (isset($object['id']) ? (string) $object['id'] : null),
            'amount' => isset($object['amount_total']) ? (int) $object['amount_total'] : null,
            'currency' => isset($object['currency']) ? strtoupper((string) $object['currency']) : null,
            'paid' => $event['type'] === 'checkout.session.completed' && ($object['payment_status'] ?? '') === 'paid',
        ];
    }

    /**
     * Stripe-Signature: t=timestamp,v1=hex(HMAC_SHA256(secret, "t.payload"))
     */
    public static function verifySignature(string $payload, string $header, string $secret, int $now): bool
    {
        if ($secret === '' || $header === '') {
            return false;
        }
        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $header) as $part) {
            [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
            if ($k === 't' && ctype_digit($v)) {
                $timestamp = (int) $v;
            } elseif ($k === 'v1') {
                $signatures[] = $v;
            }
        }
        if ($timestamp === null || !$signatures || abs($now - $timestamp) > self::TOLERANCE_SECONDS) {
            return false;
        }
        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) {
                return true;
            }
        }
        return false;
    }

    private function request(string $method, string $path, array $data): array
    {
        $ch = curl_init(rtrim((string) $this->config['api_base'], '/') . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_USERPWD => $this->config['secret_key'] . ':',
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Stripe-Version: 2024-06-20'],
        ]);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $status >= 400) {
            app('logger')->error('Stripe API error', ['status' => $status]);
            throw new \RuntimeException('Payment provider request failed.');
        }
        return json_decode((string) $body, true) ?: [];
    }
}
