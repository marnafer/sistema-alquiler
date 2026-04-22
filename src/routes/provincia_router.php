<?php
/**
 * Router de Provincias
 */

require_once SRC_PATH . 'controllers/ProvinciaController.php';

use App\Controllers\ProvinciaController;

$controller = new ProvinciaController();
$method = $_SERVER['REQUEST_METHOD'];

// 1. Provincias con conteo: /api/provincias/con-localidades
if ($path === '/api/provincias/con-localidades' && $method === 'GET') {
    $controller->indexWithCount();
    exit;
}

// 2. CRUD general: /api/provincias
if ($path === '/api/provincias') {
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

// 3. CRUD con ID: /api/provincias/{id}
if (preg_match('#^/api/provincias/([0-9]+)$#', $path, $matches)) {
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