<?php
/**
 * Router del Módulo de Favoritos
 * Gestiona las acciones de agregar y quitar de la lista de deseos
 */

require_once SRC_PATH . 'controllers/FavoritosController.php';

use App\Controllers\FavoritosController;

$controller = new FavoritosController();

// Obtenemos el método de la petición (POST en este caso para acciones)
$method = $_SERVER['REQUEST_METHOD'];

// --- Acción: Agregar a Favoritos ---
if ($path === '/favoritos/agregar') {
    if ($method === 'POST') {
        $controller->agregar();
    } else {
        http_response_code(405);
        echo "Método no permitido para esta acción.";
    }
    exit;
}

// --- Acción: Quitar de Favoritos ---
if ($path === '/favoritos/quitar') {
    if ($method === 'POST') {
        $controller->quitar();
    } else {
        http_response_code(405);
        echo "Método no permitido para esta acción.";
    }
    exit;
}

// --- Vista: Listado de Favoritos (Opcional por ahora) ---
if ($path === '/favoritos/') {
    if ($method === 'GET') {
        $controller->listar_Favoritos();
    }
    exit;
}