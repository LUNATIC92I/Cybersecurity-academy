<?php

use App\Core\Env;

return [
    'host' => Env::get('DB_HOST', '127.0.0.1'),
    'port' => (int) Env::get('DB_PORT', 3306),
    'socket' => Env::get('DB_SOCKET', ''),
    'database' => Env::get('DB_DATABASE', 'cyberacademy'),
    'username' => Env::get('DB_USERNAME', 'academy'),
    'password' => Env::get('DB_PASSWORD', ''),
];
