<?php
// src/routes/router.php

require_once SRC_PATH . 'controllers/PropiedadController.php';

if ($path === '/propiedades/nuevo') {
    if ($method === 'GET') {
        mostrarFormulario();
    }
    exit;
}

if (trim($path) === '/propiedades') {
    
    switch ($method) {
        case 'GET':
            listarPropiedades(); // Delegamos al controlador
            break;
            
        case 'POST':
            crearPropiedad(); // Delegamos al controlador
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Método $method no permitido"]);
            break;
    }
    exit; 
}