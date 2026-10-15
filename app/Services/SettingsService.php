<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Platform settings stored in platform_settings, with typed defaults.
 */
final class SettingsService
{
    public const DEFAULTS = [
        'registration_open' => '1',
        'lesson_min_seconds' => '45',
        'public_stats_enabled' => '1',
        'maintenance_banner' => '',
        'certificate_signatory' => 'Academic Board, Cybersecurity-Academy',
        'contact_email' => 'support@example.com',
        'social_linkedin' => '',
        'social_facebook' => '',
        'social_tiktok' => '',
    ];

    private static ?array $cache = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        if (self::$cache === null) {
            self::$cache = [];
            try {
                foreach (db()->fetchAll('SELECT setting_key, setting_value FROM platform_settings') as $row) {
                    self::$cache[$row['setting_key']] = $row['setting_value'];
                }
            } catch (\Throwable) {
                // Table may not exist before migrations run.
            }
        }
        return self::$cache[$key] ?? self::DEFAULTS[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        db()->query(
            'INSERT INTO platform_settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, $value]
        );
        self::$cache = null;
    }

    public static function flush(): void
    {
        self::$cache = null;
    }
}
