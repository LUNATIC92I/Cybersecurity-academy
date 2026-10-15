<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Chooses the right translation row for a piece of content.
 *
 * A translation is used only if it exists and its status is "published"
 * (staff previewing may see drafts). Otherwise the English version is shown
 * and flagged with _fallback = true so the UI can say so explicitly, rather
 * than silently presenting English as French.
 */
final class Localizer
{
    public static function contentLocale(): string
    {
        $user = auth()->user();
        if ($user && !empty($user['content_locale'])) {
            return $user['content_locale'];
        }
        return locale();
    }

    /**
     * @param array<string, array> $byLocale rows keyed by locale
     */
    public static function pick(array $byLocale, ?string $locale = null, bool $allowDrafts = false): ?array
    {
        $locale ??= self::contentLocale();
        $preferred = $byLocale[$locale] ?? null;
        if ($preferred && ($allowDrafts || ($preferred['translation_status'] ?? 'published') === 'published')) {
            return $preferred + ['_locale' => $locale, '_fallback' => false];
        }
        $fallback = $byLocale['en'] ?? reset($byLocale) ?: null;
        if ($fallback === null) {
            return null;
        }
        return $fallback + ['_locale' => $fallback['locale'] ?? 'en', '_fallback' => $locale !== ($fallback['locale'] ?? 'en')];
    }

    /**
     * Load translation rows for many parents in one query and attach the
     * chosen translation under $key.
     */
    public static function attach(array $items, string $table, string $foreignKey, string $key = 't', ?string $locale = null, bool $allowDrafts = false): array
    {
        if ($items === []) {
            return [];
        }
        if (!preg_match('/^[a-z_]+$/', $table) || !preg_match('/^[a-z_]+$/', $foreignKey)) {
            throw new \InvalidArgumentException('Invalid identifier.');
        }
        $ids = array_values(array_unique(array_map(fn ($i) => (int) $i['id'], $items)));
        $rows = db()->fetchAll(
            "SELECT * FROM `$table` WHERE `$foreignKey` IN (" . \App\Core\Database::placeholders($ids) . ')',
            $ids
        );
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int) $row[$foreignKey]][$row['locale']] = $row;
        }
        foreach ($items as &$item) {
            $item[$key] = self::pick($grouped[(int) $item['id']] ?? [], $locale, $allowDrafts) ?? [];
        }
        return $items;
    }

    public static function lines(?string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text) ?: []), fn ($l) => $l !== ''));
    }
}
