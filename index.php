<?php
require_once __DIR__ . '/vendor/autoload.php';

require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'core/Response.php';
require_once 'core/ErrorHandler.php';
require_once 'models/Model.php';
require_once 'controllers/ApiController.php';
require_once 'routes/routes.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  http_response_code(200);
  exit;
}

try {
  $database = new Database();
  $db = $database->connect();

  error_log("Connexion DB réussie: " . ($db ? "oui" : "non"));

  $router = new Router($db);
  setupRoutes($router);

  $router->handleRequest();

} catch (Exception $e) {
  error_log("Erreur dans index.php: " . $e->getMessage());
  Response::error('Server error: ' . $e->getMessage(), 500);
}
?>