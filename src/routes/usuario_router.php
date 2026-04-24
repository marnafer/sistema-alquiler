<?php
/**
 * Router RESTful del módulo de Usuarios (nombres en español)
 */

require_once SRC_PATH . 'controllers/UsuarioController.php';

use App\Controllers\UsuarioController;

$controller = new UsuarioController();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. --- RUTA ESPECIAL: Login (debe ir antes de /api/usuarios/{id} y /api/usuarios) ---
if ($path === '/api/usuarios/login') {
    if ($method === 'POST') {
        $controller->login();
    } else {
        http_response_code(405);
        echo json_encode(['error' => "Método $method no permitido en esta ruta"]);
    }
    exit;
}

// 2. --- RUTA ESPECIAL: Obtener por rol ---
if (preg_match('#^/api/usuarios/rol/([0-9]+)$#', $path, $matches)) {
    $rolId = (int)$matches[1];
    if ($method === 'GET') {
        $controller->obtenerPorRol($rolId);
    } else {
        http_response_code(405);
        echo json_encode(['error' => "Método $method no permitido en esta ruta"]);
    }
    exit;
}

// 3. --- RUTA: Restaurar usuario (/api/usuarios/restaurar/{id}) ---
if (preg_match('#^/api/usuarios/restaurar/([0-9]+)$#', $path, $matches)) {
    $usuarioId = $matches[1];
    if ($method === 'POST') {
        $controller->restaurar($usuarioId);
    } else {
        http_response_code(405);
        echo json_encode(['error' => "Método $method no permitido en esta ruta"]);
    }
    exit;
}

// 4. --- API: Usuarios con ID (/api/usuarios/{id}) ---
if (preg_match('#^/api/usuarios/([0-9]+)$#', $path, $matches)) {
    $usuarioId = $matches[1];

    switch ($method) {
        case 'GET':    $controller->mostrar($usuarioId); break;
        case 'PUT':    $controller->actualizar($usuarioId); break;
        case 'DELETE': $controller->eliminar($usuarioId); break;
        default:
            http_response_code(405);
            echo json_encode(['error' => "Método $method no permitido"]);
            break;
    }
    exit;
}

// 5. --- API: Usuarios General (/api/usuarios) ---
if (trim($path) === '/api/usuarios') {
    if ($method === 'GET') {
        $controller->listarUsuariosApi();
    } elseif ($method === 'POST') {
        $controller->guardar();
    } else {
        http_response_code(405);
        echo json_encode(['error' => "Método $method no permitido"]);
    }
    exit;
}

// 6. --- VISTAS (HTML) ---
if ($path === '/usuarios') {
    if ($method === 'GET') $controller->listarUsuarios();
    exit;
}

if ($path === '/usuarios/nuevo') {
    if ($method === 'GET') $controller->mostrarFormulario();
    exit;
}

// Si no coincidió ninguna ruta, dejar que el router global gestione el 404