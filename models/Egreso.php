<?php
class Egreso {
    private $conn;
    private $table = 'Egresos';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar activos
    public function getAll() {
        $query = "SELECT * FROM {$this->table} WHERE EG_ACTIVO = 'A' ORDER BY EG_FECHA DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener por ID
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE EG_ID = :id AND EG_ACTIVO = 'A' ORDER BY EG_FECHA DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt;
    }

    // Generar nuevo ID
    public function generateNewId() {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $num = $row ? intval($row['total']) + 1 : 1;
        return 'EG' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    // Crear egreso
    public function create($id, $data) {
        $query = "INSERT INTO {$this->table} (EG_ID, EG_CEL_ID, EG_PER_ID, EG_DESCRIPCION, EG_MONTO, EG_FECHA, EG_TIPO, EG_ACTIVO) 
                  VALUES (:id, :celula, :persona, :descripcion, :monto, :fecha, :tipo, 'A')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':celula', $data['eg_cel_id']);
        $stmt->bindParam(':persona', $data['eg_per_id']);
        $stmt->bindParam(':descripcion', $data['eg_descripcion']);
        $stmt->bindParam(':monto', $data['eg_monto']);
        $stmt->bindParam(':fecha', $data['eg_fecha']);
        $stmt->bindParam(':tipo', $data['eg_tipo']);
        return $stmt->execute();
    }

    // Actualizar egreso
    public function update($id, $data) {
        $query = "UPDATE {$this->table} SET 
                     EG_CEL_ID = :celula,
                     EG_PER_ID = :persona,
                     EG_DESCRIPCION = :descripcion,
                     EG_MONTO = :monto,
                     EG_FECHA = :fecha,
                     EG_TIPO = :tipo
                  WHERE EG_ID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula', $data['eg_cel_id']);
        $stmt->bindParam(':persona', $data['eg_per_id']);
        $stmt->bindParam(':descripcion', $data['eg_descripcion']);
        $stmt->bindParam(':monto', $data['eg_monto']);
        $stmt->bindParam(':fecha', $data['eg_fecha']);
        $stmt->bindParam(':tipo', $data['eg_tipo']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Borrado lógico
    public function delete($id) {
        $query = "UPDATE {$this->table} SET EG_ACTIVO = 'I' WHERE EG_ID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Filtrar por célula
    public function getByCelula($celulaId) {
        $query = "SELECT * FROM {$this->table} WHERE EG_CEL_ID = :celula AND EG_ACTIVO = 'A'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula', $celulaId);
        $stmt->execute();
        return $stmt;
    }
}
