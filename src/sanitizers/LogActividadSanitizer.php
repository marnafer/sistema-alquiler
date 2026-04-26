<?php

namespace App\Sanitizers;

class LogActividadSanitizer
{
    /**
     * Sanitizar todos los datos de un log
     */
    public static function sanitizar(array $data): array
    {
        return [
            'id' => self::sanitizarId($data['id'] ?? null),
            'usuario_id' => self::sanitizarUsuarioId($data['usuario_id'] ?? null),
            'accion' => self::sanitizarAccion($data['accion'] ?? null),
            'ip_address' => self::sanitizarIp($data['ip_address'] ?? null),
            'fecha' => self::sanitizarFecha($data['fecha'] ?? null)
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
     * Sanitizar ID de usuario
     */
    public static function sanitizarUsuarioId($id): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }
        $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
        return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
    }

    /**
     * Sanitizar acción
     */
    public static function sanitizarAccion($accion): ?string
    {
        if ($accion === null || $accion === '') {
            return null;
        }
        
        $accion = trim($accion);
        $accion = preg_replace('/\s+/', ' ', $accion);
        $accion = strip_tags($accion);
        $accion = htmlspecialchars($accion, ENT_QUOTES, 'UTF-8');
        
        if (strlen($accion) > 255) {
            $accion = substr($accion, 0, 255);
        }
        
        return $accion;
    }

    /**
     * Sanitizar IP address
     */
    public static function sanitizarIp($ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }
        
        $ip = trim($ip);
        
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        
        return null;
    }

    /**
     * Sanitizar fecha
     */
    public static function sanitizarFecha($fecha): ?string
    {
        if ($fecha === null || $fecha === '') {
            return null;
        }
        $timestamp = strtotime($fecha);
        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    /**
     * Obtener IP del cliente
     */
    public static function getClientIp(): ?string
    {
        $ip = null;
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        }
        
        if ($ip) {
            $ip = explode(',', $ip)[0];
            $ip = trim($ip);
        }
        
        return self::sanitizarIp($ip);
    }
}