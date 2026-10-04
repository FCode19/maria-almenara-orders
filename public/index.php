<?php

declare(strict_types=1);

header('X-Content-Type-Options: nosniff');

header('X-Frame-Options: SAMEORIGIN');

header(
    'Referrer-Policy: strict-origin-when-cross-origin'
);

header(
    'Permissions-Policy: camera=(), microphone=(), geolocation=()'
);

use App\Core\Env;
use App\Core\Router;
use App\Core\Session;

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr(
        $class,
        strlen($prefix)
    );

    $file = BASE_PATH
        . '/app/'
        . str_replace('\\', '/', $relativeClass)
        . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

Env::load(BASE_PATH . '/.env');

date_default_timezone_set(
    getenv('APP_TIMEZONE') ?: 'America/Lima'
);

$debug = filter_var(
    getenv('APP_DEBUG'),
    FILTER_VALIDATE_BOOLEAN
);

if ($debug) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}

Session::start();

$router = new Router();

require BASE_PATH . '/routes/web.php';
require BASE_PATH . '/routes/api.php';

$router->dispatch();