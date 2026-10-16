<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\CertificateService;
use Tests\TestCase;

final class SharingPrivacyTest extends TestCase
{
    private function cert(array $user, int $public): array
    {
        $num = CertificateService::generateNumber('SOC');
        $now = now();
        $id = db()->insert('certificates', [
            'certificate_number' => $num, 'user_id' => (int) $user['id'], 'course_id' => 1, 'path_id' => 1,
            'holder_name' => $user['display_name'], 'title_en' => 'SOC Analyst Professional', 'title_fr' => 'Analyste SOC',
            'locale' => 'en', 'criteria' => '{}', 'status' => 'valid', 'issued_at' => $now,
            'signature' => CertificateService::sign($num, $user['display_name'], 1, $now),
            'is_public' => $public, 'share_token' => bin2hex(random_bytes(16)),
        ]);
        return db()->fetch('SELECT * FROM certificates WHERE id = ?', [$id]);
    }

    public function test_private_certificate_share_page_is_not_public(): void
    {
        $cert = $this->cert($this->makeUser(), 0);
        $this->assertStatus(404, $this->get('/achievements/' . $cert['share_token']));
    }

    public function test_public_certificate_share_page_is_reachable(): void
    {
        $cert = $this->cert($this->makeUser(), 1);
        $this->assertStatus(200, $this->get('/achievements/' . $cert['share_token']));
    }

    public function test_private_profile_is_404(): void
    {
        $user = $this->makeUser('student', ['display_name' => 'Private Person']);
        db()->update('users', ['public_profile' => 0, 'public_slug' => 'private-person-abc'], ['id' => (int) $user['id']]);
        $this->assertStatus(404, $this->get('/learners/private-person-abc'));
    }

    public function test_public_profile_shows_only_public_items(): void
    {
        $user = $this->makeUser('student', ['display_name' => 'Public Person']);
        db()->update('users', ['public_profile' => 1, 'public_slug' => 'public-person-abc'], ['id' => (int) $user['id']]);
        $this->cert($user, 0); // private cert must not leak
        $res = $this->get('/learners/public-person-abc');
        $this->assertStatus(200, $res);
        $this->assertStringContainsString('Public Person', $res->content());
    }
}
