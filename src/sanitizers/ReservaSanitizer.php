<?php
/**
 * Sanitizador para la entidad Reserva (versión simplificada)
 * SOLO sanitiza los datos, NO valida
 */

/**
 * Sanitiza todos los datos de una reserva
 */
function sanitizarReserva($data) {
    return [
        'id' => sanitizarIdReserva($data['id'] ?? null),
        'propiedad_id' => sanitizarIdPropiedad($data['propiedad_id'] ?? null),
        'inquilino_id' => sanitizarIdInquilino($data['inquilino_id'] ?? null),
        'fecha_desde' => sanitizarFecha($data['fecha_desde'] ?? null),
        'fecha_hasta' => sanitizarFecha($data['fecha_hasta'] ?? null),
        'precio_total' => sanitizarPrecio($data['precio_total'] ?? null),
        'estado' => sanitizarEstado($data['estado'] ?? null)
    ];
}

/**
 * Sanitizar ID
 */
function sanitizarIdReserva($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar ID de propiedad
 */
function sanitizarIdPropiedad($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar ID de inquilino
 */
function sanitizarIdInquilino($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar fecha (formato Y-m-d)
 */
function sanitizarFecha($fecha) {
    if ($fecha === null || $fecha === '') {
        return null;
    }
    $timestamp = strtotime($fecha);
    return $timestamp ? date('Y-m-d', $timestamp) : null;
}

/**
 * Sanitizar precio
 */
function sanitizarPrecio($precio) {
    if ($precio === null || $precio === '') {
        return null;
    }
    
    // Reemplazar coma por punto
    $precio = str_replace(',', '.', $precio);
    
    // Eliminar cualquier cosa que no sea número o punto
    $precio = preg_replace('/[^0-9\.]/', '', $precio);
    
    // Validar como float
    $precioSanitizado = filter_var($precio, FILTER_VALIDATE_FLOAT);
    
    if ($precioSanitizado !== false && $precioSanitizado > 0) {
        return round($precioSanitizado, 2);
    }
    
    return null;
}

/**
 * Sanitizar estado
 */
function sanitizarEstado($estado) {
    $estadosValidos = ['pendiente', 'confirmada', 'cancelada', 'finalizada'];
    
    if ($estado === null || $estado === '') {
        return 'pendiente';
    }
    
    $estado = strtolower(trim($estado));
    $estado = htmlspecialchars($estado, ENT_QUOTES, 'UTF-8');
    
    return in_array($estado, $estadosValidos) ? $estado : 'pendiente';
}

/**
 * Sanitizar solo fechas (para verificar disponibilidad)
 */
function sanitizarFechasReserva($data) {
    return [
        'fecha_desde' => sanitizarFecha($data['fecha_desde'] ?? null),
        'fecha_hasta' => sanitizarFecha($data['fecha_hasta'] ?? null)
    ];
}

/**
 * Sanitizar solo estado
 */
function sanitizarSoloEstadoReserva($estado) {
    return sanitizarEstado($estado);
}

/**
 * Sanitizar solo IDs
 */
function sanitizarIdsReserva($data) {
    return [
        'id' => sanitizarIdReserva($data['id'] ?? null),
        'propiedad_id' => sanitizarIdPropiedad($data['propiedad_id'] ?? null),
        'inquilino_id' => sanitizarIdInquilino($data['inquilino_id'] ?? null)
    ];
}