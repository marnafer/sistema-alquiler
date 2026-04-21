<?php
/**
 * Router RESTful del módulo de Usuarios
 */

require_once SRC_PATH . 'controllers/UsuarioController.php';

use App\Controllers\UsuarioController;

$controller = new UsuarioController();
$method = $_SERVER['REQUEST_METHOD'];

// 1. --- API: Usuarios con ID (/api/usuarios/{id}) ---
if (preg_match('#^/api/usuarios/([0-9]+)$#', $path, $matches)) {
    $usuarioId = $matches[1];

    switch ($method) {
        case 'GET':    $controller->mostrar($usuarioId); break; // Ver un usuario
        case 'PUT':    $controller->actualizar($usuarioId); break; // Editar (REST puro)
        case 'DELETE': $controller->eliminar($usuarioId); break; // Borrar
        default:
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
            break;
    }
    exit;
}

// 2. --- API: Usuarios General (/api/usuarios) ---
if ($path === '/api/usuarios') {
    if ($method === 'POST') {
        $controller->guardar(); // Crear nuevo
    } elseif ($method === 'GET') {
        $controller->indexApi(); // Listar usuarios en JSON (opcional)
    } else {
        http_response_code(405);
    }
    exit;
}

// 3. --- VISTAS (HTML) ---
if ($path === '/usuarios') {
    if ($method === 'GET') $controller->listarUsuarios(); // Página con la tabla
    exit;
}

if ($path === '/usuarios/nuevo') {
    if ($method === 'GET') $controller->mostrarFormulario(); // Página del form
    exit;
}