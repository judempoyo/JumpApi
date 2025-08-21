<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'core/Response.php';
require_once 'core/ErrorHandler.php';
require_once 'models/Model.php';
require_once 'controllers/ApiController.php';
require_once 'routes/routes.php';

// Gestion des pré-vols CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Connexion à la base de données principale
    $database = new Database();
    $db = $database->connect();

    // Debug: vérifier la connexion
    error_log("Connexion DB réussie: " . ($db ? "oui" : "non"));

    // Initialisation du routeur
    $router = new Router($db);
    setupRoutes($router);

    // Gestion de la requête
    $router->handleRequest();

} catch (Exception $e) {
    error_log("Erreur dans index.php: " . $e->getMessage());
    Response::error('Server error: ' . $e->getMessage(), 500);
}
?>