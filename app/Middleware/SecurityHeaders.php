<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

final class SecurityHeaders
{
    public function handle(Request $request, callable $next, ?string $arg = null): Response
    {
        return $this->apply($next($request));
    }

    public function apply(Response $response): Response
    {
        // All scripts and styles are served from our own origin; no inline
        // script is used anywhere, which allows a strict policy.
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self'",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            'upgrade-insecure-requests',
        ]);
        $response->header('Content-Security-Policy', $csp);
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'DENY');
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->header('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $response->header('Cross-Origin-Opener-Policy', 'same-origin');
        if (config('app.env') === 'production') {
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        if ($response->getHeader('Cache-Control') === null) {
            $response->header('Cache-Control', 'no-store, private');
        }
        return $response;
    }
}
