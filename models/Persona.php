<?php
class Persona {
    private $conn;
    private $table = 'Personas';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar activas
    public function getAll() {
        $query = "SELECT * FROM {$this->table} WHERE PE_ACTIVO = 'A' ORDER BY PE_ID ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $query = "SELECT COUNT(*) AS total FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $num = intval($row['total']) + 1;
        return 'PE' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }


    // Crear persona
    public function create($id, $data) {
        $hash = password_hash($data['PR_AUTH_TEXT'], PASSWORD_DEFAULT);
        $query = "INSERT INTO {$this->table} (PE_ID, PE_NOMBRE, PE_EMAIL, PR_AUTH_TEXT, PE_ROL, PE_CEL_ID, PE_ACTIVO) 
                  VALUES (:id, :nombre, :email, :password, :rol, :celula, 'A')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $data['pe_nombre']);
        $stmt->bindParam(':email', $data['pe_email']);
        $stmt->bindParam(':password', $hash);
        $stmt->bindParam(':rol', $data['pe_rol']);
        $stmt->bindParam(':celula', $data['pe_cel_id']);
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
        $stmt->bindParam(':nombre', $data['pe_nombre']);
        $stmt->bindParam(':email', $data['pe_email']);
        $stmt->bindParam(':rol', $data['pe_rol']);
        $stmt->bindParam(':celula', $data['pe_cel_id']);
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
        
       if ($user) {
            if (password_verify($password, $user['pr_auth_text'])) {
                error_log("✅ Contraseña OK");
                unset($user['pr_auth_text']);
                return $user;
            } else {
                error_log("❌ password_verify falló");
                error_log("Input: [$password]");
                error_log("Hash: [{$user['pr_auth_text']}]");
                return false;
            }
        } else {
            error_log("❌ Usuario no encontrado");
            return false;
        }

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
