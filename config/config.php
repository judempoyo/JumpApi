<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);

// Configuration de l'API
define('API_VERSION', $_ENV['API_VERSION'] ?? 'v1');
define('API_BASE_PATH', '/api/' . API_VERSION);
define('ENVIRONMENT', $_ENV['ENVIRONMENT'] ?? 'development');

// Configuration de la base de données
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// Configuration CORS
define('CORS_ALLOWED_ORIGINS', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*');

// Configuration Timezone
$timezone = $_ENV['TIMEZONE'] ?? 'Europe/Paris';
date_default_timezone_set($timezone);

// Headers de sécurité de base
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

if (ENVIRONMENT === 'production') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Configuration CORS dynamique
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = explode(',', CORS_ALLOWED_ORIGINS);

if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, Accept, charset, boundary, Content-Length, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 3600');

// Gestion des pré-vols OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autoloader
spl_autoload_register(function ($className) {
    $directories = [
        __DIR__ . '/../controllers/',
        __DIR__ . '/../models/',
        __DIR__ . '/../core/',
        __DIR__ . '/../utils/',
        __DIR__ . '/../middleware/',
        __DIR__ . '/'
    ];

    $fileName = $className . '.php';

    foreach ($directories as $directory) {
        $filePath = $directory . $fileName;
        if (file_exists($filePath)) {
            require_once $filePath;
            return;
        }
    }

    if (ENVIRONMENT === 'development') {
        error_log("Autoloader: Class $className not found");
    }
});

// Initialisation des logs
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Configuration PHP
ini_set('memory_limit', $_ENV['MEMORY_LIMIT'] ?? '128M');
ini_set('max_execution_time', $_ENV['MAX_EXECUTION_TIME'] ?? 30);
?>