<?php
require_once 'Model.php';

class UserModel extends Model
{
  protected $table = 'users';
  protected $primaryKey = 'id';

  public function __construct($db)
  {
    parent::__construct($db);
  }

  public function findByEmail($email)
  {
    $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $stmt->fetch();
  }

  public function create($data)
  {
    if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
      throw new Exception("Invalid email address");
    }

    return parent::create($data);
  }
}
?>