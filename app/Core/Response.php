<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    private array $headers = [];
    private array $cookies = [];
    /** @var callable|null */
    private $streamer = null;

    public function __construct(
        private string $content = '',
        private int $status = 200,
        array $headers = []
    ) {
        foreach ($headers as $name => $value) {
            $this->header($name, $value);
        }
    }

    public static function html(string $content, int $status = 200): self
    {
        return new self($content, $status, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public static function json(mixed $data, int $status = 200): self
    {
        return new self(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self('', $status, ['Location' => $url]);
    }

    public static function text(string $content, int $status = 200, string $type = 'text/plain; charset=UTF-8'): self
    {
        return new self($content, $status, ['Content-Type' => $type]);
    }

    /**
     * Send raw bytes as a download. The filename is sanitised to prevent
     * header injection.
     */
    public static function download(string $bytes, string $filename, string $mime, bool $inline = false): self
    {
        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'download';
        return new self($bytes, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . $safe . '"',
            'Content-Length' => (string) strlen($bytes),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public static function file(string $path, string $filename, string $mime, bool $inline = false): self
    {
        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'download';
        $response = new self('', 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . $safe . '"',
            'Content-Length' => (string) filesize($path),
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->streamer = static function () use ($path): void {
            readfile($path);
        };
        $response->content = (string) file_get_contents($path);
        return $response;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = str_replace(["\r", "\n"], '', $value);
        return $this;
    }

    public function withoutHeader(string $name): self
    {
        unset($this->headers[$name]);
        return $this;
    }

    public function cookie(string $name, string $value, int $expires, array $options = []): self
    {
        $this->cookies[$name] = [$value, $expires, $options];
        return $this;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function cookies(): array
    {
        return $this->cookies;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);
            foreach ($this->headers as $name => $value) {
                header($name . ': ' . $value, true);
            }
            foreach ($this->cookies as $name => [$value, $expires, $options]) {
                setcookie($name, $value, array_merge([
                    'expires' => $expires,
                    'path' => '/',
                    'secure' => (bool) config('session.secure'),
                    'httponly' => true,
                    'samesite' => 'Lax',
                ], $options));
            }
        }
        if ($this->streamer !== null) {
            ($this->streamer)();
            return;
        }
        echo $this->content;
    }
}
