<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\CertificateService;
use Tests\TestCase;

final class CertificateTest extends TestCase
{
    private function issueCert(array $user): array
    {
        $course = db()->fetch("SELECT * FROM courses WHERE slug = 'soc-analyst-professional'");
        $num = CertificateService::generateNumber('SOC');
        $now = now();
        $id = db()->insert('certificates', [
            'certificate_number' => $num, 'user_id' => (int) $user['id'], 'course_id' => (int) $course['id'], 'path_id' => 1,
            'holder_name' => $user['display_name'], 'title_en' => 'SOC Analyst Professional', 'title_fr' => 'Analyste SOC Professionnel',
            'path_name_en' => 'SOC Analyst', 'path_name_fr' => 'Analyste SOC', 'locale' => 'en',
            'criteria' => json_encode(['lessons_completed' => 7, 'labs_passed' => 2, 'exam_score' => 90, 'exam_required' => 70]),
            'status' => 'valid', 'issued_at' => $now,
            'signature' => CertificateService::sign($num, $user['display_name'], (int) $course['id'], $now),
            'is_public' => 0, 'share_token' => bin2hex(random_bytes(16)),
        ]);
        return db()->fetch('SELECT * FROM certificates WHERE id = ?', [$id]);
    }

    public function test_valid_certificate_verifies(): void
    {
        $cert = $this->issueCert($this->makeUser());
        $result = CertificateService::verify($cert['certificate_number']);
        $this->assertSame('valid', $result['state']);
    }

    public function test_tampered_certificate_is_detected(): void
    {
        $cert = $this->issueCert($this->makeUser());
        db()->update('certificates', ['holder_name' => 'Someone Else'], ['id' => (int) $cert['id']]);
        $result = CertificateService::verify($cert['certificate_number']);
        $this->assertSame('tampered', $result['state']);
    }

    public function test_unknown_number_is_invalid(): void
    {
        $this->assertSame('invalid', CertificateService::verify('CA-SOC-2026-DEADBEEF')['state']);
        $this->assertSame('invalid', CertificateService::verify('not-a-number')['state']);
    }

    public function test_revocation_shows_on_verification(): void
    {
        $admin = $this->makeUser('super_admin');
        $cert = $this->issueCert($this->makeUser());
        CertificateService::revoke((int) $cert['id'], 'Issued in error', (int) $admin['id']);
        $this->assertSame('revoked', CertificateService::verify($cert['certificate_number'])['state']);
        $this->assertSame('revoked', db()->value('SELECT status FROM certificates WHERE id = ?', [(int) $cert['id']]));
        // Audit event recorded.
        $this->assertNotNull(db()->fetch('SELECT id FROM certificate_events WHERE certificate_id = ? AND event = "revoked"', [(int) $cert['id']]));
    }

    public function test_public_verification_page_renders(): void
    {
        $cert = $this->issueCert($this->makeUser());
        $res = $this->get('/verify/' . $cert['certificate_number']);
        $this->assertStatus(200, $res);
        $this->assertStringContainsString($cert['certificate_number'], $res->content());
    }

    public function test_pdf_is_generated(): void
    {
        $cert = $this->issueCert($this->makeUser());
        $pdf = CertificateService::pdf($cert, 'en');
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertGreaterThan(5000, strlen($pdf));
    }
}
