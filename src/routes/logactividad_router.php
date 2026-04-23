<?php
/**
 * Router de LogActividad
 */

require_once SRC_PATH . 'controllers/LogActividadController.php';

use App\Controllers\LogActividadController;

$controller = new LogActividadController();
$method = $_SERVER['REQUEST_METHOD'];

// 1. Estadísticas: /api/logs-actividad/estadisticas
if ($path === '/api/logs-actividad/estadisticas' && $method === 'GET') {
    $controller->getEstadisticas();
    exit;
}

// 2. Búsqueda: /api/logs-actividad/buscar?q=texto
if ($path === '/api/logs-actividad/buscar' && $method === 'GET') {
    $controller->search();
    exit;
}

// 3. Por fechas: /api/logs-actividad/fecha?desde=X&hasta=Y
if ($path === '/api/logs-actividad/fecha' && $method === 'GET') {
    $controller->getByFecha();
    exit;
}

// 4. Limpiar logs antiguos: /api/logs-actividad/limpiar/antiguos?dias=30
if ($path === '/api/logs-actividad/limpiar/antiguos' && $method === 'DELETE') {
    $controller->limpiarAntiguos();
    exit;
}

// 5. Logs por usuario: /api/logs-actividad/usuario/{id}
if (preg_match('#^/api/logs-actividad/usuario/([0-9]+)$#', $path, $matches)) {
    if ($method === 'GET') {
        $controller->getByUsuario($matches[1]);
    } elseif ($method === 'DELETE') {
        $controller->limpiarPorUsuario($matches[1]);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
    }
    exit;
}

// 6. Registrar log manual: /api/logs-actividad/registrar
if ($path === '/api/logs-actividad/registrar' && $method === 'POST') {
    $controller->registrar();
    exit;
}

// 7. CRUD general: /api/logs-actividad
if ($path === '/api/logs-actividad') {
    if ($method === 'GET') {
        $controller->index();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
    }
    exit;
}

// 8. CRUD con ID: /api/logs-actividad/{id}
if (preg_match('#^/api/logs-actividad/([0-9]+)$#', $path, $matches)) {
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