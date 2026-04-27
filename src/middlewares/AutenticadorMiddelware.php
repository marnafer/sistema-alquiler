<?php

namespace App\Middlewares;

use App\Helpers\JwtHelper;

class AutenticadorMiddleware {

    public static function verificar() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader) {
            http_response_code(401);
            echo json_encode(['error' => 'Token requerido']);
            exit;
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $user = JwtHelper::validarToken($token);

            return $user;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }
    }

    public static function soloPropietario() {
        $user = self::verificar();

        if ($user->rol_id != 2) {
            http_response_code(403);
            echo json_encode(['error' => 'Solo propietarios']);
            exit;
        }

        return $user;
    }

    public static function soloInquilino() {
        $user = self::verificar();

        if ($user->rol_id != 1) {
            http_response_code(403);
            echo json_encode(['error' => 'Solo inquilinos']);
            exit;
        }

        return $user;
    }
}