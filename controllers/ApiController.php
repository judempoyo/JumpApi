<?php
class ApiController
{
    protected $db;
    protected $model;
    protected $requestMethod;
    protected $uri;
    protected $params = [];
    protected $allowedOrigins = ['http://localhost:8000', '*'];

    public function __construct($db, $model = null)
    {
        $this->db = $db;
        $this->model = $model;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->params = $this->getQueryParams();

        $this->handleCors();
        $this->validateRequest();
    }

    protected function handleCors()
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array($origin, $this->allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 3600');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    protected function validateRequest()
    {
        // Vérifier le Content-Type pour les requêtes POST/PUT
        if (in_array($this->requestMethod, ['POST', 'PUT'])) {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (stripos($contentType, 'application/json') === false) {
                Response::error('Content-Type must be application/json', 415);
            }
        }

        // Limiter la taille du corps de la requête
        if ($_SERVER['CONTENT_LENGTH'] > 1000000) { // 1MB max
            Response::error('Request body too large', 413);
        }
    }

    protected function getQueryParams()
    {
        $params = [];
        
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $params);
        }
        
        // Nettoyer tous les paramètres
        foreach ($params as $key => $value) {
            $params[$key] = $this->sanitizeInput($value);
        }
        
        return $params;
    }

    protected function sanitizeInput($input)
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        
        if (is_numeric($input)) {
            return $input;
        }
        
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
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
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('Invalid JSON format', 400);
        }
        
        return $this->sanitizeInput($data);
    }

    public function handleRequest()
    {
        try {
            $this->validateToken();

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
        // Paramètres de pagination
        $page = isset($this->params['page']) ? (int)$this->params['page'] : 1;
        $perPage = isset($this->params['per_page']) ? (int)$this->params['per_page'] : 10;
        $orderBy = isset($this->params['order_by']) ? $this->params['order_by'] : 'id';
        $order = isset($this->params['order']) ? $this->params['order'] : 'ASC';

        // Validation
        if ($page < 1) $page = 1;
        if ($perPage < 1 || $perPage > 100) $perPage = 10;

        // Conditions de filtrage (exclure les paramètres de pagination)
        $filterParams = array_diff_key($this->params, array_flip(['page', 'per_page', 'order_by', 'order']));
        $conditions = [];
        
        foreach ($filterParams as $key => $value) {
            if (!empty($value)) {
                $conditions[$key] = $value;
            }
        }

        // Récupérer les données
        $data = $this->model->findWithConditions($conditions, $page, $perPage, $orderBy, $order);
        $total = $this->model->getTotalCount($conditions);
        $totalPages = ceil($total / $perPage);

        // Préparer la réponse
        $response = [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1,
                'next_page' => $page < $totalPages ? $page + 1 : null,
                'prev_page' => $page > 1 ? $page - 1 : null
            ],
            'filters' => $conditions
        ];

        Response::send($response);
    }

    protected function get($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID invalide');
        }

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

        $id = $this->model->create($data);
        if ($id) {
            Response::send(['id' => $id], 201, 'Created successfully');
        } else {
            Response::error('Failed to create resource');
        }
    }

    protected function update()
    {
        if (!isset($this->params['id'])) {
            Response::badRequest('ID parameter required');
        }

        $id = $this->params['id'];
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID invalide');
        }

        $data = $this->getRequestBody();
        if (empty($data)) {
            Response::badRequest('No data provided for update');
        }

        $result = $this->model->update($id, $data);
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

        $id = $this->params['id'];
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID invalide');
        }

        $result = $this->model->delete($id);
        if ($result) {
            Response::send(null, 200, 'Deleted successfully');
        } else {
            Response::error('Failed to delete resource');
        }
    }

    protected function validateToken()
    {
        // Exclure les méthodes OPTIONS et les routes publiques
        if ($this->requestMethod === 'OPTIONS') {
            return true;
        }

        $headers = getallheaders();
        $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

        if (!$this->isValidToken($token)) {
            Response::error('Token d\'authentification invalide ou manquant', 401);
        }
    }

    protected function isValidToken($token)
    {
        // Implémentation basique - à remplacer par votre logique JWT
        /* if (empty($token)) {
            return false;
        }

        // Exemple simple - en production, utilisez une bibliothèque JWT
        $secret = 'votre_secret_jwt';
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        } */

        // Ici, vous devriez valider la signature JWT
        // Pour l'exemple, on accepte tout token non vide
        //return !empty($token);
        return true;
    }
}
?>