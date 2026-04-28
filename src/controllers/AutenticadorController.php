<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Helpers\JwtHelper;
use App\Sanitizers\UsuarioSanitizer;
use Ao

class AutenticadorController {

    // LOGIN
    public function login() {
        $data = json_decode(file_get_contents("php://input"), true) ?? [];

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        // 1. Validar que vengan los datos
        if (!$email || !$password) {
            renderJson([
                'success' => false,
                'error' => 'Datos incompletos'
            ], 400);
        }

        // 2. Sanitizar email
        $email = sanitizarSoloEmail($email);

        // 3. Validar formato de email
        $validacionEmail = validarEmailLoginUsuario($email);

        if (!$validacionEmail['success']) {
            renderJson($validacionEmail, 400);
        }

        $usuario = Usuario::where('email', $email)->first();

        if (!$usuario || !password_verify($password, $usuario->contrasena)) {
            renderJson([
                'success' => false,
                'error' => 'Credenciales invalidas'
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
        $data = json_decode(file_get_contents("php://input"), true) ?? [];

        // 1. Sanitizar
        $san = sanitizarUsuario($data);

        // 2. Validar datos
        $val = validarCrearUsuario($san);

        if (!$val['success']) {
            renderJson($val, 400);
        }

        // 3. Validación de negocio (DB)
        if (Usuario::where('email', $san['email'])->exists()) {
            renderJson([
                'success' => false,
                'error' => 'El usuario ya existe'
            ], 409); 
        }

        $san['contrasena'] = password_hash($san['contrasena'], PASSWORD_BCRYPT);
        $san['rol_id'] = $san['rol_id'] ?? 1;

        $usuario = Usuario::create($san);

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