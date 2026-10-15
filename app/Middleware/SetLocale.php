<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Translator;

/**
 * Locale resolution order: explicit ?lang= parameter, session, the signed-in
 * user's saved preference, the locale cookie, then the English default.
 */
final class SetLocale
{
    public function handle(Request $request, callable $next, ?string $arg = null): Response
    {
        $session = session();
        $explicit = $request->query('lang');
        $locale = null;
        $persistCookie = false;

        if (is_string($explicit) && in_array($explicit, Translator::SUPPORTED, true)) {
            $locale = $explicit;
            $persistCookie = true;
            if (auth()->check() && auth()->user()['locale'] !== $locale) {
                db()->update('users', ['locale' => $locale], ['id' => auth()->id()]);
            }
        }
        $locale ??= $session->get('locale');
        if (!$locale && auth()->check()) {
            $locale = auth()->user()['locale'];
        }
        $locale ??= $request->cookie('ca_locale');
        if (!in_array($locale, Translator::SUPPORTED, true)) {
            $locale = config('app.locale', 'en');
        }

        $session->put('locale', $locale);
        translator()->setLocale($locale);

        $response = $next($request);
        if ($persistCookie) {
            $response->cookie('ca_locale', $locale, time() + 31536000);
        }
        $response->header('Content-Language', $locale);
        return $response;
    }
}
