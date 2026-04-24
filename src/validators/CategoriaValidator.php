<?php

namespace App\Validators;

class CategoriaValidator
{
    /**
     * Valida ID (entero positivo)
     * Retorna ['success'=>bool, 'error'=>string|null]
     */
    public static function validarId($id): array
    {
        if ($id === null || $id === '') {
            return ['success' => false, 'error' => 'El ID es requerido'];
        }
        if (!is_numeric($id) || (int)$id <= 0) {
            return ['success' => false, 'error' => 'El ID debe ser un entero positivo'];
        }
        return ['success' => true, 'error' => null];
    }

    /**
     * Valida nombre de categoría
     */
    public static function validarNombre(?string $nombre): array
    {
        if ($nombre === null || $nombre === '') {
            return ['success' => false, 'error' => 'El nombre de la categoría es requerido'];
        }

        $len = mb_strlen($nombre);
        if ($len < 3) {
            return ['success' => false, 'error' => 'El nombre debe tener al menos 3 caracteres'];
        }
        if ($len > 50) {
            return ['success' => false, 'error' => 'El nombre no puede exceder los 50 caracteres'];
        }

        // Permitir letras Unicode, números, espacios, guiones y &
        if (!preg_match('/^[\p{L}\p{N}\s\-\&]+$/u', $nombre)) {
            return ['success' => false, 'error' => 'El nombre solo puede contener letras, números, espacios, guiones y &'];
        }

        return ['success' => true, 'error' => null];
    }

    /**
     * Valida payload completo. Espera datos ya sanitizados.
     * Retorna ['success'=>bool, 'errors'=>array|null, 'data'=>array|null]
     */
    public static function validarCategoria(array $data, bool $requerirId = false): array
    {
        $errores = [];

        if ($requerirId) {
            $resId = self::validarId($data['id'] ?? null);
            if (!$resId['success    ']) $errores['id'] = $resId['error'];
        }

        $resNombre = self::validarNombre($data['nombre'] ?? null);
        if (!$resNombre['success']) $errores['nombre'] = $resNombre['error'];

        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores, 'data' => null];
        }

        return ['success' => true, 'errors' => null, 'data' => $data];
    }
}