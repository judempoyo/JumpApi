<?php
class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;
    private $external_config = null;

    public function __construct($external_config = null)
    {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->port = DB_PORT;
        $this->external_config = $external_config;
    }

    public function connect()
    {
        $this->conn = null;

        if ($this->external_config) {
            $this->host = $this->external_config['host'] ?? DB_HOST;
            $this->db_name = $this->external_config['db_name'] ?? DB_NAME;
            $this->username = $this->external_config['username'] ?? DB_USER;
            $this->password = $this->external_config['password'] ?? DB_PASS;
            $this->port = $this->external_config['port'] ?? DB_PORT;
        }

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=" . DB_CHARSET;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            return $this->conn;
        } catch (PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
?>