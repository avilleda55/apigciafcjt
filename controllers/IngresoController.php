<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Ingreso.php';

class IngresoController {
    private $db;
    private $conn;
    private $ingreso;


    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->ingreso = new Ingreso($this->conn);
        header("Content-Type: application/json");
    }

    // Listar todos
    public function getAll() {
        $stmt = $this->ingreso->getAll();
        $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($ingresos);
        $this->db->closeConnection();
    }

    // Obtener por ID
    public function getById($id) {
        $stmt = $this->ingreso->getById($id);
        $ingreso = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($ingreso) {
            echo json_encode($ingreso);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Ingreso no encontrado']);
        }
        $this->db->closeConnection();
    }

    // Crear
    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        $currentUserId = $data['current_user_id'] ?? null;

        if (empty($data['in_cel_id']) || empty($data['in_per_id']) || empty($data['in_monto']) || empty($data['in_fecha']) || empty($currentUserId)) {
            http_response_code(400);
            echo json_encode(['message' => 'Datos incompletos']);
            return;
        }

        $newId = $this->ingreso->generateNewId();
        $this->ingreso->create($newId, $data);

        require_once __DIR__ . '/../models/Actividad.php';
        $actividad = new Actividad($this->conn);
        $actividad->logAction([
            'AC_ID' => $actividad->generateNewId(),
            'AC_PER_ID' => $currentUserId,
            'AC_ENTIDAD' => 'Ingresos',
            'AC_ENTIDAD_ID' => $newId,
            'AC_ACCION' => 'CREAR',
            'AC_DATOS_ANTES' => '',
            'AC_DATOS_DESPUES' => json_encode($data)
        ]);

        echo json_encode(['message' => 'Ingreso creado', 'IN_ID' => $newId]);
        $this->db->closeConnection();
    }


    // Actualizar
    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $currentUserId = $data['current_user_id'] ?? null;

        $antes = $this->ingreso->getById($id)->fetch(PDO::FETCH_ASSOC);
        $updated = $this->ingreso->update($id, $data);

        if ($updated) {
            require_once __DIR__ . '/../models/Actividad.php';
            $actividad = new Actividad($this->conn);
            $actividad->logAction([
                'AC_ID' => $actividad->generateNewId(),
                'AC_PER_ID' => $currentUserId,
                'AC_ENTIDAD' => 'Ingresos',
                'AC_ENTIDAD_ID' => $id,
                'AC_ACCION' => 'ACTUALIZAR',
                'AC_DATOS_ANTES' => json_encode($antes),
                'AC_DATOS_DESPUES' => json_encode($data)
            ]);

            echo json_encode(['message' => 'Ingreso actualizado']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Ingreso no encontrado']);
        }
        $this->db->closeConnection();
    }

    // Eliminar lógico
    public function delete($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $currentUserId = $data['current_user_id'] ?? null;

        $antes = $this->ingreso->getById($id)->fetch(PDO::FETCH_ASSOC);
        $deleted = $this->ingreso->delete($id);

        if ($deleted) {
            require_once __DIR__ . '/../models/Actividad.php';
            $actividad = new Actividad($this->conn);
            $actividad->logAction([
                'AC_ID' => $actividad->generateNewId(),
                'AC_PER_ID' => $currentUserId,
                'AC_ENTIDAD' => 'Ingresos',
                'AC_ENTIDAD_ID' => $id,
                'AC_ACCION' => 'ELIMINAR',
                'AC_DATOS_ANTES' => json_encode($antes),
                'AC_DATOS_DESPUES' => ''
            ]);

            echo json_encode(['message' => 'Ingreso eliminado (lógico)']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Ingreso no encontrado']);
        }
        $this->db->closeConnection();
    }

    // Filtrar por célula
    public function getByCelula($celulaId) {
        $stmt = $this->ingreso->getByCelula($celulaId);
        $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($ingresos) {
            echo json_encode($ingresos);
        } else {
            echo json_encode(['message' => 'No hay ingresos para esta célula']);
        }
        $this->db->closeConnection();
    }
}
