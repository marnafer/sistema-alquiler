<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Helpers\JwtHelper;
use App\Middlewares\AutenticadorMiddleware;

class UsuarioController
{
    /**
     * SOLO ADMIN
     * GET /api/usuarios
     */
    public function listarUsuariosApi()
    {
        $user = AutenticadorMiddleware::verificar();

        if ($user->rol != 3) {
            renderJson([
                'success' => false,
                'error' => 'Solo administradores'
            ], 403);
            return;
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

        if ($user->rol != 3 && $user->sub != $id) {
            renderJson([
                'success' => false,
                'error' => 'No autorizado'
            ], 403);
            return;
        }

        try {
            $usuario = Usuario::obtenerPorId($id);

            if (!$usuario) {
                renderJson([
                    'success' => false,
                    'error' => 'Usuario no encontrado'
                ], 404);
                return;
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
     * POST /api/usuarios (registro público o admin)
     */
    public function guardar()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            renderJson(['success' => false, 'error' => 'Datos inválidos'], 400);
            return;
        }

        if (Usuario::existePorEmail($data['email'])) {
            renderJson(['success' => false, 'error' => 'Email ya registrado'], 409);
            return;
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
     * LOGIN JWT
     */
    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['email'], $data['contrasena'])) {
            renderJson(['success' => false, 'error' => 'Datos incompletos'], 400);
            return;
        }

        $usuario = Usuario::verificarCredenciales(
            sanitizarSoloEmail($data['email']),
            $data['contrasena']
        );

        if (!$usuario) {
            renderJson(['success' => false, 'error' => 'Credenciales inválidas'], 401);
            return;
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
     * DELETE usuario
     */
    public function eliminar($id)
    {
        $user = AutenticadorMiddleware::verificar();

        if ($user->rol != 3) {
            renderJson(['success' => false, 'error' => 'Solo admin'], 403);
            return;
        }

        try {
            if (!Usuario::existe($id)) {
                renderJson(['success' => false, 'error' => 'No existe'], 404);
                return;
            }

            Usuario::destroy($id);

            renderJson([
                'success' => true,
                'message' => 'Usuario eliminado'
            ], 200);

        } catch (\Exception $e) {
            renderJson(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}