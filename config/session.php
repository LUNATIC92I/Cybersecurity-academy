<?php

use App\Core\Env;

return [
    'name' => 'ca_session',
    'lifetime' => (int) Env::get('SESSION_LIFETIME', 120),
    'secure' => (bool) Env::get('SESSION_SECURE_COOKIE', false),
    'same_site' => 'Lax',
    'path' => dirname(__DIR__) . '/storage/sessions',
];
