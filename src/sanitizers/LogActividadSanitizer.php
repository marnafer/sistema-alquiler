<?php
/**
 * Sanitizador para la entidad LogActividad
 * SOLO sanitiza los datos, NO valida
 */

/**
 * Sanitiza todos los datos de un log de actividad
 */
function sanitizarLogActividad($data) {
    return [
        'id' => sanitizarIdLogActividad($data['id'] ?? null),
        'usuario_id' => sanitizarUsuarioId($data['usuario_id'] ?? null),
        'accion' => sanitizarAccion($data['accion'] ?? null),
        'ip_address' => sanitizarIpAddress($data['ip_address'] ?? null),
        'fecha' => sanitizarFecha($data['fecha'] ?? null)
    ];
}

/**
 * Sanitizar ID de log de actividad
 */
function sanitizarIdLogActividad($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar ID de usuario
 */
function sanitizarUsuarioId($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar acción
 */
function sanitizarAccion($accion) {
    if ($accion === null || $accion === '') {
        return null;
    }
    
    $accion = trim($accion);
    $accion = preg_replace('/\s+/', ' ', $accion);
    $accion = htmlspecialchars($accion, ENT_QUOTES, 'UTF-8');
    $accion = strip_tags($accion);
    
    if (strlen($accion) > 255) {
        $accion = substr($accion, 0, 255);
    }
    
    return $accion;
}

/**
 * Sanitizar dirección IP
 */
function sanitizarIpAddress($ip) {
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
function sanitizarFecha($fecha) {
    if ($fecha === null || $fecha === '') {
        return null;
    }
    $timestamp = strtotime($fecha);
    return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
}

/**
 * Obtener IP del cliente (sanitizada)
 */
function obtenerIpClienteLog() {
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
    
    return sanitizarIpAddress($ip);
}