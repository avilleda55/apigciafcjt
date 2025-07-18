<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Persona.php';

class PersonaController {
    private $db;
    private $conn;
    private $persona;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->persona = new Persona($this->conn);
        header("Content-Type: application/json");
    }

    // Listar todas
    public function getAll() {
        $stmt = $this->persona->getAll();
        $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($personas);
        $this->db->closeConnection();
    }

    // Obtener por ID
    public function getById($id) {
        $stmt = $this->persona->getById($id);
        $persona = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($persona) {
            unset($persona['PR_AUTH_TEXT']); // No mandamos hash al frontend
            echo json_encode($persona);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Persona no encontrada']);
        }
        $this->db->closeConnection();
    }

    // Crear
    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        $currentUserId = $data['current_user_id'] ?? null;

        if (empty($data['pe_nombre']) || empty($data['pe_email']) || empty($data['pr_auth_text']) || empty($currentUserId)) {
            http_response_code(400);
            echo json_encode(['message' => 'Datos incompletos']);
            return;
        }

        $newId = $this->persona->generateNewId();
        $this->persona->create($newId, $data);

        // Registrar en Actividad
        require_once __DIR__ . '/../models/Actividad.php';
        $actividad = new Actividad($this->conn);
        $actividad->logAction([
            'AC_ID' => $actividad->generateNewId(),
            'AC_PER_ID' => $currentUserId,
            'AC_ENTIDAD' => 'Personas',
            'AC_ENTIDAD_ID' => $newId,
            'AC_ACCION' => 'CREAR',
            'AC_DATOS_ANTES' => '',
            'AC_DATOS_DESPUES' => json_encode($data)
        ]);

        echo json_encode(['message' => 'Persona creada', 'PE_ID' => $newId]);
        $this->db->closeConnection();
    }


    // Actualizar (sin tocar password)
    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $currentUserId = $data['current_user_id'] ?? null;

        $antes = $this->persona->getById($id)->fetch(PDO::FETCH_ASSOC);
        $updated = $this->persona->update($id, $data);

        if ($updated) {
            require_once __DIR__ . '/../models/Actividad.php';
            $actividad = new Actividad($this->conn);
            $actividad->logAction([
                'AC_ID' => $actividad->generateNewId(),
                'AC_PER_ID' => $currentUserId,
                'AC_ENTIDAD' => 'Personas',
                'AC_ENTIDAD_ID' => $id,
                'AC_ACCION' => 'ACTUALIZAR',
                'AC_DATOS_ANTES' => json_encode($antes),
                'AC_DATOS_DESPUES' => json_encode($data)
            ]);

            echo json_encode(['message' => 'Persona actualizada']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Persona no encontrada']);
        }
        $this->db->closeConnection();
    }

    // Borrado lógico
    public function delete($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $currentUserId = $data['current_user_id'] ?? null;

        $antes = $this->persona->getById($id)->fetch(PDO::FETCH_ASSOC);
        $deleted = $this->persona->delete($id);

        if ($deleted) {
            require_once __DIR__ . '/../models/Actividad.php';
            $actividad = new Actividad($this->conn);
            $actividad->logAction([
                'AC_ID' => $actividad->generateNewId(),
                'AC_PER_ID' => $currentUserId,
                'AC_ENTIDAD' => 'Personas',
                'AC_ENTIDAD_ID' => $id,
                'AC_ACCION' => 'ELIMINAR',
                'AC_DATOS_ANTES' => json_encode($antes),
                'AC_DATOS_DESPUES' => ''
            ]);

            echo json_encode(['message' => 'Persona eliminada (lógica)']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Persona no encontrada']);
        }
        $this->db->closeConnection();
    }

    // Login
    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['PE_EMAIL']) || empty($data['PR_AUTH_TEXT'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Email y contraseña requeridos']);
            return;
        }
        $user = $this->persona->login($data['PE_EMAIL'], $data['PR_AUTH_TEXT']);
        if ($user) {
            echo json_encode(['message' => 'Login exitoso', 'user' => $user]);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Credenciales incorrectas']);
        }
        $this->db->closeConnection();
    }

    // Cambiar contraseña
    public function changePassword($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['new_password'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Nueva contraseña requerida']);
            return;
        }
        $changed = $this->persona->changePassword($id, $data['new_password']);
        if ($changed) {
            echo json_encode(['message' => 'Contraseña actualizada']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Persona no encontrada']);
        }
        $this->db->closeConnection();
    }
}
