<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

// Chargement de la configuration
require_once 'config/config.php';

// Chargement des dépendances principales
require_once 'config/Database.php';
require_once 'core/Response.php';
require_once 'core/ErrorHandler.php';
require_once 'models/Model.php';
require_once 'controllers/ApiController.php';

// Vérification de la maintenance
if (file_exists(__DIR__ . '/maintenance.flag') && !isset($_GET['bypass'])) {
    Response::error('Service temporarily unavailable for maintenance', 503, null, 'MAINTENANCE_MODE');
}

try {
    // Initialisation de la base de données
    $database = new Database();
    $db = $database->connect();

    // Log de connexion réussie
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        error_log("Connexion DB réussie");
    }

    // Initialisation du router
    $router = new Router($db);
    
    // Chargement des routes
    if (file_exists(__DIR__ . '/routes/routes.php')) {
        require_once __DIR__ . '/routes/routes.php';
        
        if (function_exists('setupRoutes')) {
            setupRoutes($router);
        } else {
            throw new Exception('setupRoutes function not found in routes.php');
        }
    } else {
        throw new Exception('Routes file not found');
    }

    // Gestion de la requête
    $router->handleRequest();

}catch (Throwable $e) {
    error_log("Erreur attrapée dans index.php : " . $e->getMessage());
    Response::error("Debug: " . $e->getMessage(), 500, [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], 'DEBUG_ERROR');
}
 catch (PDOException $e) {
    error_log("Erreur de connexion DB: " . $e->getMessage());
    Response::error('Database connection failed', 500, null, 'DB_CONNECTION_ERROR');
    
} catch (Exception $e) {
    error_log("Erreur dans index.php: " . $e->getMessage());
    Response::error('Server error occurred', 500, null, 'SERVER_ERROR');
    
} catch (Throwable $e) {
    error_log("Erreur fatale dans index.php: " . $e->getMessage());
    Response::error('Fatal server error', 500, null, 'FATAL_ERROR');
}

// Log de fin de requête
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024;
    $executionTime = microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
    error_log(sprintf("Request completed: %.2fMB memory, %.3fs", $memoryUsage, $executionTime));
}
?>