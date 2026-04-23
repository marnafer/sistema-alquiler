<?php

namespace App\Sanitizers;

class PropiedadImagenSanitizer
{
    public static function sanitizarId($id): int
    {
        return (int) filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitiza payload para creación de imagenes.
     * Devuelve arreglo con propiedad_id (int), descripcion (string|null) y posible imagen_base64.
     */
    public static function sanitizarPropiedadImagen(array $data): array
    {
        return [
            'propiedad_id'    => isset($data['propiedad_id']) ? (int) filter_var($data['propiedad_id'], FILTER_SANITIZE_NUMBER_INT) : 0,
            'descripcion'     => isset($data['descripcion']) && trim($data['descripcion']) !== '' ? htmlspecialchars(trim($data['descripcion']), ENT_QUOTES, 'UTF-8') : null,
            'imagen_base64'   => isset($data['imagen_base64']) ? trim($data['imagen_base64']) : null
        ];
    }
}