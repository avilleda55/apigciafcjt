<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo "✅ Conexión exitosa a Supabase PostgreSQL.";
} else {
    echo "❌ Error al conectar.";
}
