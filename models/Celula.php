<?php
class Celula {
    private $conn;
    private $table = 'Celulas';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM {$this->table} WHERE CE_ACTIVO = 'A'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE CE_ID = :id AND CE_ACTIVO = 'A'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt;
    }

    public function generateNewId() {
        $query = "SELECT CE_ID FROM {$this->table} ORDER BY CE_ID DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $num = intval(substr($row['CE_ID'], 2)) + 1;
        } else {
            $num = 1;
        }
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
