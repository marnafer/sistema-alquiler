<?php

namespace App\Sanitizers;

class CategoriaSanitizer
{
    /**
     * Sanitiza un ID (por ejemplo de la URL)
     */
    public static function sanitizeId($id): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }
        $val = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        return $val === false ? null : (int)$val;
    }

    /**
     * Sanitiza el nombre:
     * - trim
     * - colapsa espacios
     * - capitaliza palabras (Unicode-safe)
     * - escapa HTML
     */
    public static function sanitizarNombre(string $nombre): string
    {
        $nombre = trim($nombre);
        $nombre = preg_replace('/\s+/u', ' ', $nombre);
        $nombre = mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8');
        return htmlspecialchars($nombre, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sanitiza todo el payload de categoría
     */
    public static function sanitizarCategoria(array $data): array
    {
        return [
            'id'     => isset($data['id']) ? self::sanitizeId($data['id']) : null,
            'nombre' => isset($data['nombre']) && $data['nombre'] !== '' ? self::sanitizarNombre((string)$data['nombre']) : null,
        ];
    }
}