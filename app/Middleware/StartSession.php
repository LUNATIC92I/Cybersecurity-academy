<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

final class StartSession
{
    public function handle(Request $request, callable $next, ?string $arg = null): Response
    {
        $session = session();
        $session->start($request->isSecure());
        if (!$session->has('_token')) {
            $session->put('_token', bin2hex(random_bytes(32)));
        }

        $response = $next($request);

        // Remember the last successfully rendered page for "redirect back".
        if ($request->method === 'GET' && $response->status() === 200
            && str_starts_with((string) $response->getHeader('Content-Type'), 'text/html')) {
            $query = $request->queryString();
            unset($query['lang']);
            $session->put('_previous_url', $request->path . ($query ? '?' . http_build_query($query) : ''));
        }
        return $response;
    }
}
