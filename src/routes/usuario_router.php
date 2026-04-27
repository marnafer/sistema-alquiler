<?php

require_once SRC_PATH . 'controllers/UsuarioController.php';

use App\Controllers\UsuarioController;

$controller = new UsuarioController();

switch (true) {

    // LOGIN
    case $path === '/api/usuarios/login' && $method === 'POST':
        $controller->login();
        break;

    // REGISTER
    case $path === '/api/usuarios' && $method === 'POST':
        $controller->guardar();
        break;

    // LISTAR (SOLO ADMIN)
    case $path === '/api/usuarios' && $method === 'GET':
        $user = AutenticadorMiddleware::soloAdmin();
        $controller->listarUsuariosApi();
        break;

    // GET ID
    case preg_match('#^/api/usuarios/(\d+)$#', $path, $m) && $method === 'GET':
        $controller->mostrar($m[1]);
        break;

    // UPDATE
    case preg_match('#^/api/usuarios/(\d+)$#', $path, $m) && $method === 'PUT':
        $user = AutenticadorMiddleware::verificar();
        $controller->actualizar($m[1]);
        break;

    // DELETE (SOLO ADMIN)
    case preg_match('#^/api/usuarios/(\d+)$#', $path, $m) && $method === 'DELETE':
        $user = AutenticadorMiddleware::soloAdmin();
        $controller->eliminar($m[1]);
        break;

    // RESTORE (SOLO ADMIN)
    case preg_match('#^/api/usuarios/restaurar/(\d+)$#', $path, $m) && $method === 'POST':
        $user = AutenticadorMiddleware::soloAdmin();
        $controller->restaurar($m[1]);
        break;

    default:
        http_response_code(404);
        renderJson(['error' => 'Ruta no encontrada'], 404);
}