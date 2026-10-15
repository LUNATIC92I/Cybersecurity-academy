<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

final class Authenticate
{
    public function handle(Request $request, callable $next, ?string $arg = null): Response
    {
        if (!auth()->check()) {
            if ($request->wantsJson()) {
                return Response::json(['message' => __('auth.required')], 401);
            }
            if ($request->method === 'GET') {
                session()->put('_intended', $request->path);
            }
            flash('info', __('auth.required'));
            return Response::redirect(route('login'));
        }
        return $next($request);
    }
}
