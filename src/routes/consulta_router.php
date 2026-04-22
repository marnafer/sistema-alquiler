<?php
/**
 * Router RESTful del módulo de Consultas
 * TODAS las respuestas son JSON
 */

require_once SRC_PATH . 'controllers/ConsultaController.php';

use App\Controllers\ConsultaController;

$controller = new ConsultaController();
$method = $_SERVER['REQUEST_METHOD'];

// 1. Ruta para consultas por propiedad: /api/consultas/propiedad/{id}
if (preg_match('#^/api/consultas/propiedad/([0-9]+)$#', $path, $matches)) {
    $propiedadId = $matches[1];

    if ($method === 'GET') {
        $controller->listarPorPropiedad($propiedadId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método $method no permitido. Solo GET"]);
    }
    exit;
}

// 2. Ruta para consultas por inquilino: /api/consultas/inquilino/{id}
if (preg_match('#^/api/consultas/inquilino/([0-9]+)$#', $path, $matches)) {
    $inquilinoId = $matches[1];

    if ($method === 'GET') {
        $controller->listarPorInquilino($inquilinoId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método $method no permitido. Solo GET"]);
    }
    exit;
}

// 3. Ruta con ID: /api/consultas/{id}
if (preg_match('#^/api/consultas/([0-9]+)$#', $path, $matches)) {
    $consultaId = $matches[1];

    if ($method === 'GET') {
        $controller->obtener($consultaId);
    } elseif ($method === 'PUT') {
        $controller->actualizar($consultaId);
    } elseif ($method === 'DELETE') {
        $controller->eliminar($consultaId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método $method no permitido"]);
    }
    exit;
}

// 4. Ruta general: /api/consultas
if ($path === '/api/consultas') {
    if ($method === 'GET') {
        $controller->listar();
    } elseif ($method === 'POST') {
        $controller->crear();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método $method no permitido"]);
    }
    exit;
}