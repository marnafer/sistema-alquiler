<?php

namespace App\Controllers;

require_once __DIR__ . '/../sanitizers/PropiedadSanitizer.php';
require_once __DIR__ . '/../validators/PropiedadValidator.php';

use App\Models\Propiedad; // marianofer98 1

class PropiedadController {

    /**
     * Muestra el formulario de carga
     */
    public function mostrarFormulario() { // Agregamos 'public'
        header('Content-Type: text/html; charset=utf-8');
        require_once SRC_PATH . 'views/propiedades_form.php';
        exit;
    }

    /**
     * Lista las propiedades en formato JSON
     */
    public function listarPropiedades() { // Agregamos 'public'
        if (ob_get_length()) ob_clean();

        $respuesta = [
            "ok" => true,
            "mensaje" => "Listado de propiedades en construccion" 
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Procesa la creación de una propiedad
     */
    public function crearPropiedad() { // Agregamos 'public'
        $input = $_POST; 
        $datosLimpios = sanitizarPropiedad($input);
        $errores = validarPropiedad($datosLimpios);

        if (!empty($errores)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $errores
            ]);
            return;
        }

        try {
            $nuevaPropiedad = Propiedad::create($datosLimpios);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Propiedad creada con éxito',
                'id' => $nuevaPropiedad->id
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }
}