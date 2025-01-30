<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once './config/DataBase.php';
require_once './models/BaseModel.php';
require_once './models/Product.php';
require_once './models/User.php';
require_once './models/ModelFactory.php';


use models\Product;
use models\User;
use models\ModelFactory;
use config\DataBase;
use models\BaseModel;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

$request_method = $_SERVER["REQUEST_METHOD"];
$db = new Database();

$modelName = isset($_GET['model']) ? $_GET['model'] : 'product'; // Default to 'product'
try {
  $model = ModelFactory::create($modelName, $db->getConnection());

  switch ($request_method) {
    case 'GET':
      handleGetRequest($model);
      break;
    case 'POST':
      handlePostRequest($model);
      break;
    case 'PUT':
      handlePutRequest($model);
      break;
    case 'DELETE':
      handleDeleteRequest($model);
      break;
    default:
      jsonResponse(['error' => 'Method Not Allowed'], 405);
      break;
  }
} catch (Exception $e) {
  error_log('Error: ' . $e->getMessage());
  jsonResponse(['error' => 'Internal Server Error'], 500);
}

function handleGetRequest($model)
{
  // Check if an ID is provided in the query parameters
  $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

  if ($id) {
    // Fetch a single product by ID
    $product = $model->getById($id);

    if ($product) {
      jsonResponse([
        'status' => 'success',
        'data' => $product
      ]);
    } else {
      jsonResponse([
        'status' => 'error',
        'data' => ['message' => 'Product not found']
      ], 404);
    }
  } else {
    // If no ID is provided, fetch all products
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $data = $model->getAll($page, $limit);

    jsonResponse([
      'status' => 'success',
      'data' => $data
    ]);
  }
}

function handlePostRequest($model)
{
  $data = json_decode(file_get_contents("php://input"));
  if ($model->create($data)) {
    jsonResponse([
      'status' => 'success',
      'data' => ['message' => 'Resource created successfully']
    ], 201);
  } else {
    jsonResponse([
      'status' => 'error',
      'data' => ['error' => 'Failed to create resource']
    ], 400);
  }
}

function handlePutRequest($model)
{
  $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
  $data = json_decode(file_get_contents("php://input"));

  $product = $model->getById($id);
  if (!$product) {
    jsonResponse([
      'status' => 'error',
      'data' => ['message' => 'Product not found']
    ], 404); // Return 404 status code
  } else {

    // Proceed to update
    if ($model->update($id, $data)) {
      jsonResponse([
        'status' => 'success',
        'data' => ['message' => 'Resource updated successfully']
      ]);
    } else {
      jsonResponse([
        'status' => 'error',
        'data' => ['error' => 'Failed to update resource']
      ], 400);
    }
  }
}

function handleDeleteRequest($model)
{
  $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

  // Check if the product exists before attempting to delete
  $product = $model->getById($id);

  if (!$product) {
    jsonResponse([
      'status' => 'error',
      'data' => ['message' => 'Product not found']
    ], 404); // Return 404 status code
  } else {
    // Proceed to delete
    if ($model->delete($id)) {
      jsonResponse([
        'status' => 'success',
        'data' => ['message' => 'Resource deleted successfully']
      ]);
    } else {
      jsonResponse([
        'status' => 'error',
        'data' => ['message' => 'Failed to delete resource']
      ], 400);
    }
  }
}

function jsonResponse($data, $status = 200)
{
  http_response_code($status);
  header('Content-Type: application/json');
  echo json_encode($data);
}
