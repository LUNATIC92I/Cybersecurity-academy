<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuditLogger;
use App\Services\SettingsService;

final class SettingsController extends AdminController
{
    public function index(Request $request): Response
    {
        $values = [];
        foreach (array_keys(SettingsService::DEFAULTS) as $key) {
            $values[$key] = setting($key);
        }
        return $this->render('settings', [
            'values' => $values,
            'environment' => [
                'app_env' => config('app.env'),
                'debug' => config('app.debug') ? 'on' : 'off',
                'mail_driver' => config('mail.driver'),
                'payment_provider' => config('payments.provider'),
                'demo_mode' => config('app.demo_mode') ? 'on' : 'off',
                'php' => PHP_VERSION,
                'app_key' => config('app.key') !== '' ? 'set' : 'MISSING',
            ],
        ]);
    }

    public function update(Request $request): Response
    {
        $data = $this->validate($request, [
            'lesson_min_seconds' => 'required|integer|between:0,3600',
            'contact_email' => 'required|email',
            'certificate_signatory' => 'required|max:120',
            'maintenance_banner' => 'max:255',
            'social_linkedin' => 'url',
            'social_facebook' => 'url',
            'social_tiktok' => 'url',
        ]);
        $data['registration_open'] = $request->bool('registration_open') ? '1' : '0';
        $data['public_stats_enabled'] = $request->bool('public_stats_enabled') ? '1' : '0';
        foreach ($data as $key => $value) {
            SettingsService::set($key, $value === null ? '' : (string) $value);
        }
        AuditLogger::log('settings.updated', 'settings', null, array_keys($data));
        flash('success', __('admin.saved'));
        return redirect(route('admin.settings'));
    }
}
