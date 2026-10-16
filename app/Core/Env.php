<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal .env loader. Values already present in the real environment win,
 * so production servers can inject secrets without a file on disk.
 */
final class Env
{
    private static array $values = [];

    public static function load(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) {
            return;
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if ($key === '' || !preg_match('/^[A-Z0-9_]+$/', $key)) {
                continue;
            }
            if ((str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            // First definition wins, so an earlier-loaded file (e.g. .env.testing)
            // takes precedence over a later one (.env), and real environment
            // variables override both.
            if (getenv($key) === false && !array_key_exists($key, $_ENV) && !array_key_exists($key, self::$values)) {
                self::$values[$key] = $value;
            }
        }
    }

    public static function set(string $key, ?string $value): void
    {
        self::$values[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$values)) {
            $value = self::$values[$key];
        } elseif (array_key_exists($key, $_ENV)) {
            $value = $_ENV[$key];
        } elseif (($env = getenv($key)) !== false) {
            $value = $env;
        } else {
            return $default;
        }

        return match (is_string($value) ? strtolower($value) : $value) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }
}
