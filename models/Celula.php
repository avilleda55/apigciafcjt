<?php
class Celula {
    private $conn;
    private $table = 'Celulas';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM {$this->table} WHERE CE_ACTIVO = 'A' ORDER BY CE_ID ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE CE_ID = :id AND CE_ACTIVO = 'A' ORDER BY CE_ID ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt;
    }

    public function generateNewId() {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $num = $row ? intval($row['total']) + 1 : 1;
        return 'CE' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function create($id, $data) {
        $query = "INSERT INTO {$this->table} (CE_ID, CE_NOMBRE, CE_UBICACION, CE_RESPONSABLE, CE_ACTIVO) 
                  VALUES (:id, :nombre, :ubicacion, :responsable, 'A')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $data['ce_nombre']);
        $stmt->bindParam(':ubicacion', $data['ce_ubicacion']);
        $stmt->bindParam(':responsable', $data['ce_responsable']);
        return $stmt->execute();
    }

    public function update($id, $data) {
        $query = "UPDATE {$this->table} SET 
                     CE_NOMBRE = :nombre,
                     CE_UBICACION = :ubicacion,
                     CE_RESPONSABLE = :responsable
                  WHERE CE_ID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $data['ce_nombre']);
        $stmt->bindParam(':ubicacion', $data['ce_ubicacion']);
        $stmt->bindParam(':responsable', $data['ce_responsable']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "UPDATE {$this->table} SET CE_ACTIVO = 'I' WHERE CE_ID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
