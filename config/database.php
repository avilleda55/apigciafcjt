<?php
class Database {
    private $host = 'ep-summer-sea-aevlmy8b-pooler.c-2.us-east-2.aws.neon.tech';
    private $db_name = 'neondb';
    private $username = 'neondb_owner';
    private $password = 'npg_Sxigy2aYEF5q';
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