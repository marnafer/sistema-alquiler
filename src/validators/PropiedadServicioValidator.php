<?php
/**
 * Validador para la entidad PropiedadServicio
 * SOLO valida los datos, NO sanitiza
 */

/**
 * Validar todos los datos de una relación propiedad-servicio
 */
function validarPropiedadServicio($data, $requerirId = false) {
    $errores = [];
    
    // Validar ID (solo si se requiere)
    if ($requerirId) {
        $resultado = validarIdRequeridoPropiedadServicio($data['id'] ?? null, 'relación');
        if (!$resultado['valido']) {
            $errores['id'] = $resultado['error'];
        }
    }
    
    // Validar propiedad_id
    $resultado = validarPropiedadIdRequerido($data['propiedad_id'] ?? null);
    if (!$resultado['valido']) {
        $errores['propiedad_id'] = $resultado['error'];
    }
    
    // Validar servicio_id
    $resultado = validarServicioIdRequerido($data['servicio_id'] ?? null);
    if (!$resultado['valido']) {
        $errores['servicio_id'] = $resultado['error'];
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
function validarIdRequeridoPropiedadServicio($id, $campo = '') {
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
 * Validar ID de propiedad requerido
 */
function validarPropiedadIdRequerido($id) {
    if ($id === null || $id === '') {
        return ['valido' => false, 'error' => 'El ID de propiedad es requerido'];
    }
    
    if (!is_numeric($id)) {
        return ['valido' => false, 'error' => 'El ID de propiedad debe ser un número'];
    }
    
    if ($id <= 0) {
        return ['valido' => false, 'error' => 'El ID de propiedad debe ser positivo'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar ID de servicio requerido
 */
function validarServicioIdRequerido($id) {
    if ($id === null || $id === '') {
        return ['valido' => false, 'error' => 'El ID de servicio es requerido'];
    }
    
    if (!is_numeric($id)) {
        return ['valido' => false, 'error' => 'El ID de servicio debe ser un número'];
    }
    
    if ($id <= 0) {
        return ['valido' => false, 'error' => 'El ID de servicio debe ser positivo'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar para crear nueva relación
 */
function validarCrearPropiedadServicio($data) {
    return validarPropiedadServicio($data, false);
}

/**
 * Validar para actualizar relación existente
 */
function validarActualizarPropiedadServicio($data) {
    return validarPropiedadServicio($data, true);
}

/**
 * Validar solo ID
 */
function validarSoloIdPropiedadServicio($id) {
    $resultado = validarIdRequeridoPropiedadServicio($id, 'relación');
    
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
 * Validar existencia de propiedad y servicio (para usar en controlador)
 */
function validarExistenciaPropiedadServicio($propiedadId, $servicioId, $db) {
    // Verificar que la propiedad existe
    $query = "SELECT id FROM propiedades WHERE id = :id AND deleted_at IS NULL";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $propiedadId]);
    
    if ($stmt->rowCount() == 0) {
        return [
            'success' => false,
            'error' => 'La propiedad no existe o fue eliminada'
        ];
    }
    
    // Verificar que el servicio existe
    $query = "SELECT id FROM servicios WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $servicioId]);
    
    if ($stmt->rowCount() == 0) {
        return [
            'success' => false,
            'error' => 'El servicio no existe'
        ];
    }
    
    return [
        'success' => true,
        'error' => null
    ];
}