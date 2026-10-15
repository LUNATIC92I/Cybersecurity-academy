<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Validator;

final class UserService
{
    public static function register(array $data): int
    {
        return db()->transaction(function () use ($data) {
            $id = db()->insert('users', [
                'email' => mb_strtolower(trim($data['email'])),
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'display_name' => trim($data['display_name']),
                'locale' => in_array($data['locale'] ?? 'en', ['en', 'fr'], true) ? $data['locale'] : 'en',
            ]);
            self::assignRole($id, 'student');
            return $id;
        });
    }

    public static function createAdministrator(string $email, string $name, string $password): int
    {
        $v = Validator::make(
            ['email' => $email, 'password' => $password, 'display_name' => $name],
            ['email' => 'required|email|unique:users,email', 'password' => 'required|password', 'display_name' => 'required|max:120']
        );
        if ($v->fails()) {
            throw new \InvalidArgumentException(implode(' ', $v->errors()));
        }
        $id = self::register(['email' => $email, 'password' => $password, 'display_name' => $name, 'locale' => 'en']);
        self::assignRole($id, 'super_admin');
        db()->update('users', ['email_verified_at' => now()], ['id' => $id]);
        AuditLogger::log('user.admin_created', 'user', $id, [], $id);
        return $id;
    }

    public static function assignRole(int $userId, string $roleCode): void
    {
        $roleId = db()->value('SELECT id FROM roles WHERE code = ?', [$roleCode]);
        if ($roleId === null) {
            throw new \RuntimeException("Unknown role [$roleCode].");
        }
        db()->query('INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)', [$userId, (int) $roleId]);
    }

    public static function syncRoles(int $userId, array $roleCodes): void
    {
        db()->transaction(function () use ($userId, $roleCodes) {
            db()->delete('user_roles', ['user_id' => $userId]);
            foreach ($roleCodes as $code) {
                self::assignRole($userId, $code);
            }
        });
    }

    public static function changePassword(int $userId, string $newPassword): void
    {
        db()->query(
            'UPDATE users SET password_hash = ?, auth_version = auth_version + 1 WHERE id = ?',
            [password_hash($newPassword, PASSWORD_DEFAULT), $userId]
        );
    }

    /**
     * Records the request; erasure happens after the grace period via
     * `maintenance:run`, or immediately if $immediate is true.
     */
    public static function requestDeletion(int $userId, bool $immediate = false): void
    {
        if ($immediate) {
            self::erase($userId);
            return;
        }
        db()->update('users', ['deletion_requested_at' => now(), 'status' => 'suspended'], ['id' => $userId]);
        AuditLogger::log('user.deletion_requested', 'user', $userId, [], $userId);
    }

    /**
     * Irreversibly removes personal data. Certificates are retained for
     * verification integrity, but detached from the account and kept with the
     * holder name only (documented in the privacy policy).
     */
    public static function erase(int $userId): void
    {
        db()->transaction(function () use ($userId) {
            db()->query('UPDATE certificates SET is_public = 0 WHERE user_id = ?', [$userId]);
            db()->query('UPDATE support_tickets SET email = ?, name = ? WHERE user_id = ?', ['erased@invalid', 'Erased user', $userId]);
            db()->delete('users', ['id' => $userId]);
            AuditLogger::log('user.erased', 'user', $userId, [], null);
        });
    }

    public static function generatePublicSlug(string $name): string
    {
        $base = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower(ContentRenderer::ascii($name))), '-') ?: 'learner';
        $base = substr($base, 0, 40);
        do {
            $slug = $base . '-' . bin2hex(random_bytes(3));
        } while (db()->value('SELECT COUNT(*) FROM users WHERE public_slug = ?', [$slug]));
        return $slug;
    }
}
