<?php
/**
 * Router de Reservas (versión simplificada)
 */

require_once SRC_PATH . 'controllers/ReservaController.php';

use App\Controllers\ReservaController;

$controller = new ReservaController();
$method = $_SERVER['REQUEST_METHOD'];

// Verificar disponibilidad: /api/reservas/disponibilidad
if ($path === '/api/reservas/disponibilidad' && $method === 'GET') {
    $controller->checkAvailability();
    exit;
}

// Cambiar estado: /api/reservas/{id}/estado
if (preg_match('#^/api/reservas/([0-9]+)/estado$#', $path, $matches) && $method === 'PATCH') {
    $controller->changeStatus($matches[1]);
    exit;
}

// CRUD normal: /api/reservas
if ($path === '/api/reservas') {
    if ($method === 'GET') {
        $controller->index();
    } elseif ($method === 'POST') {
        $controller->store();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
    }
    exit;
}

// CRUD con ID: /api/reservas/{id}
if (preg_match('#^/api/reservas/([0-9]+)$#', $path, $matches)) {
    $id = $matches[1];
    
    if ($method === 'GET') {
        $controller->show($id);
    } elseif ($method === 'PUT') {
        $controller->update($id);
    } elseif ($method === 'DELETE') {
        $controller->delete($id);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
    }
    exit;
}