<?php
/**
 * Router RESTful del módulo de Categorías
 */

require_once SRC_PATH . 'controllers/CategoriaController.php';

use App\Controllers\CategoriaController;

$controller = new CategoriaController();
$method = $_SERVER['REQUEST_METHOD'];

// 1. --- RUTA DE API CON ID: /api/categorias/{id} ---
if (preg_match('#^/api/categorias/([0-9]+)$#', $path, $matches)) {
    $categoriaId = $matches[1];

    if ($method === 'GET') {
        $controller->obtener($categoriaId);
    } elseif ($method === 'PUT') {
        $controller->actualizar($categoriaId);
    } elseif ($method === 'DELETE') {
        $controller->eliminar($categoriaId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método $method no permitido. Métodos permitidos: GET, PUT, DELETE"]);
    }
    exit;
}

// 2. --- RUTA DE API GENERAL: /api/categorias ---
if ($path === '/api/categorias') {
    if ($method === 'GET') {
        $controller->listar();
    } elseif ($method === 'POST') {
        $controller->crear();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método $method no permitido. Métodos permitidos: GET, POST"]);
    }
    exit;
}

// 3. --- RUTA DE VISTA (HTML): /categorias ---
if ($path === '/categorias') {
    if ($method === 'GET') {
        $controller->listarVista();
    } else {
        http_response_code(405);
    }
    exit;
}