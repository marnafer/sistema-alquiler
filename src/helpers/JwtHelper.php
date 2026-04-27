<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../../config/config.php';

class JwtHelper {

    public static function generarToken($usuario) {
        $payload = [
            'sub' => $usuario->id,
            'email' => $usuario->email,
            'rol_id' => $usuario->rol_id,
            'iat' => time(),
            'exp' => time() + JWT_EXPIRATION
        ];

        return JWT::encode($payload, JWT_SECRET, 'HS256');
    }

    public static function validarToken($token) {
        return JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
    }
}