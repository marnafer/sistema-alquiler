<?php
/**
 * Validador para la entidad Provincia
 * SOLO valida los datos, NO sanitiza
 */

/**
 * Validar todos los datos de una provincia
 */
function validarProvincia($data, $requerirId = false) {
    $errores = [];
    
    // Validar ID (solo si se requiere)
    if ($requerirId) {
        $resultado = validarIdRequerido($data['id'] ?? null, 'provincia');
        if (!$resultado['valido']) {
            $errores['id'] = $resultado['error'];
        }
    }
    
    // Validar nombre
    $resultado = validarNombreProvincia($data['nombre'] ?? null);
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
 * Validar nombre de provincia
 */
function validarNombreProvincia($nombre) {
    if ($nombre === null || $nombre === '') {
        return ['valido' => false, 'error' => 'El nombre de la provincia es requerido'];
    }
    
    $nombreLimpio = trim($nombre);
    $longitud = strlen($nombreLimpio);
    
    if ($longitud < 3) {
        return ['valido' => false, 'error' => 'El nombre debe tener al menos 3 caracteres'];
    }
    
    if ($longitud > 100) {
        return ['valido' => false, 'error' => 'El nombre no puede exceder los 100 caracteres'];
    }
    
    if (is_numeric($nombreLimpio)) {
        return ['valido' => false, 'error' => 'El nombre no puede ser solo números'];
    }
    
    if (!preg_match('/^[a-zA-ZáéíóúñÑÁÉÍÓÚ\s]+$/u', $nombreLimpio)) {
        return ['valido' => false, 'error' => 'El nombre solo puede contener letras y espacios'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar para crear nueva provincia
 */
function validarCrearProvincia($data) {
    return validarProvincia($data, false);
}

/**
 * Validar para actualizar provincia existente
 */
function validarActualizarProvincia($data) {
    return validarProvincia($data, true);
}

/**
 * Validar solo ID
 */
function validarSoloIdProvincia($id) {
    $resultado = validarIdRequerido($id, 'provincia');
    
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