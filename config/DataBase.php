<?php


class Database
{
  private $host;
  private $db_name;
  private $username;
  private $password;
  public $connection;

  public function __construct()
  {
    $this->host = 'localhost';
    $this->db_name = 'jump_api';
    $this->username = 'root';
    $this->password = 'Jude@2023';
  }

  public function getConnection()
  {
    $this->connection = null;
    try {
      $this->connection = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $exception) {
      echo "Connection error: " . $exception->getMessage();
    }
    return $this->connection;
  }
}
