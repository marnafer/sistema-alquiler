<?php
/**
 * Sanitizador para la entidad Servicio
 * SOLO sanitiza los datos, NO valida
 */

/**
 * Sanitiza todos los datos de un servicio
 */
function sanitizarServicio($data) {
    return [
        'id' => sanitizarIdServicio($data['id'] ?? null),
        'nombre' => sanitizarNombreServicio($data['nombre'] ?? null)
    ];
}

/**
 * Sanitizar ID de servicio
 */
function sanitizarIdServicio($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar nombre de servicio
 */
function sanitizarNombreServicio($nombre) {
    if ($nombre === null || $nombre === '') {
        return null;
    }
    
    $nombre = trim($nombre);
    $nombre = preg_replace('/\s+/', ' ', $nombre);
    $nombre = ucwords(strtolower($nombre));
    $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    $nombre = strip_tags($nombre);
    
    if (strlen($nombre) > 50) {
        $nombre = substr($nombre, 0, 50);
    }
    
    return $nombre;
}

/**
 * Sanitizar solo nombre
 */
function sanitizarSoloNombreServicio($nombre) {
    return sanitizarNombreServicio($nombre);
}