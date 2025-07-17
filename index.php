<?php
//echo "Hola, el backend responde.";
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
// Configurar headers generales
header("Access-Control-Allow-Origin: *"); // ⚠️ OJO: abre para todos, puedes restringir por dominio
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Manejar preflight OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Cargar router
require_once __DIR__ . '/routes/routes.php';
