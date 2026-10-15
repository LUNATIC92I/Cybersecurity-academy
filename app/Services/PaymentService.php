<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Payments\DemoProvider;
use App\Services\Payments\PaymentProvider;
use App\Services\Payments\StripeProvider;

/**
 * Orders, coupons and fulfilment. Prices are always computed on the server;
 * an order becomes "paid" only through markPaid(), which is called by a
 * verified provider webhook (or the demo provider's protected action) —
 * never by a browser redirect to a success page.
 */
final class PaymentService
{
    public static function provider(): PaymentProvider
    {
        return match (config('payments.provider')) {
            'stripe' => new StripeProvider((array) config('payments.stripe')),
            default => new DemoProvider(),
        };
    }

    public static function isLive(): bool
    {
        $p = self::provider();
        return $p->name() !== 'demo' && $p->isConfigured();
    }

    /**
     * @return array{item: array, description: string, price: int, currency: string}|null
     */
    public static function resolveItem(string $type, int $id): ?array
    {
        if ($type === 'plan') {
            $plan = db()->fetch('SELECT * FROM plans WHERE id = ? AND is_active = 1 AND is_contact_sales = 0 AND price_cents > 0', [$id]);
            if (!$plan) {
                return null;
            }
            $name = db()->value('SELECT name FROM plan_translations WHERE plan_id = ? AND locale = ?', [$id, locale()])
                ?? db()->value('SELECT name FROM plan_translations WHERE plan_id = ? AND locale = "en"', [$id]);
            return ['item' => $plan, 'description' => 'Cybersecurity-Academy — ' . $name, 'price' => (int) $plan['price_cents'], 'currency' => $plan['currency']];
        }
        if ($type === 'course') {
            $course = db()->fetch('SELECT * FROM courses WHERE id = ? AND status = "published" AND access_level = "premium" AND price_cents > 0', [$id]);
            if (!$course) {
                return null;
            }
            $title = db()->value('SELECT title FROM course_translations WHERE course_id = ? AND locale = "en"', [$id]);
            return ['item' => $course, 'description' => (string) $title, 'price' => (int) $course['price_cents'], 'currency' => $course['currency']];
        }
        return null;
    }

    public static function findCoupon(string $code): ?array
    {
        $code = strtoupper(trim($code));
        if ($code === '' || !preg_match('/^[A-Z0-9_-]{3,40}$/', $code)) {
            return null;
        }
        return db()->fetch(
            'SELECT * FROM coupons WHERE code = ? AND is_active = 1
               AND (expires_at IS NULL OR expires_at > UTC_TIMESTAMP())
               AND (max_redemptions IS NULL OR redeemed_count < max_redemptions)',
            [$code]
        );
    }

    public static function discount(int $price, ?array $coupon): int
    {
        if (!$coupon) {
            return 0;
        }
        $discount = $coupon['percent_off'] !== null
            ? (int) round($price * min(100, (int) $coupon['percent_off']) / 100)
            : (int) $coupon['amount_off_cents'];
        return max(0, min($price, $discount));
    }

    public static function createOrder(int $userId, string $type, int $itemId, ?string $couponCode): array
    {
        $resolved = self::resolveItem($type, $itemId);
        if (!$resolved) {
            throw new \DomainException('invalid_item');
        }
        $coupon = $couponCode ? self::findCoupon($couponCode) : null;
        if ($couponCode && !$coupon) {
            throw new \DomainException('invalid_coupon');
        }
        $discount = self::discount($resolved['price'], $coupon);
        $id = db()->insert('orders', [
            'reference' => strtoupper(bin2hex(random_bytes(10))),
            'user_id' => $userId,
            'item_type' => $type,
            'item_id' => $itemId,
            'subtotal_cents' => $resolved['price'],
            'discount_cents' => $discount,
            'total_cents' => $resolved['price'] - $discount,
            'currency' => $resolved['currency'],
            'coupon_id' => $coupon['id'] ?? null,
            'status' => 'pending',
            'provider' => self::provider()->name(),
        ]);
        return db()->fetch('SELECT * FROM orders WHERE id = ?', [$id]) + ['description' => $resolved['description']];
    }

    /**
     * Mark an order paid after server-side verification, idempotently, and
     * fulfil it. Amount and currency must match the order exactly.
     */
    public static function markPaid(string $provider, ?string $session, string $transactionId, ?int $amount, ?string $currency): bool
    {
        return db()->transaction(function () use ($provider, $session, $transactionId, $amount, $currency) {
            $order = db()->fetch('SELECT * FROM orders WHERE provider = ? AND provider_session = ? FOR UPDATE', [$provider, $session]);
            if (!$order) {
                app('logger')->warning('Payment for unknown order', ['provider' => $provider]);
                return false;
            }
            if ($order['status'] === 'paid') {
                return true;
            }
            if ($order['status'] !== 'pending'
                || ($amount !== null && $amount !== (int) $order['total_cents'])
                || ($currency !== null && strtoupper($currency) !== strtoupper($order['currency']))) {
                app('logger')->warning('Payment verification mismatch', ['order' => $order['reference']]);
                db()->update('orders', ['status' => 'failed'], ['id' => (int) $order['id']]);
                return false;
            }
            db()->insert('payments', [
                'order_id' => (int) $order['id'],
                'provider' => $provider,
                'provider_transaction_id' => $transactionId,
                'amount_cents' => (int) $order['total_cents'],
                'currency' => $order['currency'],
                'status' => 'succeeded',
                'verified_at' => now(),
            ]);
            db()->update('orders', ['status' => 'paid', 'paid_at' => now()], ['id' => (int) $order['id']]);
            if ($order['coupon_id']) {
                db()->query('UPDATE coupons SET redeemed_count = redeemed_count + 1 WHERE id = ?', [(int) $order['coupon_id']]);
            }
            self::fulfil($order);
            AuditLogger::log('order.paid', 'order', $order['reference'], ['provider' => $provider, 'amount' => (int) $order['total_cents']], (int) $order['user_id']);
            NotificationService::notify((int) $order['user_id'], 'order_paid', ['reference' => $order['reference']], route('dashboard.billing'));
            return true;
        });
    }

    private static function fulfil(array $order): void
    {
        $userId = (int) $order['user_id'];
        if ($order['item_type'] === 'plan') {
            $plan = db()->fetch('SELECT * FROM plans WHERE id = ?', [(int) $order['item_id']]);
            $interval = $plan['billing_interval'] === 'year' ? '+1 year' : '+1 month';
            $current = AccessService::activeSubscription($userId);
            $start = $current && $current['current_period_end'] ? $current['current_period_end'] : now();
            db()->insert('subscriptions', [
                'user_id' => $userId,
                'plan_id' => (int) $plan['id'],
                'status' => 'active',
                'provider' => $order['provider'],
                'provider_reference' => $order['reference'],
                'current_period_start' => now(),
                'current_period_end' => gmdate('Y-m-d H:i:s', strtotime($start . ' UTC ' . $interval)),
            ]);
        } elseif ($order['item_type'] === 'course') {
            db()->insert('access_grants', [
                'user_id' => $userId,
                'course_id' => (int) $order['item_id'],
                'source' => 'purchase',
                'reason' => 'Order ' . $order['reference'],
            ]);
            $course = db()->fetch('SELECT * FROM courses WHERE id = ?', [(int) $order['item_id']]);
            if ($course) {
                AccessService::enroll($userId, $course);
            }
        }
    }
}
