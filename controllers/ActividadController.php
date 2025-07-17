<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Actividad.php';

class ActividadController {
    private $db;
    private $conn;
    private $actividad;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->actividad = new Actividad($this->conn);
        header("Content-Type: application/json");
    }

    public function getAll() {
        $filters = [];

        // Obtener filtros desde query params (?fecha_inicio=...&fecha_fin=...&entidad=...&accion=...)
        if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_fin'])) {
            $filters['fecha_inicio'] = $_GET['fecha_inicio'];
            $filters['fecha_fin'] = $_GET['fecha_fin'];
        }
        if (isset($_GET['entidad'])) {
            $filters['entidad'] = $_GET['entidad'];
        }
        if (isset($_GET['accion'])) {
            $filters['accion'] = $_GET['accion'];
        }

        $stmt = $this->actividad->getAll($filters);
        $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($actividades);
        $this->db->closeConnection();
    }
}
