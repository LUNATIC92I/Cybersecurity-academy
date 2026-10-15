<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\UserService;

/**
 * Demonstration accounts for local evaluation only. Passwords come from the
 * DEMO_PASSWORD environment variable or are generated randomly and printed
 * once; nothing is hard-coded. Never run with --demo in production.
 */
final class DemoSeeder
{
    /** @var callable */
    private $out;

    public function __construct(callable $out)
    {
        $this->out = $out;
    }

    public function run(): string
    {
        if (config('app.env') === 'production') {
            throw new \RuntimeException('Refusing to create demonstration accounts in production.');
        }
        $password = (string) (\App\Core\Env::get('DEMO_PASSWORD') ?: ('Demo-' . bin2hex(random_bytes(6)) . 'A1'));
        $accounts = [
            ['admin@demo.academy.test', 'Demo Administrator', 'super_admin'],
            ['instructor@demo.academy.test', 'Demo Instructor', 'instructor'],
            ['learner@demo.academy.test', 'Demo Learner', 'student'],
        ];
        foreach ($accounts as [$email, $name, $role]) {
            if (db()->value('SELECT COUNT(*) FROM users WHERE email = ?', [$email])) {
                continue;
            }
            $id = UserService::register(['email' => $email, 'password' => $password, 'display_name' => $name, 'locale' => 'en']);
            if ($role !== 'student') {
                UserService::assignRole($id, $role);
            }
            db()->update('users', ['email_verified_at' => now()], ['id' => $id]);
            ($this->out)("  Demo account: $email ($role)");
        }
        ($this->out)("  Demo password: $password");
        ($this->out)('  These accounts are for local evaluation only. Delete them before deploying.');
        return '3 accounts';
    }
}
