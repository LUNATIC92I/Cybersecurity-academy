<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class AuthTest extends TestCase
{
    public function test_registration_creates_student_and_signs_in(): void
    {
        $res = $this->post('/register', [
            'display_name' => 'New Learner',
            'email' => 'new@test.local',
            'password' => 'Strong-Passw0rd',
            'password_confirmation' => 'Strong-Passw0rd',
            'locale' => 'en',
            'accept_terms' => '1',
        ]);
        $this->assertStatus(302, $res);
        $this->assertSame('/dashboard', $res->getHeader('Location'));
        $this->assertTrue(auth()->check());
        $this->assertTrue(auth()->hasRole('student'));
    }

    public function test_registration_rejects_weak_password(): void
    {
        $res = $this->post('/register', [
            'display_name' => 'Weak', 'email' => 'weak@test.local',
            'password' => 'short', 'password_confirmation' => 'short',
            'locale' => 'en', 'accept_terms' => '1',
        ]);
        $this->assertStatus(302, $res);
        $this->assertNull(db()->fetch('SELECT id FROM users WHERE email = ?', ['weak@test.local']));
    }

    public function test_registration_requires_terms(): void
    {
        $this->post('/register', [
            'display_name' => 'NoTerms', 'email' => 'noterms@test.local',
            'password' => 'Strong-Passw0rd', 'password_confirmation' => 'Strong-Passw0rd', 'locale' => 'en',
        ]);
        $this->assertNull(db()->fetch('SELECT id FROM users WHERE email = ?', ['noterms@test.local']));
    }

    public function test_login_succeeds_with_correct_credentials(): void
    {
        $this->makeUser('student', ['email' => 'log@test.local', 'password' => 'Correct-Passw0rd']);
        auth()->logout();
        $res = $this->post('/login', ['email' => 'log@test.local', 'password' => 'Correct-Passw0rd']);
        $this->assertStatus(302, $res);
        $this->assertTrue(auth()->check());
    }

    public function test_login_throttles_after_too_many_failures(): void
    {
        $this->makeUser('student', ['email' => 'brute@test.local', 'password' => 'Correct-Passw0rd']);
        auth()->logout();
        $max = (int) config('security.login_max_attempts');
        for ($i = 0; $i < $max; $i++) {
            $this->post('/login', ['email' => 'brute@test.local', 'password' => 'wrong']);
        }
        // Even the correct password is now throttled.
        $this->post('/login', ['email' => 'brute@test.local', 'password' => 'Correct-Passw0rd']);
        $this->assertFalse(auth()->check(), 'Account should be throttled even with the correct password.');
    }

    public function test_password_change_increments_auth_version(): void
    {
        $user = $this->makeUser('student', ['password' => 'Old-Passw0rd-1']);
        $before = (int) $user['auth_version'];
        \App\Services\UserService::changePassword((int) $user['id'], 'New-Passw0rd-2');
        $after = (int) db()->value('SELECT auth_version FROM users WHERE id = ?', [(int) $user['id']]);
        $this->assertSame($before + 1, $after);
    }

    public function test_password_reset_token_is_single_use(): void
    {
        $user = $this->makeUser('student', ['email' => 'reset@test.local']);
        auth()->logout();
        \App\Services\PasswordResetService::sendLink('reset@test.local');
        $hash = db()->value('SELECT token_hash FROM password_resets WHERE user_id = ?', [(int) $user['id']]);
        $this->assertNotNull($hash);
        // Recover the raw token from the sent e-mail.
        $sent = app('mailer')->sent();
        $url = end($sent)['html'];
        preg_match('/reset-password\/([a-f0-9]{64})/', $url, $m);
        $token = $m[1];
        $this->assertTrue(\App\Services\PasswordResetService::reset($token, 'Reset-Passw0rd-9'));
        $this->assertFalse(\App\Services\PasswordResetService::reset($token, 'Another-Passw0rd-9'), 'Token must not be reusable.');
    }
}
