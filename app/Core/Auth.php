<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session-based authentication with role/permission lookups.
 */
final class Auth
{
    private ?array $user = null;
    private bool $resolved = false;
    private ?array $permissions = null;
    private ?array $roles = null;

    public function __construct(private readonly Database $db, private readonly Session $session)
    {
    }

    public function user(): ?array
    {
        if (!$this->resolved) {
            $this->resolved = true;
            $id = $this->session->get('user_id');
            if ($id) {
                $user = $this->db->fetch('SELECT * FROM users WHERE id = ? AND status = ?', [(int) $id, 'active']);
                // Invalidate sessions created before the last password change.
                if ($user && (int) $this->session->get('auth_version', 0) === (int) $user['auth_version']) {
                    $this->user = $user;
                } else {
                    $this->session->forget('user_id');
                }
            }
        }
        return $this->user;
    }

    public function id(): ?int
    {
        return $this->user() ? (int) $this->user['id'] : null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function login(array $user): void
    {
        $this->session->regenerate();
        $this->session->put('user_id', (int) $user['id']);
        $this->session->put('auth_version', (int) $user['auth_version']);
        $this->session->put('locale', $user['locale']);
        $this->user = $user;
        $this->resolved = true;
        $this->permissions = null;
        $this->roles = null;
        $this->db->update('users', ['last_login_at' => now()], ['id' => (int) $user['id']]);
    }

    public function logout(): void
    {
        $locale = $this->session->get('locale');
        $this->session->invalidate();
        if ($locale) {
            $this->session->put('locale', $locale);
        }
        $this->user = null;
        $this->resolved = true;
        $this->permissions = null;
        $this->roles = null;
    }

    public function refresh(): void
    {
        $this->resolved = false;
        $this->user = null;
        $this->permissions = null;
        $this->roles = null;
    }

    public function roles(): array
    {
        if ($this->roles === null) {
            $this->roles = $this->check()
                ? $this->db->column(
                    'SELECT r.code FROM roles r JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = ?',
                    [$this->id()]
                )
                : [];
        }
        return $this->roles;
    }

    public function hasRole(string ...$codes): bool
    {
        return (bool) array_intersect($codes, $this->roles());
    }

    public function permissions(): array
    {
        if ($this->permissions === null) {
            $this->permissions = $this->check()
                ? $this->db->column(
                    'SELECT DISTINCT p.code FROM permissions p
                     JOIN role_permissions rp ON rp.permission_id = p.id
                     JOIN user_roles ur ON ur.role_id = rp.role_id
                     WHERE ur.user_id = ?',
                    [$this->id()]
                )
                : [];
        }
        return $this->permissions;
    }

    public function can(string $permission): bool
    {
        if (!$this->check()) {
            return false;
        }
        return in_array($permission, $this->permissions(), true);
    }

    public function isStaff(): bool
    {
        return $this->can('admin.access');
    }
}
