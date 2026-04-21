<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Sanitizers\UsuarioSanitizer;
use App\Validators\UsuarioValidator;

class UsuarioController {

    /**
     * Lista los usuarios en una vista de tabla
     * Ruta: /usuarios
     */
    public function listarUsuarios() { 
        try {
            // Obtenemos todos los usuarios de la DB usando Eloquent
            $usuarios = Usuario::all(); 

            // Cargamos la vista de listado
            require_once SRC_PATH . 'views/usuarios/usuarios_listar.php';
        } catch (\Exception $e) {
            die("Error al listar usuarios: " . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario de registro
     * Ruta: /usuarios/nuevo
     */
    public function mostrarFormulario() {
        $datos = [];
        $errores = [];
        require_once SRC_PATH . 'views/usuarios/usuarios_registrar.php';
    }

    /**
     * Procesa la creación de un usuario
     * Ruta: /usuarios/guardar (POST)
     */
    public function crearUsuario() {
        // 1. Sanitización
        $datosLimpios = UsuarioSanitizer::sanitizarUsuario($_POST);
        
        // 2. Validación
        $errores = UsuarioValidator::validarUsuario($datosLimpios);

        if (!empty($errores)) {
            $datos = $datosLimpios;
            require_once SRC_PATH . 'views/usuarios/usuarios_registrar.php';
            return;
        }

        try {
            // 3. Persistencia
            Usuario::create($datosLimpios);

            // 4. Redirección al listado con mensaje de éxito
            header('Location: /usuarios?status=success');
            exit;
        } catch (\Exception $e) {
            $errores['db'] = "Error en la base de datos: " . $e->getMessage();
            $datos = $datosLimpios;
            require_once SRC_PATH . 'views/usuarios/usuarios_registrar.php';
        }
    }
}