<?php

use App\Core\Env;

return [
    'driver' => Env::get('MAIL_DRIVER', 'log'),
    'host' => Env::get('MAIL_HOST', ''),
    'port' => (int) Env::get('MAIL_PORT', 587),
    'username' => Env::get('MAIL_USERNAME', ''),
    'password' => Env::get('MAIL_PASSWORD', ''),
    'encryption' => Env::get('MAIL_ENCRYPTION', 'tls'),
    'from_address' => Env::get('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
    'from_name' => Env::get('MAIL_FROM_NAME', 'Cybersecurity-Academy'),
];
