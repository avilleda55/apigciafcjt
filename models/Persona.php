<?php
class Persona {
    private $conn;
    private $table = 'Personas';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar activas
    public function getAll() {
        $query = "SELECT * FROM {$this->table} WHERE PE_ACTIVO = 'A'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener por ID
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE PE_ID = :id AND PE_ACTIVO = 'A'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt;
    }

    // Generar nuevo ID
    public function generateNewId() {
        $query = "SELECT PE_ID FROM {$this->table} WHERE PE_ID ~ '^PE[0-9]+$' ORDER BY CAST(SUBSTRING(PE_ID, 3) AS INTEGER) DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $num = intval(substr($row['PE_ID'], 2)) + 1;
        } else {
            $num = 1;
        }
        return 'PE' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    // Crear persona
    public function create($id, $data) {
        $hash = password_hash($data['PR_AUTH_TEXT'], PASSWORD_DEFAULT);
        $query = "INSERT INTO {$this->table} (PE_ID, PE_NOMBRE, PE_EMAIL, PR_AUTH_TEXT, PE_ROL, PE_CEL_ID, PE_ACTIVO) 
                  VALUES (:id, :nombre, :email, :password, :rol, :celula, 'A')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $data['PE_NOMBRE']);
        $stmt->bindParam(':email', $data['PE_EMAIL']);
        $stmt->bindParam(':password', $hash);
        $stmt->bindParam(':rol', $data['PE_ROL']);
        $stmt->bindParam(':celula', $data['PE_CEL_ID']);
        return $stmt->execute();
    }

    // Actualizar persona (sin tocar password si no llega)
    public function update($id, $data) {
        $query = "UPDATE {$this->table} SET 
                     PE_NOMBRE = :nombre,
                     PE_EMAIL = :email,
                     PE_ROL = :rol,
                     PE_CEL_ID = :celula
                  WHERE PE_ID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $data['PE_NOMBRE']);
        $stmt->bindParam(':email', $data['PE_EMAIL']);
        $stmt->bindParam(':rol', $data['PE_ROL']);
        $stmt->bindParam(':celula', $data['PE_CEL_ID']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Borrado lógico
    public function delete($id) {
        $query = "UPDATE {$this->table} SET PE_ACTIVO = 'I' WHERE PE_ID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Login (verificar credenciales)
    public function login($email, $password) {
        $query = "SELECT * FROM {$this->table} WHERE PE_EMAIL = :email AND PE_ACTIVO = 'A'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            error_log("⚠️ Usuario no encontrado o inactivo");
            return false;
        }

        if (!password_verify($password, $user['PR_AUTH_TEXT'])) {
            error_log("⚠️ Contraseña incorrecta");
            return false;
        }

        unset($user['PR_AUTH_TEXT']); 
        return $user;
    }

    // Cambiar contraseña
    public function changePassword($id, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE {$this->table} SET PR_AUTH_TEXT = :password WHERE PE_ID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hash);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
