<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Password reset tokens: 256-bit random, stored only as SHA-256 hashes,
 * single-use and time-limited. Responses never reveal whether an e-mail
 * address is registered.
 */
final class PasswordResetService
{
    public static function sendLink(string $email): void
    {
        $user = db()->fetch('SELECT * FROM users WHERE email = ? AND status = ?', [mb_strtolower(trim($email)), 'active']);
        if (!$user) {
            return;
        }
        $token = bin2hex(random_bytes(32));
        $ttl = (int) config('security.password_reset_ttl_minutes', 60);
        db()->transaction(function () use ($user, $token, $ttl) {
            db()->query('DELETE FROM password_resets WHERE user_id = ?', [(int) $user['id']]);
            db()->insert('password_resets', [
                'user_id' => (int) $user['id'],
                'token_hash' => hash('sha256', $token),
                'expires_at' => gmdate('Y-m-d H:i:s', time() + $ttl * 60),
            ]);
        });
        app('mailer')->send($user['email'], 'password_reset', [
            'name' => $user['display_name'],
            'url' => absolute_route('password.reset', ['token' => $token]),
            'minutes' => $ttl,
        ], $user['locale']);
    }

    public static function findValid(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        return db()->fetch(
            'SELECT pr.*, u.email FROM password_resets pr JOIN users u ON u.id = pr.user_id
             WHERE pr.token_hash = ? AND pr.used_at IS NULL AND pr.expires_at > UTC_TIMESTAMP() AND u.status = ?',
            [hash('sha256', $token), 'active']
        );
    }

    public static function reset(string $token, string $password): bool
    {
        return db()->transaction(function () use ($token, $password) {
            $row = self::findValid($token);
            if (!$row) {
                return false;
            }
            $claimed = db()->query(
                'UPDATE password_resets SET used_at = UTC_TIMESTAMP() WHERE id = ? AND used_at IS NULL',
                [(int) $row['id']]
            )->rowCount();
            if ($claimed !== 1) {
                return false;
            }
            UserService::changePassword((int) $row['user_id'], $password);
            AuditLogger::log('auth.password_reset', 'user', (int) $row['user_id'], [], (int) $row['user_id']);
            return true;
        });
    }
}
