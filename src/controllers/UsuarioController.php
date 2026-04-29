<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Helpers\JwtHelper;
use App\Middlewares\AutenticadorMiddleware;
use App\Sanitizers\UsuarioSanitizer;
use App\Validators\UsuarioValidator;

class UsuarioController
{
    /**
     * SOLO ADMIN
     * GET /api/usuarios
     */
    public function listarUsuariosApi()
    {
        $user = AutenticadorMiddleware::verificar();

        if ($user->rol_id != 3) {
            renderJson([
                'success' => false,
                'error' => 'Solo administradores'
            ], 403);
        }

        try {
            $usuarios = Usuario::obtenerTodos();

            renderJson([
                'success' => true,
                'data' => $usuarios,
                'total' => $usuarios->count()
            ], 200);

        } catch (\Exception $e) {
            renderJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/usuarios/{id}
     */
    public function mostrar($id)
    {
        $user = AutenticadorMiddleware::verificar();

        // Sanitizar y validar ID
        $idSan = sanitizarIdUsuario($id);
        $validacionId = validarSoloIdUsuario($idSan);

        if (!$validacionId['success']) {
            renderJson($validacionId, 400);
        }

        if ($user->rol_id != 3 && $user->sub != $idSan) {
            renderJson([
                'success' => false,
                'error' => 'No autorizado'
            ], 403);
        }

        try {
            $usuario = Usuario::obtenerPorId($idSan);

            if (!$usuario) {
                renderJson([
                    'success' => false,
                    'error' => 'Usuario no encontrado'
                ], 404);
            }

            renderJson([
                'success' => true,
                'data' => $usuario
            ], 200);

        } catch (\Exception $e) {
            renderJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/usuarios
     */
    public function registrar()
    {
        $raw = json_decode(file_get_contents('php://input'), true) ?? [];

        if (!$raw) {
            renderJson([
                'success' => false,
                'error' => 'Datos inválidos'
            ], 400);
        }

        // 1. Sanitizar
        $data = UsuarioSanitizer::sanitizarUsuario($raw);

        // 2. Validar
        $validacion = UsuarioValidator::validarCrearUsuario($data);

        if (!$validacion['success']) {
            renderJson([
                'success' => false,
                'errors' => $validacion['errors']
            ], 400);
        }

       // 3. Regla de negocio
        $usuarioModel = new Usuario();

        if ($usuarioModel->existePorEmail($data['email'])) {
            renderJson([
                'success' => false,
                'error' => 'Email ya registrado'
            ], 409);
        }

        try {
            $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);

            $usuario = Usuario::create($data);

            renderJson([
                'success' => true,
                'message' => 'Usuario creado',
                'data' => [
                    'id' => $usuario->id,
                    'email' => $usuario->email
                ]
            ], 201);

        } catch (\Exception $e) {
            renderJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * LOGIN
     */
    public static function login()
    {
        $raw = json_decode(file_get_contents('php://input'), true) ?? [];

        $email = $raw['email'] ?? null;
        $contrasena = $raw['contrasena'] ?? null;

        if (!$email || !$contrasena) {
            renderJson([
                'success' => false,
                'error' => 'Datos incompletos'
            ], 400);
            exit;
        }

        // Sanitizar email
        $email = UsuarioSanitizer::sanitizarSoloEmail($email); 

        // Validar email
        $validacion = UsuarioValidator::validarEmailLoginUsuario($email); 

        if (!$validacion['success']) {
            renderJson($validacion, 400);
        }

        $validacionPass = UsuarioValidator::validarContrasenaUsuario($contrasena); // Validar contraseña (aunque no se sanitice, se valida su formato)

        if (!$validacionPass['success']) {
            renderJson($validacionPass, 400);
        }

        $usuarioModel = new Usuario();

        $usuario = $usuarioModel->verificarCredenciales($email, $contrasena); // Se instancia el modelo para usar el método de verificación de credenciales

        if (!$usuario) {
            renderJson([
                'success' => false,
                'error' => 'Credenciales inválidas'
            ], 401);
        }

        $token = JwtHelper::generarToken($usuario);

        renderJson([
            'success' => true,
            'message' => 'Login exitoso',
            'token' => $token,
            'user' => [
                'id' => $usuario->id,
                'email' => $usuario->email,
                'rol_id' => $usuario->rol_id
            ]
        ], 200);
    }

    /**
     * LOGOUT
     */
    public function logout()
    {
        renderJson([
            'success' => true,
            'message' => 'Logout exitoso (el cliente elimina el token)'
        ], 200);
    }

    /**
     * DELETE
     */
    public function eliminar($id)
    {
        $user = AutenticadorMiddleware::verificar();

        if ($user->rol_id != 3) {
            renderJson([
                'success' => false,
                'error' => 'Solo admin'
            ], 403);
        }

        // Sanitizar y validar ID
        $idSan = sanitizarIdUsuario($id);
        $validacion = validarSoloIdUsuario($idSan);

        if (!$validacion['success']) {
            renderJson($validacion, 400);
        }

        try {
            $usuario = Usuario::find($idSan);

            if (!$usuario) {
                renderJson([
                    'success' => false,
                    'error' => 'Usuario no encontrado'
                ], 404);
            }

            $usuario->delete();

            renderJson([
                'success' => true,
                'message' => 'Usuario eliminado'
            ], 200);

        } catch (\Exception $e) {
            renderJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
    * PUT /api/usuarios/{id}  // Editar usuario, solo admin o el mismo usuario
    */

    public function actualizar($id)
    {
        $user = AutenticadorMiddleware::verificar(); 

        // Sanitizar y validar ID
        $idSan = UsuarioSanitizer::sanitizarIdUsuario($id);
        $validacionId = UsuarioValidator::validarSoloIdUsuario($idSan);

        if (!$validacionId['success']) {
            renderJson($validacionId, 400);
        }
        if ($user->rol_id != 3 && $user->sub != $idSan) {
            renderJson([
                'success' => false,
                'error' => 'No autorizado'
            ], 403);
        }
        $raw = json_decode(file_get_contents('php://input'), true) ?? []; 
        if (!is_array($raw)) {
            renderJson([
                'success' => false,
                'error' => 'Datos inválidos'
            ], 400);
        }

        // Sanitizar datos
        $data = UsuarioSanitizer::sanitizarUsuario($raw);

        // Validar datos
        $validacion = UsuarioValidator::validarActualizarUsuario($data);

        if (!$validacion['success']) {
            renderJson([
                'success' => false,
                'errors' => $validacion['errors']
            ], 400);
        }
        try {
            $usuario = Usuario::find($idSan);
            if (!$usuario || $usuario->deleted_at !== null) {
                renderJson([
                    'success' => false,
                    'error' => 'Usuario no encontrado'
                ], 404);
            }

            // Si se actualiza la contraseña, hashearla
            if (!empty($data['contrasena'])) {
                $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);
            }
            // Verificar si el nuevo email ya está registrado por otro usuario
            $usuarioModel = new Usuario();

            if (isset($data['email']) && $usuarioModel->existePorEmail($data['email'], $idSan)) {
                renderJson([
                    'success' => false,
                    'error' => 'Email ya registrado por otro usuario'
                ], 409);
            }

            $usuario->update($data);

            renderJson([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            renderJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}   
