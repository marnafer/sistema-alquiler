<?php
/**
 * Router de Roles
 */

require_once SRC_PATH . 'controllers/RolController.php';

use App\Controllers\RolController;

$controller = new RolController();
$method = $_SERVER['REQUEST_METHOD'];

// 1. Roles con conteo de usuarios: /api/roles/con-usuarios
if ($path === '/api/roles/con-usuarios' && $method === 'GET') {
    $controller->indexWithCount();
    exit;
}

// 2. Rol por defecto: /api/roles/default
if ($path === '/api/roles/default' && $method === 'GET') {
    $controller->getDefault();
    exit;
}

// 3. CRUD general: /api/roles
if ($path === '/api/roles') {
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

// 4. CRUD con ID: /api/roles/{id}
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
        echo json_encode(["error" => "Método no permitido"]);
    }
    exit;
}

// Si no coincide ninguna ruta
http_response_code(404);
echo json_encode(["error" => "Ruta no encontrada"]);