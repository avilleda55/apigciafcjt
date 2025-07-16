<?php
class Database {
    private $host = 'sql206.infinityfree.com';
    private $db_name = 'if0_39471125_gestion';
    private $username = 'sql206.infinityfree.com';
    private $password = 'sw8GXQMiBzcOqXC';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=".$this->host.";dbname=".$this->db_name.";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo json_encode(['error' => "Connection error: " . $exception->getMessage()]);
            exit;
        }
        return $this->conn;
    }

     public function closeConnection() {
        $this->conn = null;
    }
}