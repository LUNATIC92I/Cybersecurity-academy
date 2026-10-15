<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;

/**
 * Route middleware "can:permission.code". Authorization is always checked on
 * the server; hiding a button in the UI is never relied upon.
 */
final class RequirePermission
{
    public function handle(Request $request, callable $next, ?string $permission = null): Response
    {
        if (!auth()->check()) {
            return (new Authenticate())->handle($request, $next);
        }
        if ($permission === null || !auth()->can($permission)) {
            throw HttpException::forbidden();
        }
        return $next($request);
    }
}
