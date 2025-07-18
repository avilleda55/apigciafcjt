<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Egreso.php';

class EgresoController {
    private $db;
    private $conn;
    private $egreso;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->egreso = new Egreso($this->conn);
        header("Content-Type: application/json");
    }

    public function getAll() {
        $stmt = $this->egreso->getAll();
        $egresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($egresos);
        $this->db->closeConnection();
    }

    public function getById($id) {
        $stmt = $this->egreso->getById($id);
        $egreso = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($egreso) {
            echo json_encode($egreso);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Egreso no encontrado']);
        }
        $this->db->closeConnection();
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        $currentUserId = $data['current_user_id'] ?? null;

        if (empty($data['eg_cel_id']) || empty($data['eg_per_id']) || empty($data['eg_monto']) || empty($data['eg_fecha']) || empty($currentUserId)) {
            http_response_code(400);
            echo json_encode(['message' => 'Datos incompletos']);
            return;
        }

        $newId = $this->egreso->generateNewId();
        $this->egreso->create($newId, $data);

        require_once __DIR__ . '/../models/Actividad.php';
        $actividad = new Actividad($this->conn);
        $actividad->logAction([
            'AC_ID' => $actividad->generateNewId(),
            'AC_PER_ID' => $currentUserId,
            'AC_ENTIDAD' => 'Egresos',
            'AC_ENTIDAD_ID' => $newId,
            'AC_ACCION' => 'CREAR',
            'AC_DATOS_ANTES' => '',
            'AC_DATOS_DESPUES' => json_encode($data)
        ]);

        echo json_encode(['message' => 'Egreso creado', 'EG_ID' => $newId]);
        $this->db->closeConnection();
    }

    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $currentUserId = $data['current_user_id'] ?? null;

        $antes = $this->egreso->getById($id)->fetch(PDO::FETCH_ASSOC);
        $updated = $this->egreso->update($id, $data);

        if ($updated) {
            require_once __DIR__ . '/../models/Actividad.php';
            $actividad = new Actividad($this->conn);
            $actividad->logAction([
                'AC_ID' => $actividad->generateNewId(),
                'AC_PER_ID' => $currentUserId,
                'AC_ENTIDAD' => 'Egresos',
                'AC_ENTIDAD_ID' => $id,
                'AC_ACCION' => 'ACTUALIZAR',
                'AC_DATOS_ANTES' => json_encode($antes),
                'AC_DATOS_DESPUES' => json_encode($data)
            ]);

            echo json_encode(['message' => 'Egreso actualizado']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Egreso no encontrado']);
        }
        $this->db->closeConnection();
    }

    public function delete($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $currentUserId = $data['current_user_id'] ?? null;

        $antes = $this->egreso->getById($id)->fetch(PDO::FETCH_ASSOC);
        $deleted = $this->egreso->delete($id);

        if ($deleted) {
            require_once __DIR__ . '/../models/Actividad.php';
            $actividad = new Actividad($this->conn);
            $actividad->logAction([
                'AC_ID' => $actividad->generateNewId(),
                'AC_PER_ID' => $currentUserId,
                'AC_ENTIDAD' => 'Egresos',
                'AC_ENTIDAD_ID' => $id,
                'AC_ACCION' => 'ELIMINAR',
                'AC_DATOS_ANTES' => json_encode($antes),
                'AC_DATOS_DESPUES' => ''
            ]);

            echo json_encode(['message' => 'Egreso eliminado (lógico)']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Egreso no encontrado']);
        }
        $this->db->closeConnection();
    }


        // Filtrar por célula
    public function getByCelula($celulaId) {
        $stmt = $this->egreso->getByCelula($celulaId);
        $egresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($egresos) {
            echo json_encode($egresos);
        } else {
            echo json_encode(['message' => 'No hay egresos para esta célula']);
        }
        $this->db->closeConnection();
    }

}
