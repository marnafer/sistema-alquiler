<?php
/**
 * Validador para la entidad LogActividad
 * SOLO valida los datos, NO sanitiza
 */

/**
 * Validar todos los datos de un log de actividad
 */
function validarLogActividad($data, $requerirId = false) {
    $errores = [];
    
    // Validar ID (solo si se requiere)
    if ($requerirId) {
        $resultado = validarIdRequeridoLog($data['id'] ?? null, 'log');
        if (!$resultado['valido']) {
            $errores['id'] = $resultado['error'];
        }
    }
    
    // Validar usuario_id (opcional, puede ser null para acciones anónimas)
    if (isset($data['usuario_id']) && !empty($data['usuario_id'])) {
        $resultado = validarUsuarioIdLog($data['usuario_id']);
        if (!$resultado['valido']) {
            $errores['usuario_id'] = $resultado['error'];
        }
    }
    
    // Validar acción
    $resultado = validarAccionLog($data['accion'] ?? null);
    if (!$resultado['valido']) {
        $errores['accion'] = $resultado['error'];
    }
    
    // Validar IP (opcional)
    if (isset($data['ip_address']) && !empty($data['ip_address'])) {
        $resultado = validarIpAddressLog($data['ip_address']);
        if (!$resultado['valido']) {
            $errores['ip_address'] = $resultado['error'];
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
function validarIdRequeridoLog($id, $campo = '') {
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
 * Validar ID de usuario
 */
function validarUsuarioIdLog($id) {
    if ($id === null || $id === '') {
        return ['valido' => true, 'error' => null];
    }
    
    if (!is_numeric($id)) {
        return ['valido' => false, 'error' => 'El ID de usuario debe ser un número'];
    }
    
    if ($id <= 0) {
        return ['valido' => false, 'error' => 'El ID de usuario debe ser positivo'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar acción
 */
function validarAccionLog($accion) {
    if ($accion === null || $accion === '') {
        return ['valido' => false, 'error' => 'La acción es requerida'];
    }
    
    $accionLimpia = trim($accion);
    $longitud = strlen($accionLimpia);
    
    if ($longitud < 3) {
        return ['valido' => false, 'error' => 'La acción debe tener al menos 3 caracteres'];
    }
    
    if ($longitud > 255) {
        return ['valido' => false, 'error' => 'La acción no puede exceder los 255 caracteres'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar dirección IP
 */
function validarIpAddressLog($ip) {
    if ($ip === null || $ip === '') {
        return ['valido' => true, 'error' => null];
    }
    
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return ['valido' => false, 'error' => 'La dirección IP no es válida'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar para crear nuevo log
 */
function validarCrearLogActividad($data) {
    return validarLogActividad($data, false);
}

/**
 * Validar solo ID
 */
function validarSoloIdLogActividad($id) {
    $resultado = validarIdRequeridoLog($id, 'log');
    
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