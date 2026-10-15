<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;

final class VerifyCsrfToken
{
    /** Paths authenticated by other means (provider signatures). */
    private const EXCEPT = ['#^/webhooks/#'];

    public function handle(Request $request, callable $next, ?string $arg = null): Response
    {
        if (in_array($request->method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }
        foreach (self::EXCEPT as $pattern) {
            if (preg_match($pattern, $request->path)) {
                return $next($request);
            }
        }
        $sessionToken = (string) session()->get('_token', '');
        $token = $request->input('_token') ?? $request->header('X-CSRF-Token') ?? '';
        if ($sessionToken === '' || !is_string($token) || !hash_equals($sessionToken, $token)) {
            throw new HttpException(419);
        }
        return $next($request);
    }
}
