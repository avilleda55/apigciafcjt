<?php
class Ingreso {
    private $conn;
    private $table = 'Ingresos';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar todos activos
    public function getAll() {
        $query = "SELECT * FROM {$this->table} WHERE IN_TIPO != 'I' ORDER BY IN_FECHA DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Obtener por ID
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE IN_ID = :id AND IN_TIPO != 'I' ORDER BY IN_FECHA DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Generar nuevo ID
    public function generateNewId() {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $num = $row ? intval($row['total']) + 1 : 1;
        return 'IN' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }


    // Crear ingreso
    public function create($id, $data) {
        $query = "INSERT INTO {$this->table} (IN_ID, IN_CEL_ID, IN_PER_ID, IN_DESCRIPCION, IN_MONTO, IN_FECHA, IN_TIPO) 
                  VALUES (:id, :celula, :persona, :descripcion, :monto, :fecha, :tipo)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':celula', $data['in_cel_id']);
        $stmt->bindParam(':persona', $data['in_per_id']);
        $stmt->bindParam(':descripcion', $data['in_descripcion']);
        $stmt->bindParam(':monto', $data['in_monto']);
        $stmt->bindParam(':fecha', $data['in_fecha']);
        $stmt->bindParam(':tipo', $data['in_tipo']);
        return $stmt->execute();
    }

    // Actualizar ingreso
    public function update($id, $data) {
        $query = "UPDATE {$this->table} SET 
                     IN_CEL_ID = :celula,
                     IN_PER_ID = :persona,
                     IN_DESCRIPCION = :descripcion,
                     IN_MONTO = :monto,
                     IN_FECHA = :fecha,
                     IN_TIPO = :tipo
                  WHERE IN_ID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula', $data['in_cel_id']);
        $stmt->bindParam(':persona', $data['in_per_id']);
        $stmt->bindParam(':descripcion', $data['in_descripcion']);
        $stmt->bindParam(':monto', $data['in_monto']);
        $stmt->bindParam(':fecha', $data['in_fecha']);
        $stmt->bindParam(':tipo', $data['in_tipo']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Borrado lógico (marcando IN_TIPO = 'I' → inactivo)
    public function delete($id) {
        $query = "UPDATE {$this->table} SET IN_TIPO = 'I' WHERE IN_ID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Filtrar por célula
    public function getByCelula($celulaId) {
        $query = "SELECT * FROM {$this->table} WHERE IN_CEL_ID = :celula AND IN_TIPO != 'I'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula', $celulaId);
        $stmt->execute();
        return $stmt;
    }
}
