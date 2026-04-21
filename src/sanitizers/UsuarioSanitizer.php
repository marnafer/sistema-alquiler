<?php

namespace App\Sanitizers;

class UsuarioSanitizer {
    /**
     * Limpia los datos de entrada para evitar XSS y ruidos en DB
     */
    public static function sanitizarUsuario(array $datos): array {
        $sanitizados = [];

        $sanitizados['nombre'] = isset($datos['nombre']) 
            ? htmlspecialchars(trim($datos['nombre']), ENT_QUOTES, 'UTF-8') 
            : null;

        $sanitizados['email'] = isset($datos['email']) 
            ? filter_var(trim(strtolower($datos['email'])), FILTER_SANITIZE_EMAIL) 
            : null;

        $sanitizados['password'] = $datos['password'] ?? null; // El password no se sanitiza (se hashea luego)

        $sanitizados['rol_id'] = isset($datos['rol_id']) 
            ? filter_var($datos['rol_id'], FILTER_SANITIZE_NUMBER_INT) 
            : null;

        return $sanitizados;
    }
}