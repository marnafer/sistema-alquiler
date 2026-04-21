<?php
/**
 * Router de Reseñas
 */

require_once SRC_PATH . 'controllers/ResenaController.php';

use App\Controllers\ResenaController;

$controller = new ResenaController();
$method = $_SERVER['REQUEST_METHOD'];

// 1. Estadísticas: /api/resenas/estadisticas
if ($path === '/api/resenas/estadisticas' && $method === 'GET') {
    $controller->getEstadisticas();
    exit;
}

// 2. Reseñas por propiedad: /api/resenas/propiedad/{id}
if (preg_match('#^/api/resenas/propiedad/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    $controller->getByPropiedad($matches[1]);
    exit;
}

// 3. Reseñas por usuario: /api/resenas/usuario/{id}
if (preg_match('#^/api/resenas/usuario/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    $controller->getByUsuario($matches[1]);
    exit;
}

// 4. CRUD general: /api/resenas
if ($path === '/api/resenas') {
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

// 5. CRUD con ID: /api/resenas/{id}
if (preg_match('#^/api/resenas/([0-9]+)$#', $path, $matches)) {
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