  <?php
  require_once 'Model.php';

  class ProductModel extends Model
  {
    protected $table = 'products';
    protected $primaryKey = 'id';

    public function __construct($db)
    {
      parent::__construct($db);
    }

    
  }
  ?>