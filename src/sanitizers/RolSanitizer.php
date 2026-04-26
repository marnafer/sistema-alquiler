<?php

namespace App\Sanitizers;

class RolSanitizer
{
    /**
     * Sanitizar todos los datos de un rol
     */
    public static function sanitizar(array $data): array
    {
        return [
            'id' => self::sanitizarId($data['id'] ?? null),
            'nombre' => self::sanitizarNombre($data['nombre'] ?? null)
        ];
    }

    /**
     * Sanitizar ID
     */
    public static function sanitizarId($id): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }
        $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
        return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
    }

    /**
     * Sanitizar nombre
     */
    public static function sanitizarNombre($nombre): ?string
    {
        if ($nombre === null || $nombre === '') {
            return null;
        }
        
        $nombre = trim($nombre);
        $nombre = strtolower($nombre);
        $nombre = preg_replace('/\s+/', ' ', $nombre);
        $nombre = preg_replace('/[^a-záéíóúñ\s]/u', '', $nombre);
        $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        
        if (strlen($nombre) > 30) {
            $nombre = substr($nombre, 0, 30);
        }
        
        return $nombre;
    }

    /**
     * Sanitizar solo nombre
     */
    public static function sanitizarSoloNombre($nombre): ?string
    {
        return self::sanitizarNombre($nombre);
    }
}