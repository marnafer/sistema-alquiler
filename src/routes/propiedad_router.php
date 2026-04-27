<?php

require_once SRC_PATH . 'controllers/PropiedadController.php';

use App\Controllers\PropiedadController;

$controller = new PropiedadController();

/**
 * =========================================
 * VISTA: FORMULARIO (HTML)
 * =========================================
 * GET /api/propiedades/nuevo
 */
    if ($path === '/api/propiedades/nuevo') {

        if ($method === 'GET') {
            $controller->mostrarFormulario();
        } else {
            renderJson([
                'status' => 'error',
                'message' => "Método $method no permitido"
            ], 405);
        }

        exit;
    }

/**
 * =========================================
 * API: PROPIEDADES
 * =========================================
 * GET    /api/propiedades     -> listar
 * POST   /api/propiedades     -> crear
 */
    if (trim($path) === '/api/propiedades') {

        switch ($method) {

            case 'GET':
                $controller->indexApi();
                break;

            case 'POST':
                $controller->crear();
                break;

            default:
                renderJson([
                    'status' => 'error',
                    'message' => "Método $method no permitido"
                ], 405);
                break;
        }

        exit;
    }

/**
 * =========================================
 * RUTA NO ENCONTRADA
 * =========================================
 */
    renderJson([
        'status' => 'error',
        'message' => 'Ruta no encontrada'
    ], 404);