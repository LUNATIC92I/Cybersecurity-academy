<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class CsrfTest extends TestCase
{
    public function test_post_without_token_is_rejected(): void
    {
        // Bypass the helper that injects the token.
        $request = new \App\Core\Request('POST', '/login', [], ['email' => 'x@test.local', 'password' => 'y'], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/login', 'REMOTE_ADDR' => '127.0.0.1']);
        $response = $this->app->handle($request);
        $this->assertStatus(419, $response);
    }

    public function test_post_with_wrong_token_is_rejected(): void
    {
        session()->start();
        $request = new \App\Core\Request('POST', '/login', [], ['_token' => 'wrong', 'email' => 'x@test.local', 'password' => 'y'], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/login', 'REMOTE_ADDR' => '127.0.0.1']);
        $response = $this->app->handle($request);
        $this->assertStatus(419, $response);
    }
}
