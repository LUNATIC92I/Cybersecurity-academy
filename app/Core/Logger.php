<?php

declare(strict_types=1);

namespace App\Core;

/**
 * File logger writing JSON lines to storage/logs (outside the web root).
 */
final class Logger
{
    public function __construct(private readonly string $directory)
    {
    }

    public function log(string $level, string $message, array $context = []): void
    {
        if (!is_dir($this->directory)) {
            @mkdir($this->directory, 0750, true);
        }
        $line = json_encode([
            'time' => gmdate('c'),
            'level' => $level,
            'message' => $message,
            'context' => $this->scrub($context),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        @file_put_contents($this->directory . '/app-' . gmdate('Y-m-d') . '.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    private function scrub(array $context): array
    {
        foreach ($context as $key => $value) {
            if (is_string($key) && preg_match('/pass|secret|token|key|authorization/i', $key)) {
                $context[$key] = '[redacted]';
            } elseif (is_array($value)) {
                $context[$key] = $this->scrub($value);
            }
        }
        return $context;
    }
}
