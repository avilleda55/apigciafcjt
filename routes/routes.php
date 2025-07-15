<?php
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch (true) {
    case $request === '/api/celulas' && $method === 'GET':
        require '../controllers/CelulaController.php';
        (new CelulaController())->getAll();
        break;
    // Aquí agregarías más rutas...
    default:
        echo json_encode(['error' => 'Ruta no encontrada']);
        break;
}