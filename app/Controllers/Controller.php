<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Validator;

abstract class Controller
{
    protected function validate(Request $request, array $rules): array
    {
        return Validator::make($request->all(), $rules)->validate();
    }

    protected function authorize(string $permission): void
    {
        if (!auth()->can($permission)) {
            throw HttpException::forbidden();
        }
    }

    protected function user(): array
    {
        $user = auth()->user();
        if (!$user) {
            throw new HttpException(401);
        }
        return $user;
    }

    protected function userId(): int
    {
        return (int) $this->user()['id'];
    }

    /**
     * Per-user/IP throttling for sensitive endpoints.
     */
    protected function throttle(string $key, int $max, int $seconds): void
    {
        $limiter = app('limiter');
        if (!$limiter->attempt($key, $max, $seconds)) {
            throw new HttpException(429);
        }
    }

    protected function meta(string $title, ?string $description = null, array $extra = []): array
    {
        return array_merge([
            'title' => $title,
            'description' => $description ?? __('meta.default_description'),
        ], $extra);
    }
}
