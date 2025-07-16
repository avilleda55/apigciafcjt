<?php
require_once '../config/database.php';
require_once '../models/Celula.php';

class CelulaController {
    private $db;
    private $conn;
    private $celula;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->celula = new Celula($this->conn);
        header("Content-Type: application/json");
    }

    public function getAll() {
        $stmt = $this->celula->getAll();
        $celulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($celulas);
        $this->db->closeConnection();
    }

    public function getById($id) {
        $stmt = $this->celula->getById($id);
        $celula = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($celula) {
            echo json_encode($celula);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Celula no encontrada']);
        }
        $this->db->closeConnection();
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        $currentUserId = $data['current_user_id'] ?? null;

        if (empty($data['CE_NOMBRE']) || empty($data['CE_RESPONSABLE']) || empty($currentUserId)) {
            http_response_code(400);
            echo json_encode(['message' => 'Datos incompletos']);
            return;
        }

        $newId = $this->celula->generateNewId();
        $this->celula->create($newId, $data);

        require_once '../models/Actividad.php';
        $actividad = new Actividad($this->conn);
        $actividad->logAction([
            'AC_ID' => $actividad->generateNewId(),
            'AC_PER_ID' => $currentUserId,
            'AC_ENTIDAD' => 'Celulas',
            'AC_ENTIDAD_ID' => $newId,
            'AC_ACCION' => 'CREAR',
            'AC_DATOS_ANTES' => '',
            'AC_DATOS_DESPUES' => json_encode($data)
        ]);

        echo json_encode(['message' => 'Celula creada', 'CE_ID' => $newId]);
        $this->db->closeConnection();
    }


    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $currentUserId = $data['current_user_id'] ?? null;

        $antes = $this->celula->getById($id)->fetch(PDO::FETCH_ASSOC);
        $updated = $this->celula->update($id, $data);

        if ($updated) {
            require_once '../models/Actividad.php';
            $actividad = new Actividad($this->conn);
            $actividad->logAction([
                'AC_ID' => $actividad->generateNewId(),
                'AC_PER_ID' => $currentUserId,
                'AC_ENTIDAD' => 'Celulas',
                'AC_ENTIDAD_ID' => $id,
                'AC_ACCION' => 'ACTUALIZAR',
                'AC_DATOS_ANTES' => json_encode($antes),
                'AC_DATOS_DESPUES' => json_encode($data)
            ]);

            echo json_encode(['message' => 'Celula actualizada']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Celula no encontrada']);
        }
        $this->db->closeConnection();
    }

    public function delete($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $currentUserId = $data['current_user_id'] ?? null;

        $antes = $this->celula->getById($id)->fetch(PDO::FETCH_ASSOC);
        $deleted = $this->celula->delete($id);

        if ($deleted) {
            require_once '../models/Actividad.php';
            $actividad = new Actividad($this->conn);
            $actividad->logAction([
                'AC_ID' => $actividad->generateNewId(),
                'AC_PER_ID' => $currentUserId,
                'AC_ENTIDAD' => 'Celulas',
                'AC_ENTIDAD_ID' => $id,
                'AC_ACCION' => 'ELIMINAR',
                'AC_DATOS_ANTES' => json_encode($antes),
                'AC_DATOS_DESPUES' => ''
            ]);

            echo json_encode(['message' => 'Celula eliminada (lógica)']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Celula no encontrada']);
        }
        $this->db->closeConnection();
    }
}
