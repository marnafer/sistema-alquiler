<?php
/**
 * Router RESTful del módulo de Favoritos
 */

require_once SRC_PATH . 'controllers/FavoritosController.php';

use App\Controllers\FavoritosController;

$controller = new FavoritosController();
$method = $_SERVER['REQUEST_METHOD'];

// 1. --- RUTA DE API CON ID: /api/favoritos/{id} ---
// Debe ir primero porque es más específica
if (preg_match('#^/api/favoritos/([0-9]+)$#', $path, $matches)) {
    $favoritoId = $matches[1];

    if ($method === 'DELETE') {
        $controller->quitar($favoritoId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Para un ID específico solo se permite el método DELETE"]);
    }
    exit;
}

// 2. --- RUTA DE API GENERAL: /api/favoritos ---
if ($path === '/api/favoritos') {
    if ($method === 'POST') {
        $controller->agregar();
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método $method no permitido en esta ruta"]);
    }
    exit;
}

// 3. --- RUTA DE VISTA (HTML): /favoritos ---
if ($path === '/favoritos') {
    if ($method === 'GET') {
        $controller->listar_Favoritos();
    } else {
        http_response_code(405);
    }
    exit;
}