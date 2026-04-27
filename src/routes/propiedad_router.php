<?php

require_once SRC_PATH . 'controllers/PropiedadController.php';

use App\Controllers\PropiedadController;
use App\Middlewares\AutenticadorMiddleware;

$controller = new PropiedadController();

switch (true) {

    case $path === '/api/propiedades' && $method === 'GET':
        $controller->indexApi();
        break;

    case $path === '/api/propiedades' && $method === 'POST':
        $controller->crear();
        break;

    case preg_match('#^/api/propiedades/(\d+)$#', $path, $matches) && $method === 'GET':
        $controller->mostrarApi($matches[1]);
        break;

    case preg_match('#^/api/propiedades/(\d+)$#', $path, $matches) && $method === 'PUT':
        $controller->actualizar($matches[1]);
        break;

    case preg_match('#^/api/propiedades/(\d+)$#', $path, $matches) && $method === 'DELETE':
        $controller->eliminar($matches[1]);
        break;

    case $path === '/api/propiedades/nuevo' && $method === 'GET':
        $controller->mostrarFormulario();
        break;

    default:
        http_response_code(404);
        renderJson(['error' => 'Ruta no encontrada'], 404);
}