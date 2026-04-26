<?php

namespace App\Sanitizers;

class PropiedadServicioSanitizer
{
    /**
     * Sanitizar todos los datos de una relación propiedad-servicio
     */
    public static function sanitizar(array $data): array
    {
        return [
            'id' => self::sanitizarId($data['id'] ?? null),
            'propiedad_id' => self::sanitizarPropiedadId($data['propiedad_id'] ?? null),
            'servicio_id' => self::sanitizarServicioId($data['servicio_id'] ?? null)
        ];
    }

    /**
     * Sanitizar ID de la relación
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
     * Sanitizar ID de propiedad
     */
    public static function sanitizarPropiedadId($id): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }
        $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
        return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
    }

    /**
     * Sanitizar ID de servicio
     */
    public static function sanitizarServicioId($id): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }
        $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
        return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
    }

    /**
     * Sanitizar solo IDs de propiedad y servicio (para validaciones rápidas)
     */
    public static function sanitizarIds(array $data): array
    {
        return [
            'propiedad_id' => self::sanitizarPropiedadId($data['propiedad_id'] ?? null),
            'servicio_id' => self::sanitizarServicioId($data['servicio_id'] ?? null)
        ];
    }
}