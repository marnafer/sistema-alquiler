<?php
// Router RESTful para Localidades

require_once SRC_PATH . 'controllers/LocalidadController.php';

use App\Controllers\LocalidadController;

$controller = new LocalidadController();
$method = $_SERVER['REQUEST_METHOD'];

// 1. API: /api/localidades/{id}
if (preg_match('#^/api/localidades/(.+)$#', $path, $matches)) { // (Deja pasar cualquier cosa y que el controlador decida)
    $id = $matches[1];                                          // Importante si en un futuro los id dejan de ser solo numericos

    switch ($method) {
        case 'GET':    $controller->mostrarApi($id); break;
        case 'PUT':    $controller->actualizar($id); break;
        case 'DELETE': $controller->eliminar($id); break;
        default:
            http_response_code(405);
            echo json_encode(['error' => "Método no permitido"]);
            break;
    }
    exit;
}

// 2. API: /api/localidades
if (trim($path) === '/api/localidades') {
    switch ($method) {
        case 'GET':  $controller->indexApi(); break;
        case 'POST': $controller->crear(); break;
        default:
            http_response_code(405);
            echo json_encode(['error' => "Método no permitido"]);
            break;
    }
    exit;
}

// 3. VISTAS HTML
if ($path === '/localidades') {
    if ($method === 'GET') $controller->listarLocalidades();
    exit;
}