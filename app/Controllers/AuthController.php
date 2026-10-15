<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Services\AuditLogger;
use App\Services\PasswordResetService;
use App\Services\UserService;

final class AuthController extends Controller
{
    public function showLogin(Request $request): Response
    {
        return view('auth/login', ['meta' => $this->meta(__('auth.login.title')) + ['robots' => 'noindex']], 'layouts/auth');
    }

    public function login(Request $request): Response
    {
        $data = $this->validate($request, ['email' => 'required|email', 'password' => 'required|max:200']);
        $email = mb_strtolower($data['email']);
        $limiter = app('limiter');
        $max = (int) config('security.login_max_attempts', 5);
        $decay = (int) config('security.login_decay_seconds', 900);
        $keyEmail = 'login:email:' . $email;
        $keyIp = 'login:ip:' . $request->ip();

        if ($limiter->tooManyAttempts($keyEmail, $max) || $limiter->tooManyAttempts($keyIp, $max * 4)) {
            $minutes = (int) ceil(max($limiter->availableIn($keyEmail), $limiter->availableIn($keyIp)) / 60);
            AuditLogger::log('auth.login_throttled', 'user', null, ['email_hash' => hash('sha256', $email)]);
            return $this->failLogin(__('auth.throttled', ['minutes' => max(1, $minutes)]), $email);
        }

        $user = db()->fetch('SELECT * FROM users WHERE email = ?', [$email]);
        // Verify against a dummy hash when the account doesn't exist so that
        // response timing does not reveal registered addresses.
        $hash = $user['password_hash'] ?? '$2y$10$YmcAckkuiCd.tFg8GskNX.XUenDeKygG0W9cSOCY.XhLvr6pWgINO';
        $valid = password_verify((string) $request->input('password'), $hash) && $user !== null;

        db()->insert('login_attempts', [
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'succeeded' => $valid && $user['status'] === 'active' ? 1 : 0,
        ]);

        if (!$valid || $user['status'] !== 'active') {
            $limiter->hit($keyEmail, $decay);
            $limiter->hit($keyIp, $decay);
            return $this->failLogin(
                $valid && $user['status'] === 'suspended' ? __('auth.suspended') : __('auth.failed'),
                $email
            );
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            db()->update('users', ['password_hash' => password_hash((string) $request->input('password'), PASSWORD_DEFAULT)], ['id' => (int) $user['id']]);
        }
        $limiter->clear($keyEmail);
        auth()->login($user);
        translator()->setLocale($user['locale']);
        AuditLogger::log('auth.login', 'user', (int) $user['id']);
        flash('success', __('auth.welcome_back', ['name' => $user['display_name']]));

        $intended = safe_redirect_path(session()->pull('_intended'), route('dashboard'));
        return redirect($intended);
    }

    private function failLogin(string $message, string $email): Response
    {
        session()->flash('errors', ['email' => $message]);
        session()->flash('old', ['email' => $email]);
        return redirect(route('login'));
    }

    public function logout(Request $request): Response
    {
        AuditLogger::log('auth.logout', 'user', auth()->id());
        auth()->logout();
        flash('success', __('auth.logged_out'));
        return redirect(route('home'));
    }

    public function showRegister(Request $request): Response
    {
        if (setting('registration_open') !== '1') {
            flash('info', __('auth.registration_closed'));
        }
        return view('auth/register', ['meta' => $this->meta(__('auth.register.title')) + ['robots' => 'noindex']], 'layouts/auth');
    }

    public function register(Request $request): Response
    {
        if (setting('registration_open') !== '1') {
            throw HttpException::forbidden();
        }
        $this->throttle('register:' . $request->ip(), 10, 3600);
        $data = $this->validate($request, [
            'display_name' => 'required|min:2|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|password|confirmed',
            'locale' => 'required|in:en,fr',
            'accept_terms' => 'accepted',
        ]);
        $id = UserService::register($data);
        $user = db()->fetch('SELECT * FROM users WHERE id = ?', [$id]);
        auth()->login($user);
        AuditLogger::log('auth.registered', 'user', $id, [], $id);
        app('mailer')->send($user['email'], 'welcome', [
            'name' => $user['display_name'],
            'url' => absolute_route('dashboard'),
        ], $user['locale']);
        flash('success', __('auth.registered'));
        return redirect(route('dashboard'));
    }

    public function showForgot(Request $request): Response
    {
        return view('auth/forgot', ['meta' => $this->meta(__('auth.forgot.title')) + ['robots' => 'noindex']], 'layouts/auth');
    }

    public function sendReset(Request $request): Response
    {
        $data = $this->validate($request, ['email' => 'required|email']);
        $this->throttle('reset:ip:' . $request->ip(), 10, 3600);
        if (app('limiter')->attempt('reset:email:' . mb_strtolower($data['email']), 3, 3600)) {
            PasswordResetService::sendLink($data['email']);
        }
        // Same response whether or not the address exists.
        flash('success', __('auth.reset_link_sent'));
        return redirect(route('password.request'));
    }

    public function showReset(Request $request): Response
    {
        $token = (string) $request->param('token');
        $valid = PasswordResetService::findValid($token) !== null;
        return view('auth/reset', [
            'token' => $token,
            'valid' => $valid,
            'meta' => $this->meta(__('auth.reset.title')) + ['robots' => 'noindex'],
        ], 'layouts/auth');
    }

    public function reset(Request $request): Response
    {
        $this->throttle('reset-submit:' . $request->ip(), 20, 3600);
        $data = $this->validate($request, [
            'token' => 'required|regex:/^[a-f0-9]{64}$/',
            'password' => 'required|password|confirmed',
        ]);
        if (!PasswordResetService::reset($data['token'], $data['password'])) {
            flash('error', __('auth.reset_invalid'));
            return redirect(route('password.request'));
        }
        flash('success', __('auth.reset_done'));
        return redirect(route('login'));
    }
}
