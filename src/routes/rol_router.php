<?php

require_once SRC_PATH . 'controllers/RolController.php';

use App\Controllers\RolController;

$controller = new RolController();
$method = $_SERVER['REQUEST_METHOD'];

global $path;

// 1. Roles con conteo de usuarios
if ($path === '/api/roles/con-usuarios' && $method === 'GET') {
    $controller->indexWithCount();
    exit;
}

// 2. Rol por defecto
if ($path === '/api/roles/default' && $method === 'GET') {
    $controller->getDefault();
    exit;
}

// 3. CRUD general
if ($path === '/api/roles') {
    if ($method === 'GET') {
        $controller->index();
    } elseif ($method === 'POST') {
        $controller->store();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido. Use GET o POST"]);
    }
    exit;
}

// 4. CRUD con ID
if (preg_match('#^/api/roles/([0-9]+)$#', $path, $matches)) {
    $id = $matches[1];
    
    if ($method === 'GET') {
        $controller->show($id);
    } elseif ($method === 'PUT') {
        $controller->update($id);
    } elseif ($method === 'DELETE') {
        $controller->delete($id);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido. Use GET, PUT o DELETE"]);
    }
    exit;
}

http_response_code(404);
echo json_encode(["error" => "Ruta no encontrada"]);