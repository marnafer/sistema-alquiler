<?php
/**
 * Router de Servicios
 */

require_once SRC_PATH . 'controllers/ServicioController.php';

use App\Controllers\ServicioController;

$controller = new ServicioController();
$method = $_SERVER['REQUEST_METHOD'];

// 1. Servicios con conteo de propiedades: /api/servicios/con-propiedades
if ($path === '/api/servicios/con-propiedades' && $method === 'GET') {
    $controller->indexWithCount();
    exit;
}

// 2. Servicios populares: /api/servicios/populares?limit=10
if ($path === '/api/servicios/populares' && $method === 'GET') {
    $controller->getPopulares();
    exit;
}

// 3. Servicios por propiedad: /api/servicios/propiedad/{id}
if (preg_match('#^/api/servicios/propiedad/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    $controller->getByPropiedad($matches[1]);
    exit;
}

// 4. CRUD general: /api/servicios
if ($path === '/api/servicios') {
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

// 5. CRUD con ID: /api/servicios/{id}
if (preg_match('#^/api/servicios/([0-9]+)$#', $path, $matches)) {
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