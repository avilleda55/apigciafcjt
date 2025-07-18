<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Dashboard.php';

class DashboardController {
    private $db;
    private $conn;
    private $dashboard;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->dashboard = new Dashboard($this->conn);
        header("Content-Type: application/json");
    }

    public function getDashboard() {
        $data = json_decode(file_get_contents("php://input"), true);
        $rol = $data['rol'] ?? null;
        $celulaId = $data['celula_id'] ?? null;

        if (!$rol) {
            http_response_code(400);
            echo json_encode(['message' => 'Rol requerido']);
            return;
        }

        $summary = $this->dashboard->getSummary($rol, $celulaId);
        $combinedList = $this->dashboard->getCombinedList($rol, $celulaId);
        $monthlyEvolution = $this->dashboard->getMonthlyEvolution($rol, $celulaId);
        $barChartData = $this->dashboard->getBarChartData($rol, $celulaId);

        echo json_encode([
            'summary' => $summary,
            'combinedList' => $combinedList,
            'monthlyEvolution' => $monthlyEvolution,
            'barChartData' => $barChartData
        ]);

        $this->db->closeConnection();
    }
}
