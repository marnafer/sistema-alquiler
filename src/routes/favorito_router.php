<?php
/**
 * Router RESTful del módulo de Favoritos
 */

require_once SRC_PATH . 'controllers/FavoritoController.php';

use App\Controllers\FavoritoController;

$controller = new FavoritoController();
$method = $_SERVER['REQUEST_METHOD'];

global $path;

// 1. --- GET favoritos por usuario ---
// /api/usuarios/{id}/favoritos
if (preg_match('#^/api/usuarios/([0-9]+)/favoritos$#', $path, $matches)) {
    $usuarioId = $matches[1];

    if ($method === 'GET') {
        $controller->listarFavoritos($usuarioId);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método $method no permitido"]);
    }
    exit;
}

// 2. --- RUTA GENERAL FAVORITOS ---
// /api/favoritos
if ($path === '/api/favoritos') {

    if ($method === 'GET') {
        // Listar todos los favoritos (opcional)
        $controller->listarTodos();
    
    } elseif ($method === 'POST') {
        $controller->agregarFavorito();

    } elseif ($method === 'DELETE') {
        $controller->eliminarFavorito();

    } else {
        http_response_code(405);
        echo json_encode(["error" => "Método $method no permitido. Use GET, POST o DELETE"]);
    }
    exit;
}

// 3. --- ELIMINAR favorito específico por ID ---
// /api/favoritos/{id}
if (preg_match('#^/api/favoritos/([0-9]+)$#', $path, $matches) && $method === 'DELETE') {
    $favoritoId = $matches[1];
    $controller->eliminarFavoritoPorId($favoritoId);
    exit;
}

// Si no coincide ninguna ruta
http_response_code(404);
echo json_encode(["error" => "Ruta no encontrada", "path" => $path, "method" => $method]);