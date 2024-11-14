<?php

namespace models;

class User extends BaseModel
{
  public function getAll($page, $limit)
  {
    $offset = ($page - 1) * $limit;
    $query = "SELECT * FROM users LIMIT :limit OFFSET :offset";
    $stmt = $this->connection->prepare($query);
    $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function getById($id)
  {
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $this->executeQuery($query, [$id]);
    return $stmt->fetch(\PDO::FETCH_ASSOC);
  }

  public function create($data)
  {
    if ($this->validate($data)) {
      $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
      return $this->executeQuery($query, [$data->username, $data->email, password_hash($data->password, PASSWORD_DEFAULT)]);
    }
    return false;
  }

  public function update($id, $data)
  {
    if ($this->validate($data)) {
      $query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
      return $this->executeQuery($query, [$data->username, $data->email, $id]);
    }
    return false;
  }

  public function delete($id)
  {
    $query = "DELETE FROM users WHERE id = ?";
    return $this->executeQuery($query, [$id]);
  }

  private function validate($data)
  {
    $errors = [];

    // Check for required fields
    if (empty($data->username)) {
      $errors[] = 'Username is required.';
    } elseif (strlen($data->username) < 3 || strlen($data->username) > 20) {
      $errors[] = 'Username must be between 3 and 20 characters.';
    }

    if (empty($data->email)) {
      $errors[] = 'Email is required.';
    } elseif (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'Email format is invalid.';
    }

    if (empty($data->password)) {
      $errors[] = 'Password is required.';
    } elseif (strlen($data->password) < 6) {
      $errors[] = 'Password must be at least 6 characters.';
    }

    // If there are validation errors, you can return them
    if (!empty($errors)) {
      // Log errors or return them as needed
      foreach ($errors as $error) {
        echo $error . "\n";
      }
      return false;
    }

    return true; // Validation passed
  }
}
