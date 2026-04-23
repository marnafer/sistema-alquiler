<?php
/**
 * Validador para la entidad Rol
 * SOLO valida los datos, NO sanitiza
 */

/**
 * Validar todos los datos de un rol
 */
function validarRol($data, $requerirId = false) {
    $errores = [];
    
    // Validar ID (solo si se requiere)
    if ($requerirId) {
        $resultado = validarIdRequeridoRol($data['id'] ?? null, 'rol');
        if (!$resultado['valido']) {
            $errores['id'] = $resultado['error'];
        }
    }
    
    // Validar nombre
    $resultado = validarNombreRol($data['nombre'] ?? null);
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
function validarIdRequeridoRol($id, $campo = '') {
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
 * Validar nombre de rol
 */
function validarNombreRol($nombre) {
    if ($nombre === null || $nombre === '') {
        return ['valido' => false, 'error' => 'El nombre del rol es requerido'];
    }
    
    $nombreLimpio = trim($nombre);
    $longitud = strlen($nombreLimpio);
    
    if ($longitud < 3) {
        return ['valido' => false, 'error' => 'El nombre debe tener al menos 3 caracteres'];
    }
    
    if ($longitud > 30) {
        return ['valido' => false, 'error' => 'El nombre no puede exceder los 30 caracteres'];
    }
    
    if (!preg_match('/^[a-zA-ZáéíóúñÁÉÍÓÚ\s]+$/u', $nombreLimpio)) {
        return ['valido' => false, 'error' => 'El nombre solo puede contener letras y espacios'];
    }
    
    // Roles predefinidos recomendados
    $rolesPermitidos = ['admin', 'administrador', 'inquilino', 'propietario', 'usuario'];
    $nombreLower = strtolower($nombreLimpio);
    
    if (!in_array($nombreLower, $rolesPermitidos)) {
        return ['valido' => false, 'error' => 'Rol no permitido. Roles válidos: ' . implode(', ', $rolesPermitidos)];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar para crear nuevo rol
 */
function validarCrearRol($data) {
    return validarRol($data, false);
}

/**
 * Validar para actualizar rol existente
 */
function validarActualizarRol($data) {
    return validarRol($data, true);
}

/**
 * Validar solo ID
 */
function validarSoloIdRol($id) {
    $resultado = validarIdRequeridoRol($id, 'rol');
    
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