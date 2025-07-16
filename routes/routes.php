<?php
// Desactivar advertencias innecesarias
error_reporting(E_ERROR | E_PARSE);

// Obtener la URL y el método
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Limpiar query params (todo antes del ?)
$path = parse_url($requestUri, PHP_URL_PATH);

// Quitar el prefijo '/api/' si lo tienes montado así (ajusta según tu carpeta)
$path = str_replace('/api/', '', $path);

// Dividir la ruta en partes
$pathParts = explode('/', trim($path, '/'));

// Ruta base (ej. 'celulas')
$resource = isset($pathParts[0]) ? $pathParts[0] : null;
// ID opcional (ej. 'CE00001')
$id = isset($pathParts[1]) ? $pathParts[1] : null;

// --- Routing simple ---
switch ($resource) {
    case 'celulas':
        require_once '../controllers/CelulaController.php';
        $controller = new CelulaController();

        switch ($requestMethod) {
            case 'GET':
                if ($id) {
                    $controller->getById($id);
                } else {
                    $controller->getAll();
                }
                break;
            case 'POST':
                $controller->create();
                break;
            case 'PUT':
                if ($id) {
                    $controller->update($id);
                } else {
                    echo json_encode(['error' => 'ID requerido para actualizar']);
                }
                break;
            case 'DELETE':
                if ($id) {
                    $controller->delete($id);
                } else {
                    echo json_encode(['error' => 'ID requerido para eliminar']);
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
        }
        break;

    case 'personas':
    require_once '../controllers/PersonaController.php';
    $controller = new PersonaController();

    switch ($requestMethod) {
        case 'GET':
            if ($id) {
                $controller->getById($id);
            } else {
                $controller->getAll();
            }
            break;

        case 'POST':
            if ($id === 'login') {
                $controller->login();
            } else {
                $controller->create();
            }
            break;

        case 'PUT':
            // Si la ruta es /personas/{id}/password → cambio de password
            if (isset($pathParts[2]) && $pathParts[2] === 'password') {
                $controller->changePassword($id);
            } elseif ($id) {
                $controller->update($id);
            } else {
                echo json_encode(['error' => 'ID requerido para actualizar']);
            }
            break;

        case 'DELETE':
            if ($id) {
                $controller->delete($id);
            } else {
                echo json_encode(['error' => 'ID requerido para eliminar']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
    break;
    case 'ingresos':
    require_once '../controllers/IngresoController.php';
    $controller = new IngresoController();

    switch ($requestMethod) {
        case 'GET':
            if (isset($pathParts[1]) && $pathParts[1] === 'celula' && isset($pathParts[2])) {
                // GET /api/ingresos/celula/CE00001 → filtrar por célula
                $controller->getByCelula($pathParts[2]);
            } elseif ($id) {
                // GET /api/ingresos/IN00001 → obtener por ID
                $controller->getById($id);
            } else {
                // GET /api/ingresos → listar todos
                $controller->getAll();
            }
            break;

        case 'POST':
            $controller->create();
            break;

        case 'PUT':
            if ($id) {
                $controller->update($id);
            } else {
                echo json_encode(['error' => 'ID requerido para actualizar']);
            }
            break;

        case 'DELETE':
            if ($id) {
                $controller->delete($id);
            } else {
                echo json_encode(['error' => 'ID requerido para eliminar']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
    break;
    case 'egresos':
    require_once '../controllers/EgresoController.php';
    $controller = new EgresoController();

    switch ($requestMethod) {
        case 'GET':
            if (isset($pathParts[1]) && $pathParts[1] === 'celula' && isset($pathParts[2])) {
                // GET /api/egresos/celula/CE00001 → filtrar por célula
                $controller->getByCelula($pathParts[2]);
            } elseif ($id) {
                // GET /api/egresos/EG00001 → obtener por ID
                $controller->getById($id);
            } else {
                // GET /api/egresos → listar todos
                $controller->getAll();
            }
            break;

        case 'POST':
            $controller->create();
            break;

        case 'PUT':
            if ($id) {
                $controller->update($id);
            } else {
                echo json_encode(['error' => 'ID requerido para actualizar']);
            }
            break;

        case 'DELETE':
            if ($id) {
                $controller->delete($id);
            } else {
                echo json_encode(['error' => 'ID requerido para eliminar']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
    break;
    case 'actividades':
    require_once '../controllers/ActividadController.php';
    $controller = new ActividadController();

    switch ($requestMethod) {
        case 'GET':
            $controller->getAll();
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
    break;
    case 'dashboards':
    require_once '../controllers/DashboardController.php';
    $controller = new DashboardController();

    switch ($requestMethod) {
        case 'POST':
            $controller->getDashboard();
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
    break;




    default:
        http_response_code(404);
        echo json_encode(['error' => 'Recurso no encontrado']);
}
