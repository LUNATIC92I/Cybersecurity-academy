<?php

declare(strict_types=1);

/*
 * Front controller. The web server's document root must be this public/
 * directory so that application code, .env, logs and uploads stay outside it.
 */

use App\Core\App;
use App\Core\Env;
use App\Core\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new App(dirname(__DIR__));

if (!config('app.debug')) {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/logs/php-error.log');
ini_set('expose_php', '0');
header_remove('X-Powered-By');

$app->handle(Request::fromGlobals())->send();
