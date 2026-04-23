<?php
/**
 * Validador para la entidad Servicio
 * SOLO valida los datos, NO sanitiza
 */

/**
 * Validar todos los datos de un servicio
 */
function validarServicio($data, $requerirId = false) {
    $errores = [];
    
    // Validar ID (solo si se requiere)
    if ($requerirId) {
        $resultado = validarIdRequeridoServicio($data['id'] ?? null, 'servicio');
        if (!$resultado['valido']) {
            $errores['id'] = $resultado['error'];
        }
    }
    
    // Validar nombre
    $resultado = validarNombreServicio($data['nombre'] ?? null);
    if (!$resultado['valido']) {
        $errores['nombre'] = $resultado['error'];
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
function validarIdRequeridoServicio($id, $campo = '') {
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
 * Validar nombre de servicio
 */
function validarNombreServicio($nombre) {
    if ($nombre === null || $nombre === '') {
        return ['valido' => false, 'error' => 'El nombre del servicio es requerido'];
    }
    
    $nombreLimpio = trim($nombre);
    $longitud = strlen($nombreLimpio);
    
    if ($longitud < 3) {
        return ['valido' => false, 'error' => 'El nombre debe tener al menos 3 caracteres'];
    }
    
    if ($longitud > 50) {
        return ['valido' => false, 'error' => 'El nombre no puede exceder los 50 caracteres'];
    }
    
    if (is_numeric($nombreLimpio)) {
        return ['valido' => false, 'error' => 'El nombre no puede ser solo números'];
    }
    
    if (!preg_match('/^[a-zA-ZáéíóúñÑÁÉÍÓÚ0-9\s]+$/u', $nombreLimpio)) {
        return ['valido' => false, 'error' => 'El nombre solo puede contener letras, números y espacios'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar para crear nuevo servicio
 */
function validarCrearServicio($data) {
    return validarServicio($data, false);
}

/**
 * Validar para actualizar servicio existente
 */
function validarActualizarServicio($data) {
    return validarServicio($data, true);
}

/**
 * Validar solo ID
 */
function validarSoloIdServicio($id) {
    $resultado = validarIdRequeridoServicio($id, 'servicio');
    
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