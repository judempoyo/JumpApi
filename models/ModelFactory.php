<?php

namespace models;

class ModelFactory
{
  public static function create($modelName, $db)
  {
    switch (strtolower($modelName)) {
      case 'product':
        return new Product($db);
      case 'user':
        return new User($db);
        // Add more models as needed
      default:
        throw new \Exception("Model not found.");
    }
  }
}
