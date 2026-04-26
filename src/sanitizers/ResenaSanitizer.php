<?php

namespace App\Sanitizers;

class ResenaSanitizer
{
    /**
     * Sanitizar todos los datos de una reseña
     */
    public static function sanitizar(array $data): array
    {
        return [
            'id' => self::sanitizarId($data['id'] ?? null),
            'reserva_id' => self::sanitizarReservaId($data['reserva_id'] ?? null),
            'calificacion' => self::sanitizarCalificacion($data['calificacion'] ?? null),
            'comentario' => self::sanitizarComentario($data['comentario'] ?? null),
            'fecha_publicacion' => self::sanitizarFecha($data['fecha_publicacion'] ?? null)
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
     * Sanitizar ID de reserva
     */
    public static function sanitizarReservaId($id): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }
        $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
        return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
    }

    /**
     * Sanitizar calificación (1-5)
     */
    public static function sanitizarCalificacion($calificacion): ?int
    {
        if ($calificacion === null || $calificacion === '') {
            return null;
        }
        
        $calificacion = filter_var($calificacion, FILTER_VALIDATE_INT);
        
        if ($calificacion !== false && $calificacion >= 1 && $calificacion <= 5) {
            return $calificacion;
        }
        
        return null;
    }

    /**
     * Sanitizar comentario
     */
    public static function sanitizarComentario($comentario): ?string
    {
        if ($comentario === null || $comentario === '') {
            return null;
        }
        
        $comentario = trim($comentario);
        $comentario = preg_replace('/\s+/', ' ', $comentario);
        $comentario = strip_tags($comentario);
        $comentario = htmlspecialchars($comentario, ENT_QUOTES, 'UTF-8');
        
        if (strlen($comentario) > 1000) {
            $comentario = substr($comentario, 0, 1000);
        }
        
        return $comentario;
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
     * Sanitizar solo calificación
     */
    public static function sanitizarSoloCalificacion($calificacion): ?int
    {
        return self::sanitizarCalificacion($calificacion);
    }

    /**
     * Sanitizar solo comentario
     */
    public static function sanitizarSoloComentario($comentario): ?string
    {
        return self::sanitizarComentario($comentario);
    }
}