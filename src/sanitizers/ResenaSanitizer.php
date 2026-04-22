<?php
/**
 * Sanitizador para la entidad Reseña
 * SOLO sanitiza los datos, NO valida
 */

/**
 * Sanitiza todos los datos de una reseña
 */
function sanitizarResena($data) {
    return [
        'id' => sanitizarIdResena($data['id'] ?? null),
        'reserva_id' => sanitizarIdReserva($data['reserva_id'] ?? null),
        'calificacion' => sanitizarCalificacion($data['calificacion'] ?? null),
        'comentario' => sanitizarComentario($data['comentario'] ?? null),
        'fecha_publicacion' => sanitizarFechaPublicacion($data['fecha_publicacion'] ?? null)
    ];
}

/**
 * Sanitizar ID de reseña
 */
function sanitizarIdResena($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar ID de reserva
 */
function sanitizarIdReserva($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar calificación (1-5)
 */
function sanitizarCalificacion($calificacion) {
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
function sanitizarComentario($comentario) {
    if ($comentario === null || $comentario === '') {
        return null;
    }
    
    // Eliminar espacios al inicio y final
    $comentario = trim($comentario);
    
    // Eliminar espacios múltiples
    $comentario = preg_replace('/\s+/', ' ', $comentario);
    
    // Eliminar etiquetas HTML/script
    $comentario = strip_tags($comentario);
    
    // Escapar caracteres especiales
    $comentario = htmlspecialchars($comentario, ENT_QUOTES, 'UTF-8');
    
    // Limitar longitud a 1000 caracteres
    if (strlen($comentario) > 1000) {
        $comentario = substr($comentario, 0, 1000);
    }
    
    return $comentario;
}

/**
 * Sanitizar fecha de publicación
 */
function sanitizarFechaPublicacion($fecha) {
    if ($fecha === null || $fecha === '') {
        return null;
    }
    $timestamp = strtotime($fecha);
    return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
}

/**
 * Sanitizar solo calificación
 */
function sanitizarSoloCalificacion($calificacion) {
    return sanitizarCalificacion($calificacion);
}

/**
 * Sanitizar solo comentario
 */
function sanitizarSoloComentario($comentario) {
    return sanitizarComentario($comentario);
}