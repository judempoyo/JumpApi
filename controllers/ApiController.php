<?php
class ApiController
{
  protected $db;
  protected $model;
  protected $requestMethod;
  protected $uri;
  protected $params = [];
  protected $external_config = null;

  public function __construct($db, $model = null)
  {
    $this->db = $db;
    $this->model = $model;
    $this->requestMethod = $_SERVER['REQUEST_METHOD'];
    $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $this->params = $this->getQueryParams();


  }

  protected function getQueryParams()
  {
    $params = [];


    if (isset($_SERVER['QUERY_STRING'])) {
      parse_str($_SERVER['QUERY_STRING'], $params);
    }
    
    if (isset($_GET['id'])) {
      $params['id'] = $_GET['id'];
    }
    
 
    return $params;
  }


  public function setModel($model)
  {
    $this->model = $model;
  }

  public function getModel()
  {
    return $this->model;
  }
  protected function getRequestBody()
  {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
  }

  public function handleRequest()
  {
    try {
      switch ($this->requestMethod) {
        case 'GET':
          if (isset($this->params['id'])) {
            return $this->get($this->params['id']);
          } else {
            return $this->getAll();
          }
          break;
        case 'POST':
          return $this->create();
          break;
        case 'PUT':
          return $this->update();
          break;
        case 'DELETE':
          return $this->delete();
          break;
        default:
          Response::error('Method not allowed', 405);
      }
    } catch (Exception $e) {
      Response::error($e->getMessage(), 500);
    }
  }

  protected function getAll()
  {
    $data = $this->model->findAll();
    Response::send($data);
  }

  protected function get($id)
  {
    $data = $this->model->find($id);
    if (!$data) {
      Response::notFound();
    }
    Response::send($data);
  }

  protected function create()
  {
    $data = $this->getRequestBody();
    if (empty($data)) {
      Response::badRequest('No data provided');
    }

    $result = $this->model->create($data);
    if ($result) {
      Response::send(['id' => $this->db->lastInsertId()], 201, 'Created successfully');
    } else {
      Response::error('Failed to create resource');
    }
  }

  protected function update()
  {
    if (!isset($this->params['id'])) {
      Response::badRequest('ID parameter required');
    }

    $data = $this->getRequestBody();
    if (empty($data)) {
      Response::badRequest('No data provided for update');
    }

    $result = $this->model->update($this->params['id'], $data);
    if ($result) {
      Response::send(null, 200, 'Updated successfully');
    } else {
      Response::error('Failed to update resource');
    }
  }

  protected function delete()
  {
    if (!isset($this->params['id'])) {
      Response::badRequest('ID parameter required');
    }

    $result = $this->model->delete($this->params['id']);
    if ($result) {
      Response::send(null, 200, 'Deleted successfully');
    } else {
      Response::error('Failed to delete resource');
    }
  }
}
?>