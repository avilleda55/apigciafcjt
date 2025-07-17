<?php
class Database {
    private $host = 'db.cazhqayrvvtunzbsylcq.supabase.co';
    private $db_name = 'postgres';
    private $username = 'postgres';
    private $password = 'Nhghdtcd55*';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "pgsql:host={$this->host};port=5432;dbname={$this->db_name};sslmode=require";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn = null;
    }
}