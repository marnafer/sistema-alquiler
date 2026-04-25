<?php
// 1. Importamos el controlador
require_once SRC_PATH . 'controllers/PropiedadController.php';

use App\Controllers\PropiedadController;

$controller = new PropiedadController(); // Instanciamos una sola vez

// --- Ruta: /propiedades/nuevo ---
if ($path === '/api/propiedades/nuevo') {
    if ($method === 'GET') {
        $controller->mostrarFormulario(); // Llamada al metodo de la clase
    } else {
        http_response_code(405);
    }
    exit;
}

// --- Ruta: /propiedades ---
if (trim($path) === '/api/propiedades') {
    switch ($method) {
        case 'GET':
            $controller->listarPropiedades();
            break;
            
        case 'POST':
            $controller->crearPropiedad();
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "M�todo $method no permitido"]);
            break;
    }
    exit; 
}