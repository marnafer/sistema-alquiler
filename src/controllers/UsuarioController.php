<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Sanitizers\UsuarioSanitizer;
use App\Validators\UsuarioValidator;

class UsuarioController {

    /**
     * VISTA: Muestra la tabla de usuarios (HTML)
     * Ruta: /usuarios (GET)
     */
    public function listarUsuarios() { 
        try {
            $usuarios = Usuario::all(); 
            require_once SRC_PATH . 'views/usuarios/usuarios_listar.php';
        } catch (\Exception $e) {
            die("Error al listar usuarios: " . $e->getMessage());
        }
    }

    /**
     * API: Retorna los datos de usuarios (JSON)
     * Ruta: /api/usuarios (GET)
     */
    public function indexApi() {
        header('Content-Type: application/json');
        try {
            // Seleccionamos campos seguros (sin password)
            $usuarios = Usuario::all(['id', 'nombre', 'email', 'rol_id']);
            echo json_encode(['status' => 'success', 'data' => $usuarios]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * VISTA: Muestra el formulario (HTML)
     */
    public function mostrarFormulario() {
        $datos = [];
        $errores = [];
        require_once SRC_PATH . 'views/usuarios/usuarios_registrar.php';
    }

    /**
     * API: Procesa la creación (JSON)
     * Ruta: /api/usuarios (POST)
     */
    public function guardar() {
        header('Content-Type: application/json');

        // Soporte para JSON crudo o FormData
        $inputRaw = file_get_contents("php://input");
        $inputData = json_decode($inputRaw, true) ?? $_POST;

        // 1. Sanitización
        $datosLimpios = UsuarioSanitizer::sanitizarUsuario($inputData);
        
        // 2. Validación
        $errores = UsuarioValidator::validarUsuario($datosLimpios);

        if (!empty($errores)) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'errors' => $errores]);
            return;
        }

        try {
            // Hash de contraseña antes de guardar (OBLIGATORIO en APIs)
            if (isset($datosLimpios['password'])) {
                $datosLimpios['password'] = password_hash($datosLimpios['password'], PASSWORD_BCRYPT);
            }

            $usuario = Usuario::create($datosLimpios);

            http_response_code(201); // Created
            echo json_encode([
                'status' => 'success',
                'message' => 'Usuario creado exitosamente',
                'data' => ['id' => $usuario->id, 'email' => $usuario->email]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Elimina un usuario (JSON)
     * Ruta: /api/usuarios/{id} (DELETE)
     */
    public function eliminar($id) {
        header('Content-Type: application/json');
        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
                return;
            }

            $usuario->delete();
            echo json_encode(['status' => 'success', 'message' => "Usuario #$id eliminado"]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}