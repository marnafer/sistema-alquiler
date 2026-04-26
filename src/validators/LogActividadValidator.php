<?php

namespace App\Validators;

class LogActividadValidator
{
    /**
     * Validar todos los datos de un log
     */
    public static function validar(array $data, bool $requerirId = false): array
    {
        $errores = [];

        // Validar ID (solo si se requiere)
        if ($requerirId) {
            $error = self::validarId($data['id'] ?? null);
            if ($error) {
                $errores['id'] = $error;
            }
        }

        // Validar usuario_id (opcional)
        if (isset($data['usuario_id']) && !empty($data['usuario_id'])) {
            $error = self::validarUsuarioId($data['usuario_id']);
            if ($error) {
                $errores['usuario_id'] = $error;
            }
        }

        // Validar acción
        $error = self::validarAccion($data['accion'] ?? null);
        if ($error) {
            $errores['accion'] = $error;
        }

        // Validar IP (opcional)
        if (isset($data['ip_address']) && !empty($data['ip_address'])) {
            $error = self::validarIp($data['ip_address']);
            if ($error) {
                $errores['ip_address'] = $error;
            }
        }

        return $errores;
    }

    /**
     * Validar ID
     */
    public static function validarId($id): ?string
    {
        if ($id === null || $id === '') {
            return 'El ID de log es requerido';
        }

        if (!is_numeric($id)) {
            return 'El ID debe ser un número';
        }

        if ($id <= 0) {
            return 'El ID debe ser un número positivo';
        }

        return null;
    }

    /**
     * Validar ID de usuario
     */
    public static function validarUsuarioId($id): ?string
    {
        if ($id === null || $id === '') {
            return null; // Opcional
        }

        if (!is_numeric($id)) {
            return 'El ID de usuario debe ser un número';
        }

        if ($id <= 0) {
            return 'El ID de usuario debe ser un número positivo';
        }

        return null;
    }

    /**
     * Validar acción
     */
    public static function validarAccion($accion): ?string
    {
        if ($accion === null || $accion === '') {
            return 'La acción es requerida';
        }

        $accionLimpia = trim($accion);

        if (strlen($accionLimpia) < 3) {
            return 'La acción debe tener al menos 3 caracteres';
        }

        if (strlen($accionLimpia) > 255) {
            return 'La acción no puede exceder los 255 caracteres';
        }

        return null;
    }

    /**
     * Validar IP
     */
    public static function validarIp($ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return 'La dirección IP no es válida';
        }

        return null;
    }

    /**
     * Validar para crear nuevo log
     */
    public static function validarCrear(array $data): array
    {
        return self::validar($data, false);
    }

    /**
     * Validar solo ID
     */
    public static function validarSoloId($id): array
    {
        $error = self::validarId($id);

        if ($error) {
            return [
                'success' => false,
                'message' => 'ID inválido',
                'errors' => ['id' => $error]
            ];
        }

        return [
            'success' => true,
            'message' => 'ID válido',
            'errors' => null
        ];
    }
}