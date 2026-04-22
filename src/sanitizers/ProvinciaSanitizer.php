<?php
/**
 * Sanitizador para la entidad Provincia
 * SOLO sanitiza los datos, NO valida
 */

/**
 * Sanitiza todos los datos de una provincia
 */
function sanitizarProvincia($data) {
    return [
        'id' => sanitizarIdProvincia($data['id'] ?? null),
        'nombre' => sanitizarNombreProvincia($data['nombre'] ?? null)
    ];
}

/**
 * Sanitizar ID de provincia
 */
function sanitizarIdProvincia($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar nombre de provincia
 */
function sanitizarNombreProvincia($nombre) {
    if ($nombre === null || $nombre === '') {
        return null;
    }
    
    // Eliminar espacios al inicio y final
    $nombre = trim($nombre);
    
    // Eliminar espacios múltiples
    $nombre = preg_replace('/\s+/', ' ', $nombre);
    
    // Convertir a mayúsculas iniciales cada palabra
    $nombre = ucwords(strtolower($nombre));
    
    // Eliminar caracteres especiales (solo letras, espacios, acentos)
    $nombre = preg_replace('/[^a-zA-ZáéíóúñÑÁÉÍÓÚ\s]/u', '', $nombre);
    
    // Escapar caracteres especiales para JSON/HTML
    $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    
    // Limitar longitud a 100 caracteres
    if (strlen($nombre) > 100) {
        $nombre = substr($nombre, 0, 100);
    }
    
    return $nombre;
}

/**
 * Sanitizar solo nombre
 */
function sanitizarSoloNombreProvincia($nombre) {
    return sanitizarNombreProvincia($nombre);
}