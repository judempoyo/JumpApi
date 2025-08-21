<?php
define('API_VERSION', 'v1');
define('API_BASE_PATH', '/api/' . API_VERSION);

define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'jump_api');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'Jude@2023');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_CHARSET', 'utf8mb4');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, Accept, charset, boundary, Content-Length');
header('Access-Control-Allow-Credentials: true');

date_default_timezone_set('Europe/Paris');

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

spl_autoload_register(function ($className) {
    $directories = [
        __DIR__ . '/../controllers/',
        __DIR__ . '/../models/',
        __DIR__ . '/../utils/',
        __DIR__ . '/'
    ];

    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
?>