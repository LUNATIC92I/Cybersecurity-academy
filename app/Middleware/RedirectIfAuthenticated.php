<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

final class RedirectIfAuthenticated
{
    public function handle(Request $request, callable $next, ?string $arg = null): Response
    {
        if (auth()->check()) {
            return Response::redirect(route('dashboard'));
        }
        return $next($request);
    }
}
