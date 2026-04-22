<?php

/**
 * Validar todos los datos de una consulta
 * @param array $data Datos a validar (ya sanitizados o crudos)
 * @param bool $requerirId Si se requiere ID para actualizaciones
 * @return array Respuesta con errores o éxito
 */
function validarConsulta($data, $requerirId = false) {
    $errores = [];
    
    // 1. Validar ID (solo si se requiere)
    if ($requerirId) {
        $idValidation = validarIdConsultaRequerido($data['id'] ?? null);
        if (!$idValidation['valid']) {
            $errores['id'] = $idValidation['error'];
        }
    }
    
    // 2. Validar propiedad_id
    $propiedadValidation = validarPropiedadId($data['propiedad_id'] ?? null);
    if (!$propiedadValidation['valid']) {
        $errores['propiedad_id'] = $propiedadValidation['error'];
    }
    
    // 3. Validar inquilino_id
    $inquilinoValidation = validarInquilinoId($data['inquilino_id'] ?? null);
    if (!$inquilinoValidation['valid']) {
        $errores['inquilino_id'] = $inquilinoValidation['error'];
    }
    
    // 4. Validar mensaje
    $mensajeValidation = validarMensajeConsulta($data['mensaje'] ?? null);
    if (!$mensajeValidation['valid']) {
        $errores['mensaje'] = $mensajeValidation['error'];
    }
    
    // 5. Validar fecha (opcional)
    if (isset($data['fecha_consulta']) && !empty($data['fecha_consulta'])) {
        $fechaValidation = validarFechaConsulta($data['fecha_consulta']);
        if (!$fechaValidation['valid']) {
            $errores['fecha_consulta'] = $fechaValidation['error'];
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
 * Validar ID de consulta (requerido)
 * @param mixed $id ID a validar
 * @return array Respuesta con valid y error
 */
function validarIdConsultaRequerido($id) {
    if ($id === null || $id === '') {
        return ['valid' => false, 'error' => 'El ID de consulta es requerido'];
    }
    
    if (!is_numeric($id)) {
        return ['valid' => false, 'error' => 'El ID de consulta debe ser un número'];
    }
    
    if ($id <= 0) {
        return ['valid' => false, 'error' => 'El ID de consulta debe ser un número positivo'];
    }
    
    if (filter_var($id, FILTER_VALIDATE_INT) === false) {
        return ['valid' => false, 'error' => 'El ID de consulta debe ser un número entero'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validar ID de propiedad
 * @param mixed $id ID a validar
 * @return array Respuesta con valid y error
 */
function validarPropiedadId($id) {
    if ($id === null || $id === '') {
        return ['valid' => false, 'error' => 'El ID de propiedad es requerido'];
    }
    
    if (!is_numeric($id)) {
        return ['valid' => false, 'error' => 'El ID de propiedad debe ser un número'];
    }
    
    if ($id <= 0) {
        return ['valid' => false, 'error' => 'El ID de propiedad debe ser un número positivo'];
    }
    
    if (filter_var($id, FILTER_VALIDATE_INT) === false) {
        return ['valid' => false, 'error' => 'El ID de propiedad debe ser un número entero'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validar ID de inquilino
 * @param mixed $id ID a validar
 * @return array Respuesta con valid y error
 */
function validarInquilinoId($id) {
    if ($id === null || $id === '') {
        return ['valid' => false, 'error' => 'El ID del inquilino es requerido'];
    }
    
    if (!is_numeric($id)) {
        return ['valid' => false, 'error' => 'El ID del inquilino debe ser un número'];
    }
    
    if ($id <= 0) {
        return ['valid' => false, 'error' => 'El ID del inquilino debe ser un número positivo'];
    }
    
    if (filter_var($id, FILTER_VALIDATE_INT) === false) {
        return ['valid' => false, 'error' => 'El ID del inquilino debe ser un número entero'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validar mensaje de consulta
 * @param string $mensaje Mensaje a validar
 * @return array Respuesta con valid y error
 */
function validarMensajeConsulta($mensaje) {
    if ($mensaje === null || $mensaje === '') {
        return ['valid' => false, 'error' => 'El mensaje es requerido'];
    }
    
    $mensajeLimpio = trim($mensaje);
    
    if (strlen($mensajeLimpio) < 5) {
        return ['valid' => false, 'error' => 'El mensaje debe tener al menos 5 caracteres'];
    }
    
    if (strlen($mensajeLimpio) > 5000) {
        return ['valid' => false, 'error' => 'El mensaje no puede exceder los 5000 caracteres'];
    }
    
    if (preg_match('/^\s*$/', $mensajeLimpio)) {
        return ['valid' => false, 'error' => 'El mensaje no puede estar vacío o contener solo espacios'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validar fecha de consulta
 * @param string $fecha Fecha a validar
 * @return array Respuesta con valid y error
 */
function validarFechaConsulta($fecha) {
    if ($fecha === null || $fecha === '') {
        return ['valid' => true, 'error' => null]; // Fecha opcional
    }
    
    $timestamp = strtotime($fecha);
    
    if ($timestamp === false) {
        return ['valid' => false, 'error' => 'La fecha no es válida'];
    }
    
    // Validar que no sea una fecha futura (opcional)
    if ($timestamp > time()) {
        return ['valid' => false, 'error' => 'La fecha no puede ser futura'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validar solo para crear nueva consulta
 * @param array $data Datos a validar
 * @return array Respuesta con errores o éxito
 */
function validarCrearConsulta($data) {
    return validarConsulta($data, false);
}

/**
 * Validar solo para actualizar consulta existente
 * @param array $data Datos a validar (debe incluir id)
 * @return array Respuesta con errores o éxito
 */
function validarActualizarConsulta($data) {
    return validarConsulta($data, true);
}

/**
 * Validar solo el ID para operaciones de eliminación/búsqueda
 * @param mixed $id ID a validar
 * @return array Respuesta con errores o éxito
 */
function validarSoloIdConsulta($id) {
    $validation = validarIdConsultaRequerido($id);
    
    if (!$validation['valid']) {
        return [
            'success' => false,
            'message' => 'ID inválido',
            'errors' => ['id' => $validation['error']]
        ];
    }
    
    return [
        'success' => true,
        'message' => 'ID válido',
        'errors' => null
    ];
}