<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Helpers\JwtHelper;

class AutenticadorController {

    // LOGIN
    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            renderJson([
                'success' => false,
                'error' => 'Datos incompletos'
            ], 400);
        }

        $usuario = Usuario::where('email', $email)->first();

        if (!$usuario || !password_verify($password, $usuario->contrasena)) {
            renderJson([
                'success' => false,
                'error' => 'Credenciales inválidas'
            ], 401);
        }

        $token = JwtHelper::generarToken($usuario);

        renderJson([
            'success' => true,
            'token' => $token
        ]);
    }

    // REGISTER
    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data['email'] || !$data['password']) {
            renderJson([
                'success' => false,
                'error' => 'Datos incompletos'
            ], 400);
        }

        if (Usuario::where('email', $data['email'])->exists()) {
            renderJson([
                'success' => false,
                'error' => 'El usuario ya existe'
            ], 400);
        }

        $usuario = new Usuario();
        $usuario->email = $data['email'];
        $usuario->contrasena = password_hash($data['password'], PASSWORD_BCRYPT);
        $usuario->rol_id = 1;
        $usuario->save();

        renderJson([
            'success' => true,
            'message' => 'Usuario registrado'
        ], 201);
    }

    // LOGOUT
    public function logout() {
        renderJson([
            'success' => true,
            'message' => 'Logout (el cliente elimina el token)'
        ], 200);
    }
}