<?php

function setupRoutes($router)
{
  $modelsPath = __DIR__ . '/../models/';

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
        $resourceName = strtolower($baseName);

        $resourceNamePlural = $resourceName . 's';

        require_once $modelsPath . $file;

        $router->addRoute('GET', $resourceNamePlural, function ($db) use ($modelName) {
          $controller = new ApiController($db);
          $controller->setModel(new $modelName($db));
          $controller->handleRequest();
        });

        $router->addRoute('GET', $resourceNamePlural . '/(\d+)', function ($db, $id) use ($modelName) {
          $_GET['id'] = $id;
          $controller = new ApiController($db);
          $controller->setModel(new $modelName($db));
          $controller->handleRequest();
        });

        $router->addRoute('POST', $resourceNamePlural, function ($db) use ($modelName) {
          $controller = new ApiController($db);
          $controller->setModel(new $modelName($db));
          $controller->handleRequest();
        });

        $router->addRoute('PUT', $resourceNamePlural . '/(\d+)', function ($db, $id) use ($modelName) {
          $controller = new ApiController($db);
          $controller->setModel(new $modelName($db));
          $_GET['id'] = $id;
          $controller->handleRequest();
        });

        $router->addRoute('DELETE', $resourceNamePlural . '/(\d+)', function ($db, $id) use ($modelName) {
          $controller = new ApiController($db);
          $controller->setModel(new $modelName($db));
          $_GET['id'] = $id;
          $controller->handleRequest();
        });

        error_log("Route générée pour: /$resourceNamePlural (Modèle: $modelName)");
      }
    }
  }

  if (empty($router->getRoutes())) {
    error_log("Aucun modèle trouvé, utilisation des routes par défaut");

    $router->addRoute('GET', 'users', function ($db) {
      $controller = new ApiController($db);
      $controller->setModel(new UserModel($db));
      $controller->handleRequest();
    });

    $router->addRoute('GET', 'users/(\d+)', function ($db, $id) {
      $_GET['id'] = $id;
      $controller = new ApiController($db);
      $controller->setModel(new UserModel($db));
      $controller->handleRequest();
    });

    $router->addRoute('POST', 'users', function ($db) {
      $controller = new ApiController($db);
      $controller->setModel(new UserModel($db));
      $controller->handleRequest();
    });

    $router->addRoute('PUT', 'users/(\d+)', function ($db, $id) {
      $controller = new ApiController($db);
      $controller->setModel(new UserModel($db));
      $_GET['id'] = $id;
      $controller->handleRequest();
    });

    $router->addRoute('DELETE', 'users/(\d+)', function ($db, $id) {
      $controller = new ApiController($db);
      $controller->setModel(new UserModel($db));
      $_GET['id'] = $id;
      $controller->handleRequest();
    });
  } else {
    error_log("Routes générées: " . count($router->getRoutes()));
  }
}
?>