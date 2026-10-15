<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private array $routeParams = [];
    private array $attributes = [];

    public function __construct(
        public readonly string $method,
        public readonly string $path,
        private array $query = [],
        private array $body = [],
        private array $files = [],
        private array $server = [],
        private array $cookies = [],
        private ?string $rawBody = null,
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim(rawurldecode($path), '/');

        return new self(
            $method,
            $path,
            $_GET,
            $_POST,
            $_FILES,
            $_SERVER,
            $_COOKIE,
            null
        );
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $value = $this->body[$key] ?? $this->query[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    public function string(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);
        return is_scalar($value) ? (string) $value : $default;
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->input($key);
        return is_numeric($value) ? (int) $value : $default;
    }

    public function bool(string $key): bool
    {
        return in_array($this->input($key), ['1', 1, 'on', 'true', true, 'yes'], true);
    }

    public function array(string $key): array
    {
        $value = $this->input($key, []);
        return is_array($value) ? $value : [];
    }

    public function query(string $key, mixed $default = null): mixed
    {
        $value = $this->query[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function only(array $keys): array
    {
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $this->input($key);
        }
        return $out;
    }

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;
        if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) {
            return null;
        }
        return $file['error'] === UPLOAD_ERR_NO_FILE ? null : $file;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if ($name === 'Content-Type') {
            return $this->server['CONTENT_TYPE'] ?? null;
        }
        return $this->server[$key] ?? null;
    }

    public function cookie(string $name): ?string
    {
        $value = $this->cookies[$name] ?? null;
        return is_string($value) ? $value : null;
    }

    public function rawBody(): string
    {
        if ($this->rawBody === null) {
            $this->rawBody = (string) file_get_contents('php://input');
        }
        return $this->rawBody;
    }

    public function ip(): string
    {
        // Only REMOTE_ADDR is trusted. Configure the web server to set it from
        // a trusted proxy header if the app runs behind a load balancer.
        return (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function userAgent(): string
    {
        return mb_substr((string) ($this->server['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }

    public function isSecure(): bool
    {
        return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off')
            || (int) ($this->server['SERVER_PORT'] ?? 0) === 443;
    }

    public function wantsJson(): bool
    {
        return str_contains((string) $this->header('Accept'), 'application/json')
            || $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function fullUrlWithoutQuery(): string
    {
        return $this->path;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function param(string $key, ?string $default = null): ?string
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function queryString(): array
    {
        return $this->query;
    }
}
