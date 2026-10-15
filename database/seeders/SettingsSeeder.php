<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\SettingsService;

final class SettingsSeeder
{
    public function run(): void
    {
        foreach (SettingsService::DEFAULTS as $key => $value) {
            db()->query('INSERT IGNORE INTO platform_settings (setting_key, setting_value) VALUES (?, ?)', [$key, $value]);
        }
        SettingsService::flush();
    }
}
