<?php

use App\Core\Env;

return [
    'name' => 'Cybersecurity-Academy',
    'env' => Env::get('APP_ENV', 'production'),
    'debug' => (bool) Env::get('APP_DEBUG', false),
    'url' => rtrim((string) Env::get('APP_URL', 'http://localhost:8000'), '/'),
    'key' => (string) Env::get('APP_KEY', ''),
    'locale' => 'en',
    'locales' => ['en', 'fr'],
    'support_email' => (string) Env::get('SUPPORT_EMAIL', 'support@example.com'),
    'demo_mode' => (bool) Env::get('APP_DEMO_MODE', true),
];
