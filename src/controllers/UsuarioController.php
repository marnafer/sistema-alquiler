<?php

namespace App\Controllers;

require_once __DIR__ . '/../sanitizers/UsuarioSanitizer.php';
require_once __DIR__ . '/../validators/UsuarioValidator.php';

use App\Models\Usuario;

Class UsuarioController {

    /**
     * Muestra el formulario de carga
     */
    public function mostrarFormulario() {
        header('Content-Type: text/html; charset=utf-8');
        require_once SRC_PATH . 'views/usuarios_form.php';
        exit;
    }

    /**
     * Lista los usuarios en formato JSON
     */
    public function listarUsuarios() { 
        if (ob_get_length()) ob_clean();

        $respuesta = [
            "ok" => true,
            "mensaje" => "Listado de usuarios en construccion" 
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Procesa la creación de un usuario
     */
    public function crearUsuario() {
        $input = $_POST; 
        $datosLimpios = sanitizarUsuario($input);
        $errores = validarUsuario($datosLimpios);

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
            $nuevoUsuario = Usuario::create($datosLimpios);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => $nuevoUsuario
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar el usuario: ' . $e->getMessage()
            ]);
        }
    }
}