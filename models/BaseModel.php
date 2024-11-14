<?php

namespace models;

abstract class BaseModel
{
  protected $connection;

  public function __construct($db)
  {
    $this->connection = $db;
  }

  abstract public function getAll($page, $limit);
  abstract public function getById($id);
  abstract public function create($data);
  abstract public function update($id, $data);
  abstract public function delete($id);

  // Common method to execute a query
  protected function executeQuery($query, $params = [])
  {
    $stmt = $this->connection->prepare($query);
    $stmt->execute($params);
    return $stmt;
  }
}
