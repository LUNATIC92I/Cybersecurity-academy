<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\Translator;

function app(?string $service = null): mixed
{
    $app = App::instance();
    return $service === null ? $app : $app->get($service);
}

function base_path(string $path = ''): string
{
    return App::instance()->basePath . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function config(string $key, mixed $default = null): mixed
{
    return App::instance()->config()->get($key, $default);
}

function db(): Database
{
    return app('db');
}

function session(): Session
{
    return app('session');
}

function auth(): Auth
{
    return app('auth');
}

function translator(): Translator
{
    return app('translator');
}

function locale(): string
{
    return translator()->locale();
}

/**
 * Translate an interface string.
 */
function __(string $key, array $replace = [], ?string $locale = null): string
{
    return translator()->get($key, $replace, $locale);
}

function trans_choice(string $key, int $count, array $replace = []): string
{
    return translator()->choice($key, $count, $replace);
}

/**
 * Escape for HTML output. Use for every dynamic value in templates.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

function route(string $name, array $params = []): string
{
    return App::instance()->router->url($name, $params);
}

function url(string $path = ''): string
{
    return rtrim((string) config('app.url'), '/') . '/' . ltrim($path, '/');
}

function absolute_route(string $name, array $params = []): string
{
    return url(route($name, $params));
}

function asset(string $path): string
{
    $file = base_path('public/assets/' . ltrim($path, '/'));
    $version = is_file($file) ? substr(md5((string) filemtime($file)), 0, 8) : '1';
    return '/assets/' . ltrim($path, '/') . '?v=' . $version;
}

function csrf_token(): string
{
    return (string) session()->get('_token', '');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function old(string $key, mixed $default = ''): mixed
{
    $old = session()->getFlash('old', []);
    return $old[$key] ?? $default;
}

function errors(): array
{
    return session()->getFlash('errors', []);
}

function error_for(string $field): ?string
{
    return errors()[$field] ?? null;
}

function flash(string $type, string $message): void
{
    session()->flash('message', ['type' => $type, 'text' => $message]);
}

function now(): string
{
    return gmdate('Y-m-d H:i:s');
}

function previous_url(): string
{
    $url = (string) session()->get('_previous_url', '/');
    return safe_redirect_path($url);
}

/**
 * Accept only local, absolute paths to prevent open redirects.
 */
function safe_redirect_path(?string $path, string $default = '/'): string
{
    if (!is_string($path) || $path === '' || !str_starts_with($path, '/') || str_starts_with($path, '//')
        || str_contains($path, '\\') || preg_match('/[\r\n]/', $path)) {
        return $default;
    }
    return $path;
}

function redirect(string $url): \App\Core\Response
{
    return \App\Core\Response::redirect($url);
}

function back(): \App\Core\Response
{
    return \App\Core\Response::redirect(previous_url());
}

function view(string $view, array $data = [], ?string $layout = 'layouts/app'): \App\Core\Response
{
    return \App\Core\Response::html(app('view')->render($view, $data, $layout));
}

function partial(string $view, array $data = []): string
{
    return app('view')->partial($view, $data);
}

function format_date(?string $date, ?string $locale = null): string
{
    if (!$date) {
        return '';
    }
    $locale ??= locale();
    $ts = strtotime($date . ' UTC');
    if (class_exists(\IntlDateFormatter::class)) {
        $fmt = new \IntlDateFormatter($locale === 'fr' ? 'fr_FR' : 'en_GB', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE, 'UTC');
        return (string) $fmt->format($ts);
    }
    return gmdate('Y-m-d', $ts);
}

function format_datetime(?string $date): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date . ' UTC');
    if (class_exists(\IntlDateFormatter::class)) {
        $fmt = new \IntlDateFormatter(locale() === 'fr' ? 'fr_FR' : 'en_GB', \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT, 'UTC');
        return (string) $fmt->format($ts) . ' UTC';
    }
    return gmdate('Y-m-d H:i', $ts) . ' UTC';
}

function format_money(int $cents, string $currency = 'USD'): string
{
    if (class_exists(\NumberFormatter::class)) {
        $fmt = new \NumberFormatter(locale() === 'fr' ? 'fr_FR' : 'en_US', \NumberFormatter::CURRENCY);
        return (string) $fmt->formatCurrency($cents / 100, $currency);
    }
    return sprintf('%s %.2f', $currency, $cents / 100);
}

function format_number(int|float $number): string
{
    if (class_exists(\NumberFormatter::class)) {
        return (string) (new \NumberFormatter(locale() === 'fr' ? 'fr_FR' : 'en_US', \NumberFormatter::DECIMAL))->format($number);
    }
    return number_format($number);
}

function minutes_label(int $minutes): string
{
    if ($minutes < 60) {
        return __('common.minutes', ['n' => $minutes]);
    }
    $h = intdiv($minutes, 60);
    $m = $minutes % 60;
    return $m ? __('common.hours_minutes', ['h' => $h, 'm' => $m]) : __('common.hours', ['n' => $h]);
}

function audit(string $action, ?string $entityType = null, int|string|null $entityId = null, array $details = []): void
{
    \App\Services\AuditLogger::log($action, $entityType, $entityId, $details);
}

function setting(string $key, mixed $default = null): mixed
{
    return \App\Services\SettingsService::get($key, $default);
}

/**
 * Current path with a lang query parameter, for hreflang alternates.
 */
function current_path_with_lang(string $lang): string
{
    $request = app('request');
    $query = $request->queryString();
    $query['lang'] = $lang;
    return $request->path . '?' . http_build_query($query);
}

function is_active_path(string $prefix): bool
{
    $path = app('request')->path;
    return $prefix === '/' ? $path === '/' : str_starts_with($path, $prefix);
}

function level_label(string $level): string
{
    return __('level.' . $level);
}

function icon(string $name, string $class = 'icon'): string
{
    return \App\Helpers\Icons::svg($name, $class);
}
