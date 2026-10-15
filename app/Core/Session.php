<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session wrapper. In HTTP mode it uses native PHP sessions with hardened
 * settings; in tests it uses an in-memory array so feature tests can run
 * without headers.
 */
final class Session
{
    private array $memory = [];
    private bool $started = false;

    public function __construct(private readonly array $config, private readonly bool $native = true)
    {
    }

    public function start(bool $secureRequest = false): void
    {
        if ($this->started) {
            return;
        }
        if ($this->native && session_status() !== PHP_SESSION_ACTIVE) {
            $path = $this->config['path'] ?? null;
            if ($path && is_dir($path) && is_writable($path)) {
                session_save_path($path);
            }
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.sid_length', '48');
            ini_set('session.sid_bits_per_character', '6');
            ini_set('session.gc_maxlifetime', (string) (($this->config['lifetime'] ?? 120) * 60));
            session_name($this->config['name'] ?? 'ca_session');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => (bool) ($this->config['secure'] ?? false) || $secureRequest,
                'httponly' => true,
                'samesite' => $this->config['same_site'] ?? 'Lax',
            ]);
            session_start();
        }
        $this->started = true;
        $this->ageFlashData();
    }

    private function &store(): array
    {
        if ($this->native && session_status() === PHP_SESSION_ACTIVE) {
            return $_SESSION;
        }
        return $this->memory;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $store = &$this->store();
        return $store[$key] ?? $default;
    }

    public function put(string $key, mixed $value): void
    {
        $store = &$this->store();
        $store[$key] = $value;
    }

    public function has(string $key): bool
    {
        $store = &$this->store();
        return array_key_exists($key, $store);
    }

    public function forget(string $key): void
    {
        $store = &$this->store();
        unset($store[$key]);
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->forget($key);
        return $value;
    }

    public function flash(string $key, mixed $value): void
    {
        $flash = $this->get('_flash', ['new' => [], 'old' => []]);
        $flash['new'][$key] = $value;
        $this->put('_flash', $flash);
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        $flash = $this->get('_flash', ['new' => [], 'old' => []]);
        return $flash['old'][$key] ?? $flash['new'][$key] ?? $default;
    }

    private function ageFlashData(): void
    {
        $flash = $this->get('_flash', ['new' => [], 'old' => []]);
        $this->put('_flash', ['new' => [], 'old' => $flash['new'] ?? []]);
    }

    /**
     * Called between simulated requests in tests to mimic a new HTTP request.
     */
    public function nextRequest(): void
    {
        $this->ageFlashData();
    }

    /**
     * Rotate the session identifier (after login, privilege change, logout)
     * to prevent session fixation.
     */
    public function regenerate(): void
    {
        if ($this->native && session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $this->put('_token', bin2hex(random_bytes(32)));
    }

    public function invalidate(): void
    {
        $store = &$this->store();
        $store = [];
        $this->regenerate();
    }

    public function all(): array
    {
        $store = &$this->store();
        return $store;
    }
}
