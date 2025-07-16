<?php
class Actividad {
    private $conn;
    private $table = 'Actividad';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar actividades con filtros opcionales
    public function getAll($filters = []) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query .= " AND AC_FECHA BETWEEN :fecha_inicio AND :fecha_fin";
        }
        if (!empty($filters['entidad'])) {
            $query .= " AND AC_ENTIDAD = :entidad";
        }
        if (!empty($filters['accion'])) {
            $query .= " AND AC_ACCION = :accion";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $stmt->bindParam(':fecha_inicio', $filters['fecha_inicio']);
            $stmt->bindParam(':fecha_fin', $filters['fecha_fin']);
        }
        if (!empty($filters['entidad'])) {
            $stmt->bindParam(':entidad', $filters['entidad']);
        }
        if (!empty($filters['accion'])) {
            $stmt->bindParam(':accion', $filters['accion']);
        }

        $stmt->execute();
        return $stmt;
    }

    public function generateNewId() {
        $query = "SELECT AC_ID FROM {$this->table} ORDER BY AC_ID DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $num = $row ? intval(substr($row['AC_ID'], 2)) + 1 : 1;
        return 'AC' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function logAction($data) {
        $query = "INSERT INTO {$this->table} 
            (AC_ID, AC_PER_ID, AC_ENTIDAD, AC_ENTIDAD_ID, AC_ACCION, AC_DATOS_ANTES, AC_DATOS_DESPUES) 
            VALUES (:id, :per_id, :entidad, :entidad_id, :accion, :antes, :despues)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['AC_ID']);
        $stmt->bindParam(':per_id', $data['AC_PER_ID']);
        $stmt->bindParam(':entidad', $data['AC_ENTIDAD']);
        $stmt->bindParam(':entidad_id', $data['AC_ENTIDAD_ID']);
        $stmt->bindParam(':accion', $data['AC_ACCION']);
        $stmt->bindParam(':antes', $data['AC_DATOS_ANTES']);
        $stmt->bindParam(':despues', $data['AC_DATOS_DESPUES']);
        return $stmt->execute();
    }
}
