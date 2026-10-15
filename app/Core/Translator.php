<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Centralised interface translations loaded from lang/{locale}.php.
 * Missing French keys fall back to English but are recorded so the
 * translation completeness test and admin screen can surface them.
 */
final class Translator
{
    public const SUPPORTED = ['en', 'fr'];

    private array $lines = [];
    private string $locale = 'en';
    private array $missing = [];

    public function __construct(private readonly string $path, private readonly string $fallback = 'en')
    {
    }

    public function setLocale(string $locale): void
    {
        $this->locale = in_array($locale, self::SUPPORTED, true) ? $locale : $this->fallback;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function lines(string $locale): array
    {
        if (!isset($this->lines[$locale])) {
            $file = $this->path . '/' . $locale . '.php';
            $this->lines[$locale] = is_file($file) ? require $file : [];
        }
        return $this->lines[$locale];
    }

    public function has(string $key, ?string $locale = null): bool
    {
        return array_key_exists($key, $this->lines($locale ?? $this->locale));
    }

    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale ??= $this->locale;
        $lines = $this->lines($locale);
        if (array_key_exists($key, $lines)) {
            $line = $lines[$key];
        } else {
            $this->missing[$locale][$key] = true;
            $line = $this->lines($this->fallback)[$key] ?? $key;
        }
        foreach ($replace as $name => $value) {
            $line = str_replace(':' . $name, (string) $value, $line);
        }
        return $line;
    }

    /**
     * Pluralisation helper: "one|many" strings, chosen by count.
     */
    public function choice(string $key, int $count, array $replace = [], ?string $locale = null): string
    {
        $parts = explode('|', $this->get($key, $replace + ['count' => $count], $locale));
        $useSingular = ($locale ?? $this->locale) === 'fr' ? $count <= 1 : $count === 1;
        return $useSingular || count($parts) === 1 ? $parts[0] : $parts[1];
    }

    public function missing(): array
    {
        return $this->missing;
    }
}
