<?php

namespace models;

class Product extends BaseModel
{
  public function getAll($page, $limit)
  {
    $offset = ($page - 1) * $limit;
    $query = "SELECT * FROM products LIMIT :limit OFFSET :offset";
    $stmt = $this->connection->prepare($query);
    $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function getById($id)
  {
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $this->executeQuery($query, [$id]);
    return $stmt->fetch(\PDO::FETCH_ASSOC);
  }

  public function create($data)
  {
    if ($this->validate($data)) {
      $query = "INSERT INTO products (name, price, quantity) VALUES (?, ?, ?)";
      return $this->executeQuery($query, [$data->name, $data->price, $data->quantity]);
    }
    return false;
  }

  public function update($id, $data)
  {

    if ($this->validate($data)) {
      $query = "UPDATE products SET name = ?, price = ?, quantity = ? WHERE id = ?";
      return $this->executeQuery($query, [$data->name, $data->price, $data->quantity, $id]);
    }
    return false;
  }

  public function delete($id)
  {
    $query = "DELETE FROM products WHERE id = ?";
    return $this->executeQuery($query, [$id]);
  }

  private function validate($data)
  {
    $errors = [];

    // Check for required fields
    if (empty($data->name)) {
      $errors[] = 'Name is required.';
    } elseif (strlen($data->name) > 255) {
      $errors[] = 'Name must be less than 255 characters.';
    }

    if (empty($data->price)) {
      $errors[] = 'Price is required.';
    } elseif (!is_numeric($data->price) || $data->price < 0) {
      $errors[] = 'Price must be a non-negative number.';
    }

    if (empty($data->quantity)) {
      $errors[] = 'Quantity is required.';
    } elseif (!is_numeric($data->quantity) || $data->quantity < 0) {
      $errors[] = 'Quantity must be a non-negative integer.';
    }

    // If there are validation errors, you can return them
    if (!empty($errors)) {
      // Log errors or return them as needed
      // For now, we will just print them
      foreach ($errors as $error) {
        echo $error . "\n";
      }
      return false;
    }

    return true; // Validation passed
  }
}
