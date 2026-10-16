<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\PaymentService;
use PHPUnit\Framework\TestCase;

final class PaymentDiscountTest extends TestCase
{
    public function test_percentage_discount(): void
    {
        $this->assertSame(725, PaymentService::discount(2900, ['percent_off' => 25, 'amount_off_cents' => null]));
    }

    public function test_fixed_discount_capped_at_price(): void
    {
        $this->assertSame(1000, PaymentService::discount(1000, ['percent_off' => null, 'amount_off_cents' => 5000]));
    }

    public function test_no_coupon_is_zero(): void
    {
        $this->assertSame(0, PaymentService::discount(2900, null));
    }

    public function test_percentage_is_capped_at_100(): void
    {
        $this->assertSame(2900, PaymentService::discount(2900, ['percent_off' => 150, 'amount_off_cents' => null]));
    }
}
