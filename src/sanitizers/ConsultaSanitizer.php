<?php
namespace App\Sanitizers;

/**
 * Sanitiza todos los datos de una consulta
 * @param array $data Datos crudos a sanitizar
 * @return array Datos sanitizados
 */
function sanitizarConsulta($data) {
    return [
        'id' => sanitizarIdConsulta($data['id'] ?? null),
        'propiedad_id' => sanitizarIdPropiedad($data['propiedad_id'] ?? null),
        'inquilino_id' => sanitizarIdInquilino($data['inquilino_id'] ?? null),
        'mensaje' => sanitizarMensajeConsulta($data['mensaje'] ?? null),
        'fecha_consulta' => sanitizarFechaConsulta($data['fecha_consulta'] ?? null)
    ];
}

/**
 * Sanitizar ID de consulta
 * @param mixed $id ID a sanitizar
 * @return int|null ID sanitizado o null
 */
function sanitizarIdConsulta($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return $idSanitizado !== false && $idSanitizado > 0 ? $idSanitizado : null;
}

/**
 * Sanitizar ID de propiedad
 * @param mixed $id ID a sanitizar
 * @return int|null ID sanitizado o null
 */
function sanitizarIdPropiedad($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return $idSanitizado !== false && $idSanitizado > 0 ? $idSanitizado : null;
}

/**
 * Sanitizar ID de inquilino
 * @param mixed $id ID a sanitizar
 * @return int|null ID sanitizado o null
 */
function sanitizarIdInquilino($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return $idSanitizado !== false && $idSanitizado > 0 ? $idSanitizado : null;
}

/**
 * Sanitizar mensaje de consulta
 * @param string $mensaje Mensaje crudo
 * @return string|null Mensaje sanitizado o null
 */
function sanitizarMensajeConsulta($mensaje) {
    if ($mensaje === null || $mensaje === '') {
        return null;
    }
    
    // Eliminar espacios al inicio y final
    $mensaje = trim($mensaje);
    
    // Eliminar espacios múltiples
    $mensaje = preg_replace('/\s+/', ' ', $mensaje);
    
    // Eliminar etiquetas HTML/script
    $mensaje = strip_tags($mensaje);
    
    // Escapar caracteres especiales para JSON/HTML
    $mensaje = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    
    // Limitar longitud a 5000 caracteres
    if (strlen($mensaje) > 5000) {
        $mensaje = substr($mensaje, 0, 5000);
    }
    
    return $mensaje;
}

/**
 * Sanitizar fecha de consulta
 * @param string $fecha Fecha cruda
 * @return string|null Fecha en formato Y-m-d H:i:s o null
 */
function sanitizarFechaConsulta($fecha) {
    if ($fecha === null || $fecha === '') {
        return null;
    }
    
    $timestamp = strtotime($fecha);
    return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
}

/**
 * Sanitizar solo el mensaje (útil para actualizaciones parciales)
 * @param string $mensaje Mensaje crudo
 * @return string|null Mensaje sanitizado
 */
function sanitizarSoloMensajeConsulta($mensaje) {
    return sanitizarMensajeConsulta($mensaje);
}

/**
 * Sanitizar solo los IDs (útil para validaciones rápidas)
 * @param array $data Datos con IDs
 * @return array IDs sanitizados
 */
function sanitizarIdsConsulta($data) {
    return [
        'id' => sanitizarIdConsulta($data['id'] ?? null),
        'propiedad_id' => sanitizarIdPropiedad($data['propiedad_id'] ?? null),
        'inquilino_id' => sanitizarIdInquilino($data['inquilino_id'] ?? null)
    ];
}