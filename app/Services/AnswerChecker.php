<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Server-side checking of free-form answers shared by labs and exams.
 */
final class AnswerChecker
{
    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = (string) preg_replace('/\s+/u', ' ', $value);
        return trim($value, " \t\n\r\0\x0B.\"'`");
    }

    public static function text(string $given, array $spec): bool
    {
        $given = self::normalize($given);
        if ($given === '') {
            return false;
        }
        foreach ((array) ($spec['accepted'] ?? []) as $accepted) {
            $accepted = self::normalize((string) $accepted);
            if (!empty($spec['contains']) ? str_contains($given, $accepted) : $given === $accepted) {
                return true;
            }
        }
        return false;
    }

    public static function number(mixed $given, array $spec): bool
    {
        $given = is_string($given) ? str_replace([',', ' '], ['.', ''], trim($given)) : $given;
        if (!is_numeric($given)) {
            return false;
        }
        return abs((float) $given - (float) ($spec['value'] ?? 0)) <= (float) ($spec['tolerance'] ?? 0);
    }

    public static function choice(mixed $given, array $spec): bool
    {
        return is_scalar($given) && (string) $given === (string) ($spec['correct'] ?? '');
    }

    public static function multi(mixed $given, array $spec): bool
    {
        if (!is_array($given)) {
            return false;
        }
        $given = array_values(array_unique(array_map('strval', $given)));
        $correct = array_values(array_unique(array_map('strval', (array) ($spec['correct'] ?? []))));
        sort($given);
        sort($correct);
        return $given === $correct;
    }

    /**
     * Structural check of a query (KQL/SPL/SQL) against required and
     * forbidden regular expressions. Queries are never executed.
     *
     * @return array{ok: bool, missing: int}
     */
    public static function query(string $given, array $spec): array
    {
        $given = trim($given);
        if ($given === '' || mb_strlen($given) > 4000) {
            return ['ok' => false, 'missing' => count((array) ($spec['required'] ?? []))];
        }
        $missing = 0;
        foreach ((array) ($spec['required'] ?? []) as $pattern) {
            if (@preg_match((string) $pattern, $given) !== 1) {
                $missing++;
            }
        }
        foreach ((array) ($spec['forbidden'] ?? []) as $pattern) {
            if (@preg_match((string) $pattern, $given) === 1) {
                $missing++;
            }
        }
        return ['ok' => $missing === 0, 'missing' => $missing];
    }
}
