<?php
class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct($db, $table = null)
    {
        $this->db = $db;
        if ($table) {
            $this->table = $table;
        }
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function findAll($page = null, $perPage = 10, $orderBy = 'id', $order = 'ASC')
    {
        // Validation des paramètres
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $orderBy = $this->sanitizeFieldName($orderBy);
        
        $query = "SELECT * FROM " . $this->table . " ORDER BY $orderBy $order";
        
        if ($page !== null) {
            $offset = ($page - 1) * $perPage;
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->db->prepare($query);
        
        if ($page !== null) {
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findWithConditions($conditions = [], $page = null, $perPage = 10, $orderBy = 'id', $order = 'ASC')
    {
        $where = '';
        $params = [];
        
        if (!empty($conditions)) {
            $whereParts = [];
            foreach ($conditions as $field => $value) {
                $safeField = $this->sanitizeFieldName($field);
                $whereParts[] = "$safeField = :$field";
                $params[":$field"] = $value;
            }
            $where = "WHERE " . implode(' AND ', $whereParts);
        }
        
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $orderBy = $this->sanitizeFieldName($orderBy);
        
        $query = "SELECT * FROM " . $this->table . " $where ORDER BY $orderBy $order";
        
        if ($page !== null) {
            $offset = ($page - 1) * $perPage;
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->db->prepare($query);
        
        // Bind des paramètres de condition
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if ($page !== null) {
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount($conditions = [])
    {
        $where = '';
        $params = [];
        
        if (!empty($conditions)) {
            $whereParts = [];
            foreach ($conditions as $field => $value) {
                $safeField = $this->sanitizeFieldName($field);
                $whereParts[] = "$safeField = :$field";
                $params[":$field"] = $value;
            }
            $where = "WHERE " . implode(' AND ', $whereParts);
        }
        
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " $where";
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return (int)$stmt->fetch()['total'];
    }

    public function find($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            throw new InvalidArgumentException("ID invalide");
        }

        $query = "SELECT * FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $cleanedData = $this->sanitizeData($data);
        
        $columns = implode(', ', array_keys($cleanedData));
        $placeholders = ':' . implode(', :', array_keys($cleanedData));

        $query = "INSERT INTO " . $this->table . " ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);

        foreach ($cleanedData as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $result = $stmt->execute();
        return $result ? $this->db->lastInsertId() : false;
    }

    public function update($id, $data)
    {
        if (!is_numeric($id) || $id <= 0) {
            throw new InvalidArgumentException("ID invalide");
        }

        $cleanedData = $this->sanitizeData($data);
        
        if (empty($cleanedData)) {
            throw new InvalidArgumentException("Aucune donnée à mettre à jour");
        }

        $set = [];
        foreach ($cleanedData as $key => $value) {
            $set[] = "$key = :$key";
        }
        $set = implode(', ', $set);

        $query = "UPDATE " . $this->table . " SET $set WHERE " . $this->primaryKey . " = :id";
        $stmt = $this->db->prepare($query);

        foreach ($cleanedData as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            throw new InvalidArgumentException("ID invalide");
        }

        $query = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    protected function sanitizeData($data)
    {
        $cleaned = [];
        foreach ($data as $key => $value) {
            $safeKey = $this->sanitizeFieldName($key);
            
            if (is_string($value)) {
                $cleaned[$safeKey] = htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
            } elseif (is_array($value)) {
                $cleaned[$safeKey] = json_encode($value);
            } else {
                $cleaned[$safeKey] = $value;
            }
        }
        return $cleaned;
    }

    protected function sanitizeFieldName($field)
    {
        // Autoriser seulement les caractères alphanumériques et underscores
        return preg_replace('/[^a-zA-Z0-9_]/', '', $field);
    }

    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    public function commit()
    {
        return $this->db->commit();
    }

    public function rollBack()
    {
        return $this->db->rollBack();
    }
}
?>