<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\PaymentService;
use Tests\TestCase;

final class PaymentTest extends TestCase
{
    public function test_order_is_only_fulfilled_after_verified_payment(): void
    {
        $user = $this->actingAs($this->makeUser());
        $course = db()->fetch("SELECT * FROM courses WHERE slug = 'penetration-testing-professional'");
        $order = PaymentService::createOrder((int) $user['id'], 'course', (int) $course['id'], null);
        db()->update('orders', ['provider_session' => 'sess_test_123'], ['id' => (int) $order['id']]);
        // Before payment: no access, order pending.
        $this->assertNull(PaymentService::provider() ? \App\Services\AccessService::accessSource((int) $user['id'], $course) : 'x');
        $this->assertSame('pending', db()->value('SELECT status FROM orders WHERE id = ?', [(int) $order['id']]));

        // Verified payment with correct amount fulfils the order.
        $ok = PaymentService::markPaid('demo', 'sess_test_123', 'txn_1', (int) $order['total_cents'], $order['currency']);
        $this->assertTrue($ok);
        $this->assertSame('paid', db()->value('SELECT status FROM orders WHERE id = ?', [(int) $order['id']]));
        $this->assertNotNull(\App\Services\AccessService::accessSource((int) $user['id'], $course), 'Access must be granted after payment.');
    }

    public function test_amount_mismatch_is_rejected(): void
    {
        $user = $this->actingAs($this->makeUser());
        $course = db()->fetch("SELECT * FROM courses WHERE slug = 'penetration-testing-professional'");
        $order = PaymentService::createOrder((int) $user['id'], 'course', (int) $course['id'], null);
        db()->update('orders', ['provider_session' => 'sess_bad'], ['id' => (int) $order['id']]);
        $ok = PaymentService::markPaid('demo', 'sess_bad', 'txn_2', 1, $order['currency']); // wrong amount
        $this->assertFalse($ok);
        $this->assertSame('failed', db()->value('SELECT status FROM orders WHERE id = ?', [(int) $order['id']]));
    }

    public function test_markpaid_is_idempotent(): void
    {
        $user = $this->actingAs($this->makeUser());
        $course = db()->fetch("SELECT * FROM courses WHERE slug = 'penetration-testing-professional'");
        $order = PaymentService::createOrder((int) $user['id'], 'course', (int) $course['id'], null);
        db()->update('orders', ['provider_session' => 'sess_idem'], ['id' => (int) $order['id']]);
        PaymentService::markPaid('demo', 'sess_idem', 'txn_3', (int) $order['total_cents'], $order['currency']);
        PaymentService::markPaid('demo', 'sess_idem', 'txn_3', (int) $order['total_cents'], $order['currency']);
        $this->assertSame(1, (int) db()->value('SELECT COUNT(*) FROM payments WHERE order_id = ?', [(int) $order['id']]));
    }

    public function test_browser_return_page_does_not_mark_paid(): void
    {
        $user = $this->actingAs($this->makeUser());
        $course = db()->fetch("SELECT * FROM courses WHERE slug = 'penetration-testing-professional'");
        $order = PaymentService::createOrder((int) $user['id'], 'course', (int) $course['id'], null);
        $res = $this->get('/checkout/return/' . $order['reference']);
        $this->assertStatus(200, $res);
        $this->assertSame('pending', db()->value('SELECT status FROM orders WHERE id = ?', [(int) $order['id']]), 'Visiting the return page must never mark an order paid.');
    }
}
