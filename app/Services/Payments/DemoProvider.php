<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\Request;

/**
 * Development/demo provider. No money is taken and no card details are
 * collected. Every screen that uses it is labelled "Demonstration mode".
 * Completing a demo checkout requires an authenticated, CSRF-protected POST
 * by the order owner while APP_DEMO_MODE is enabled.
 */
final class DemoProvider implements PaymentProvider
{
    public function name(): string
    {
        return 'demo';
    }

    public function isConfigured(): bool
    {
        return (bool) config('app.demo_mode');
    }

    public function createCheckout(array $order, array $user, string $description): string
    {
        $session = 'demo_' . bin2hex(random_bytes(12));
        db()->update('orders', ['provider_session' => $session], ['id' => (int) $order['id']]);
        return route('checkout.demo', ['reference' => $order['reference']]);
    }

    public function parseWebhook(Request $request): ?array
    {
        return null;
    }
}
