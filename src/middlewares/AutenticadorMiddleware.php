<?php

namespace App\Middlewares;

use App\Helpers\JwtHelper;

class AutenticadorMiddleware {

    public static function verificar() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] 
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
            ?? null;

        if (!$authHeader) {
            renderJson([
                'success' => false,
                'error' => 'Token requerido'
            ], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $user = JwtHelper::validarToken($token);
            return $user;

        } catch (\Exception $e) {
            renderJson([
                'success' => false,
                'error' => 'Token inválido'
                // opcional: $e->getMessage()
            ], 401);
        }
    }

    public static function soloPropietario() {
        $user = self::verificar();

        if ($user->rol_id != 2) {
            renderJson([
                'success' => false,
                'error' => 'Solo propietarios'
            ], 403);
        }

        return $user;
    }

    public static function soloInquilino() {
        $user = self::verificar();

        if ($user->rol_id != 1) {
            renderJson([
                'success' => false,
                'error' => 'Solo inquilinos'
            ], 403);
        }

        return $user;
    }

    public static function soloAdmin() {
        $user = self::verificar();

        if ($user->rol_id != 3) {
            renderJson([
                'success' => false,
                'error' => 'Solo administradores'
            ], 403);
        }

        return $user;
    }
}