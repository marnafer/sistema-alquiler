<?php
/**
 * Router de PropiedadServicio
 */

require_once SRC_PATH . 'controllers/PropiedadServicioController.php';

use App\Controllers\PropiedadServicioController;

$controller = new PropiedadServicioController();
$method = $_SERVER['REQUEST_METHOD'];

// 1. Estadísticas: /api/propiedades-servicios/estadisticas
if ($path === '/api/propiedades-servicios/estadisticas' && $method === 'GET') {
    $controller->getEstadisticas();
    exit;
}

// 2. Relaciones por propiedad: /api/propiedades-servicios/propiedad/{id}
if (preg_match('#^/api/propiedades-servicios/propiedad/([0-9]+)$#', $path, $matches)) {
    if ($method === 'GET') {
        $controller->getByPropiedad($matches[1]);
    } elseif ($method === 'DELETE') {
        $controller->deleteByPropiedad($matches[1]);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
    }
    exit;
}

// 3. Relaciones por servicio: /api/propiedades-servicios/servicio/{id}
if (preg_match('#^/api/propiedades-servicios/servicio/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    $controller->getByServicio($matches[1]);
    exit;
}

// 4. Sincronizar servicios de una propiedad: /api/propiedades-servicios/sync/{propiedadId}
if (preg_match('#^/api/propiedades-servicios/sync/([0-9]+)$#', $path, $matches) && $method === 'POST') {
    $controller->sync($matches[1]);
    exit;
}

// 5. CRUD general: /api/propiedades-servicios
if ($path === '/api/propiedades-servicios') {
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

// 6. CRUD con ID: /api/propiedades-servicios/{id}
if (preg_match('#^/api/propiedades-servicios/([0-9]+)$#', $path, $matches)) {
    $id = $matches[1];
    
    if ($method === 'GET') {
        $controller->show($id);
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