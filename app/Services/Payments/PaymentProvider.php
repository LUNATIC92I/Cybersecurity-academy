<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\Request;

/**
 * Contract for payment providers. The course system only talks to
 * PaymentService, so a provider can be added without touching courses.
 */
interface PaymentProvider
{
    public function name(): string;

    /** Whether this provider is configured with the credentials it needs. */
    public function isConfigured(): bool;

    /**
     * Create a hosted checkout and return the URL to redirect the buyer to.
     * Must store the provider's session identifier on the order.
     */
    public function createCheckout(array $order, array $user, string $description): string;

    /**
     * Verify an inbound webhook (signature, freshness) and return a
     * normalised event, or null if the request is not authentic.
     *
     * @return array{id: string, type: string, session: ?string, transaction: ?string, amount: ?int, currency: ?string, paid: bool}|null
     */
    public function parseWebhook(Request $request): ?array;
}
