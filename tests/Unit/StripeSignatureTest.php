<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Payments\StripeProvider;
use PHPUnit\Framework\TestCase;

final class StripeSignatureTest extends TestCase
{
    private function sign(string $payload, string $secret, int $t): string
    {
        return 't=' . $t . ',v1=' . hash_hmac('sha256', $t . '.' . $payload, $secret);
    }

    public function test_valid_signature_passes(): void
    {
        $payload = '{"id":"evt_1","type":"checkout.session.completed"}';
        $secret = 'whsec_test';
        $now = 1_700_000_000;
        $header = $this->sign($payload, $secret, $now);
        $this->assertTrue(StripeProvider::verifySignature($payload, $header, $secret, $now));
    }

    public function test_tampered_payload_fails(): void
    {
        $secret = 'whsec_test';
        $now = 1_700_000_000;
        $header = $this->sign('{"a":1}', $secret, $now);
        $this->assertFalse(StripeProvider::verifySignature('{"a":2}', $header, $secret, $now));
    }

    public function test_old_timestamp_fails(): void
    {
        $payload = '{"a":1}';
        $secret = 'whsec_test';
        $header = $this->sign($payload, $secret, 1_700_000_000);
        $this->assertFalse(StripeProvider::verifySignature($payload, $header, $secret, 1_700_000_000 + 4000));
    }

    public function test_wrong_secret_fails(): void
    {
        $payload = '{"a":1}';
        $now = 1_700_000_000;
        $header = $this->sign($payload, 'whsec_real', $now);
        $this->assertFalse(StripeProvider::verifySignature($payload, $header, 'whsec_other', $now));
    }

    public function test_empty_header_or_secret_fails(): void
    {
        $this->assertFalse(StripeProvider::verifySignature('{}', '', 'whsec', time()));
        $this->assertFalse(StripeProvider::verifySignature('{}', 't=1,v1=abc', '', time()));
    }
}
