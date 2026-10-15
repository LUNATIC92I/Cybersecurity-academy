<?php

// Router for PHP's built-in development server only:
//   php -S 127.0.0.1:8000 -t public bin/dev-router.php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . '/../public' . $path;
if ($path !== '/' && is_file($file) && !str_ends_with($file, '.php')) {
    return false;
}
require __DIR__ . '/../public/index.php';
