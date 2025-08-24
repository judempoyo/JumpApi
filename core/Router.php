<?php 
class Router
{
  private $routes = [];
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function addRoute($method, $pattern, $callback)
  {
    $this->routes[] = [
      'method' => $method,
      'pattern' => $pattern,
      'callback' => $callback
    ];
  }
  public function getRoutes()
  {
    return $this->routes;
  }


  public function handleRequest()
  {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    error_log("URI reçue: " . $uri);
    error_log("Base path: " . API_BASE_PATH);

    $basePath = API_BASE_PATH;
    if (strpos($uri, $basePath) === 0) {
      $uri = substr($uri, strlen($basePath));
    }

    $uri = trim($uri, '/');

    error_log("URI traitée: " . $uri);

    if ($uri === '' || $uri === 'home') {
      $this->handleHome();
      return;
    }

    foreach ($this->routes as $route) {
      if ($route['method'] === $method) {
        $pattern = '#^' . $route['pattern'] . '$#';

        if (preg_match($pattern, $uri, $matches)) {
          array_shift($matches);
          call_user_func_array($route['callback'], array_merge([$this->db], $matches));
          return;
        }
      }
    }

    Response::notFound('Endpoint');
  }

  private function handleHome()
  {
    $modelsPath = __DIR__ . '/../models/';
    $endpoints = [];

    if (is_dir($modelsPath)) {
      $modelFiles = scandir($modelsPath);

      foreach ($modelFiles as $file) {
        if ($file === '.' || $file === '..' || $file === 'Model.php') {
          continue;
        }

        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
          $modelName = pathinfo($file, PATHINFO_FILENAME);

          if (substr($modelName, -5) !== 'Model') {
            continue;
          }

          $baseName = substr($modelName, 0, -5);
          $resourceName = strtolower($baseName) . 's';

          $endpoints["GET /{$resourceName}"] = "List all {$resourceName}";
          $endpoints["GET /{$resourceName}/{id}"] = "Get {$resourceName} by ID";
          $endpoints["POST /{$resourceName}"] = "Create new {$resourceName}";
          $endpoints["PUT /{$resourceName}/{id}"] = "Update {$resourceName}";
          $endpoints["DELETE /{$resourceName}/{id}"] = "Delete {$resourceName}";
        }
      }
    }

    if (empty($endpoints)) {
      $endpoints = [
        'GET /users' => 'List all users',
        'GET /users/{id}' => 'Get user by ID',
        'POST /users' => 'Create new user',
        'PUT /users/{id}' => 'Update user',
        'DELETE /users/{id}' => 'Delete user',
      ];
    }

    Response::send([
      'message' => 'JUMP API REST',
      'version' => API_VERSION,
      'endpoints' => $endpoints
    ]);
  }
}