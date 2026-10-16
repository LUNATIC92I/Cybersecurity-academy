<?php

declare(strict_types=1);

namespace Tests;

use App\Core\App;
use App\Core\Request;
use App\Core\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case. Each test runs inside a transaction that is rolled back on
 * tear-down, so the seeded reference data stays intact and tests are isolated.
 */
abstract class TestCase extends BaseTestCase
{
    protected App $app;

    protected function setUp(): void
    {
        $this->app = App::instance();
        // Fresh session and auth per test.
        $this->app->singleton('session', fn () => new \App\Core\Session($this->app->config()->get('session'), false));
        $this->app->singleton('auth', fn () => new \App\Core\Auth(db(), session()));
        session()->start();
        // Ensure a CSRF token exists so request() can satisfy the CSRF middleware.
        session()->put('_token', bin2hex(random_bytes(32)));
        db()->beginTestTransaction();
    }

    protected function tearDown(): void
    {
        db()->rollBackTestTransaction();
    }

    /**
     * Dispatch a request through the full HTTP kernel (middleware + controller).
     */
    protected function request(string $method, string $path, array $body = [], array $query = [], array $server = []): Response
    {
        // Carry the CSRF token so write requests pass the CSRF middleware.
        if (in_array($method, ['POST', 'PUT', 'DELETE'], true) && !isset($body['_token'])) {
            $body['_token'] = (string) session()->get('_token', '');
        }
        $request = new Request($method, $path, $query, $body, [], array_merge(['REQUEST_METHOD' => $method, 'REQUEST_URI' => $path, 'REMOTE_ADDR' => '127.0.0.1'], $server));
        $response = $this->app->handle($request);
        session()->nextRequest();
        return $response;
    }

    protected function get(string $path, array $query = []): Response
    {
        return $this->request('GET', $path, [], $query);
    }

    protected function post(string $path, array $body = []): Response
    {
        return $this->request('POST', $path, $body);
    }

    /**
     * Create a user with the given role and return the row.
     */
    protected function makeUser(string $role = 'student', array $overrides = []): array
    {
        $email = $overrides['email'] ?? ('u' . bin2hex(random_bytes(5)) . '@test.local');
        $id = \App\Services\UserService::register([
            'email' => $email,
            'password' => $overrides['password'] ?? 'Test-Password-9x',
            'display_name' => $overrides['display_name'] ?? 'Test User',
            'locale' => $overrides['locale'] ?? 'en',
        ]);
        if ($role !== 'student') {
            \App\Services\UserService::assignRole($id, $role);
        }
        db()->update('users', ['email_verified_at' => now()], ['id' => $id]);
        auth()->refresh();
        return db()->fetch('SELECT * FROM users WHERE id = ?', [$id]);
    }

    protected function actingAs(array $user): array
    {
        auth()->login($user);
        return $user;
    }

    protected function assertStatus(int $expected, Response $response, string $message = ''): void
    {
        $this->assertSame($expected, $response->status(), $message ?: "Expected HTTP $expected, got {$response->status()}");
    }
}
