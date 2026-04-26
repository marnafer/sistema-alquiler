<?php
/**
 * Router RESTful del módulo de Favoritos
 */

require_once SRC_PATH . 'controllers/FavoritoController.php';

use App\Controllers\FavoritoController;

$controller = new FavoritoController();
$method = $_SERVER['REQUEST_METHOD'];

// 1. --- GET favoritos por usuario ---
// /api/usuarios/{id}/favoritos
if (preg_match('#^/api/usuarios/([0-9]+)/favoritos$#', $path, $matches)) {
    $usuarioId = $matches[1];

    if ($method === 'GET') {
        $controller->listarFavoritos($usuarioId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Metodo $method no permitido"]);
    }
    exit;
}

// 2. --- RUTA GENERAL FAVORITOS ---
// /api/favoritos
if ($path === '/api/favoritos') {

    if ($method === 'POST') {
        $controller->agregarFavorito();

    } elseif ($method === 'DELETE') {
        $controller->eliminarFavorito();

    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método $method no permitido"]);
    }
    exit;
}