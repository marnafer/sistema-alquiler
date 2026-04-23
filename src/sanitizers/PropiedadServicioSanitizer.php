<?php
/**
 * Sanitizador para la entidad PropiedadServicio
 * SOLO sanitiza los datos, NO valida
 */

/**
 * Sanitiza todos los datos de una relación propiedad-servicio
 */
function sanitizarPropiedadServicio($data) {
    return [
        'id' => sanitizarIdPropiedadServicio($data['id'] ?? null),
        'propiedad_id' => sanitizarPropiedadId($data['propiedad_id'] ?? null),
        'servicio_id' => sanitizarServicioId($data['servicio_id'] ?? null)
    ];
}

/**
 * Sanitizar ID de la relación
 */
function sanitizarIdPropiedadServicio($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar ID de propiedad
 */
function sanitizarPropiedadId($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar ID de servicio
 */
function sanitizarServicioId($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar solo IDs de propiedad y servicio
 */
function sanitizarIdsPropiedadServicio($data) {
    return [
        'propiedad_id' => sanitizarPropiedadId($data['propiedad_id'] ?? null),
        'servicio_id' => sanitizarServicioId($data['servicio_id'] ?? null)
    ];
}