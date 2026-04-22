<?php
/**
 * Validador para la entidad Reseña
 * SOLO valida los datos, NO sanitiza
 */

/**
 * Validar todos los datos de una reseña
 */
function validarResena($data, $requerirId = false) {
    $errores = [];
    
    // Validar ID (solo si se requiere)
    if ($requerirId) {
        $resultado = validarIdRequerido($data['id'] ?? null, 'reseña');
        if (!$resultado['valido']) {
            $errores['id'] = $resultado['error'];
        }
    }
    
    // Validar reserva_id
    $resultado = validarIdRequerido($data['reserva_id'] ?? null, 'reserva');
    if (!$resultado['valido']) {
        $errores['reserva_id'] = $resultado['error'];
    }
    
    // Validar calificación
    $resultado = validarCalificacion($data['calificacion'] ?? null);
    if (!$resultado['valido']) {
        $errores['calificacion'] = $resultado['error'];
    }
    
    // Validar comentario (opcional)
    if (isset($data['comentario']) && !empty($data['comentario'])) {
        $resultado = validarComentario($data['comentario']);
        if (!$resultado['valido']) {
            $errores['comentario'] = $resultado['error'];
        }
    }
    
    // Retornar resultado
    if (count($errores) > 0) {
        return [
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $errores
        ];
    }
    
    return [
        'success' => true,
        'message' => 'Validación exitosa',
        'errors' => null
    ];
}

/**
 * Validar ID requerido
 */
function validarIdRequerido($id, $campo = '') {
    if ($id === null || $id === '') {
        $mensaje = $campo ? "El ID de $campo es requerido" : "El ID es requerido";
        return ['valido' => false, 'error' => $mensaje];
    }
    
    if (!is_numeric($id)) {
        $mensaje = $campo ? "El ID de $campo debe ser un número" : "El ID debe ser un número";
        return ['valido' => false, 'error' => $mensaje];
    }
    
    if ($id <= 0) {
        $mensaje = $campo ? "El ID de $campo debe ser positivo" : "El ID debe ser positivo";
        return ['valido' => false, 'error' => $mensaje];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar calificación (1-5)
 */
function validarCalificacion($calificacion) {
    if ($calificacion === null || $calificacion === '') {
        return ['valido' => false, 'error' => 'La calificación es requerida'];
    }
    
    if (!is_numeric($calificacion)) {
        return ['valido' => false, 'error' => 'La calificación debe ser un número'];
    }
    
    $calificacion = (int)$calificacion;
    
    if ($calificacion < 1 || $calificacion > 5) {
        return ['valido' => false, 'error' => 'La calificación debe ser entre 1 y 5 estrellas'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar comentario
 */
function validarComentario($comentario) {
    if ($comentario === null || $comentario === '') {
        return ['valido' => true, 'error' => null]; // Comentario opcional
    }
    
    $comentarioLimpio = trim($comentario);
    $longitud = strlen($comentarioLimpio);
    
    if ($longitud < 3) {
        return ['valido' => false, 'error' => 'El comentario debe tener al menos 3 caracteres'];
    }
    
    if ($longitud > 1000) {
        return ['valido' => false, 'error' => 'El comentario no puede exceder los 1000 caracteres'];
    }
    
    if (preg_match('/^\s*$/', $comentarioLimpio)) {
        return ['valido' => false, 'error' => 'El comentario no puede estar vacío o contener solo espacios'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar para crear nueva reseña
 */
function validarCrearResena($data) {
    return validarResena($data, false);
}

/**
 * Validar para actualizar reseña existente
 */
function validarActualizarResena($data) {
    return validarResena($data, true);
}

/**
 * Validar solo ID
 */
function validarSoloIdResena($id) {
    $resultado = validarIdRequerido($id, 'reseña');
    
    if (!$resultado['valido']) {
        return [
            'success' => false,
            'message' => 'ID inválido',
            'errors' => ['id' => $resultado['error']]
        ];
    }
    
    return [
        'success' => true,
        'message' => 'ID válido',
        'errors' => null
    ];
}

/**
 * Validar solo calificación
 */
function validarSoloCalificacion($calificacion) {
    $resultado = validarCalificacion($calificacion);
    
    if (!$resultado['valido']) {
        return [
            'success' => false,
            'message' => 'Calificación inválida',
            'errors' => ['calificacion' => $resultado['error']]
        ];
    }
    
    return [
        'success' => true,
        'message' => 'Calificación válida',
        'errors' => null
    ];
}