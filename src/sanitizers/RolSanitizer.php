<?php
/**
 * Sanitizador para la entidad Rol
 * SOLO sanitiza los datos, NO valida
 */

/**
 * Sanitiza todos los datos de un rol
 */
function sanitizarRol($data) {
    return [
        'id' => sanitizarIdRol($data['id'] ?? null),
        'nombre' => sanitizarNombreRol($data['nombre'] ?? null)
    ];
}

/**
 * Sanitizar ID de rol
 */
function sanitizarIdRol($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar nombre de rol
 */
function sanitizarNombreRol($nombre) {
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
function sanitizarSoloNombreRol($nombre) {
    return sanitizarNombreRol($nombre);
}