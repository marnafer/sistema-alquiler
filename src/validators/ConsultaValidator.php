<?php

/**
 * Validar todos los datos de una consulta
 * @param array $data Datos a validar (ya sanitizados o crudos)
 * @param bool $requerirId Si se requiere ID para actualizaciones
 * @return array Respuesta con errores o éxito
 */
function validarConsulta($data, $requerirId = false) {
    $errores = [];

    // Validaciones
    if ($requerirId) {
        $idValidation = validarConsultaId($data['id'] ?? null);
        if (!$idValidation['success']) {
            $errores['id'] = $idValidation['error'];
        }
    }

    $propiedadValidation = validarPropiedadId($data['propiedad_id'] ?? null);
    if (!$propiedadValidation['success']) {
        $errores['propiedad_id'] = $propiedadValidation['error'];
    }

    $inquilinoValidation = validarInquilinoId($data['inquilino_id'] ?? null);
    if (!$inquilinoValidation['success']) {
        $errores['inquilino_id'] = $inquilinoValidation['error'];
    }

    $mensajeValidation = validarMensajeConsulta($data['mensaje'] ?? null);
    if (!$mensajeValidation['success']) {
        $errores['mensaje'] = $mensajeValidation['error'];
    }

    if (isset($data['fecha_consulta']) && !empty($data['fecha_consulta'])) {
        $fechaValidation = validarFechaConsulta($data['fecha_consulta']);
        if (!$fechaValidation['success']) {
            $errores['fecha_consulta'] = $fechaValidation['error'];
        }
    }

    //  SI HAY ERRORES
    if (count($errores) > 0) {
        return [
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $errores,
            'data' => null // 👈 IMPORTANTE
        ];
    }

    // DATOS LIMPIOS (podés mejorar esto con sanitizer si querés)
    $dataLimpia = [
        'id' => $data['id'] ?? null,
        'propiedad_id' => (int) $data['propiedad_id'],
        'inquilino_id' => (int) $data['inquilino_id'],
        'mensaje' => trim($data['mensaje']),
        'fecha_consulta' => $data['fecha_consulta'] ?? null
    ];

    return [
        'success' => true,
        'message' => 'Validación exitosa',
        'errors' => null,
        'data' => $dataLimpia 
    ];
}

/**
 * Validar ID de consulta 
 * @param mixed $id ID a validar
 * @return array Respuesta con valid y error
 */
function validarConsultaId($id): array {
    if ($id === null || $id === '') {
        return ['success' => false, 'error' => 'El ID es requerido'];
    }

    if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {
        return ['success' => false, 'error' => 'El ID debe ser un entero positivo'];
    }

    return ['success' => true, 'error' => null];
}

/**
 * Validar ID de propiedad
 * @param mixed $id ID a validar
 * @return array Respuesta con valid y error
 */
function validarPropiedadId($id) {
    if ($id === null || $id === '') {
        return ['success' => false, 'error' => 'El ID de propiedad es requerido'];
    }
    
    if (!is_numeric($id)) {
        return ['success' => false, 'error' => 'El ID de propiedad debe ser un número'];
    }
    
    if ($id <= 0) {
        return ['success' => false, 'error' => 'El ID de propiedad debe ser un número positivo'];
    }
    
    if (filter_var($id, FILTER_VALIDATE_INT) === false) {
        return ['success' => false, 'error' => 'El ID de propiedad debe ser un número entero'];
    }
    
    return ['success' => true, 'error' => null];
}

/**
 * Validar ID de inquilino
 * @param mixed $id ID a validar
 * @return array Respuesta con success y error
 */
function validarInquilinoId($id) {
    if ($id === null || $id === '') {
        return ['success' => false, 'error' => 'El ID del inquilino es requerido'];
    }
    
    if (!is_numeric($id)) {
        return ['success' => false, 'error' => 'El ID del inquilino debe ser un número'];
    }
    
    if ($id <= 0) {
        return ['success' => false, 'error' => 'El ID del inquilino debe ser un número positivo'];
    }
    
    if (filter_var($id, FILTER_VALIDATE_INT) === false) {
        return ['success' => false, 'error' => 'El ID del inquilino debe ser un número entero'];
    }
    
    return ['success' => true, 'error' => null];
}

/**
 * Validar mensaje de consulta
 * @param string $mensaje Mensaje a validar
 * @return array Respuesta con valid y error
 */
function validarMensajeConsulta($mensaje) {
    if ($mensaje === null || $mensaje === '') {
        return ['success' => false, 'error' => 'El mensaje es requerido'];
    }
    
    $mensajeLimpio = trim($mensaje);
    
    if (strlen($mensajeLimpio) < 5) {
        return ['success' => false, 'error' => 'El mensaje debe tener al menos 5 caracteres'];
    }
    
    if (strlen($mensajeLimpio) > 5000) {
        return ['success' => false, 'error' => 'El mensaje no puede exceder los 5000 caracteres'];
    }
    
    if (preg_match('/^\s*$/', $mensajeLimpio)) {
        return ['success' => false, 'error' => 'El mensaje no puede estar vacío o contener solo espacios'];
    }
    
    return ['success' => true, 'error' => null];
}

/**
 * Validar fecha de consulta
 * @param string $fecha Fecha a validar
 * @return array Respuesta con valid y error
 */
function validarFechaConsulta($fecha) {
    if ($fecha === null || $fecha === '') {
        return ['success' => true, 'error' => null]; // Fecha opcional
    }
    
    $timestamp = strtotime($fecha);
    
    if ($timestamp === false) {
        return ['success' => false, 'error' => 'La fecha no es válida'];
    }
    
    // Validar que no sea una fecha futura (opcional)
    if ($timestamp > time()) {
        return ['success' => false, 'error' => 'La fecha no puede ser futura'];
    }
    
    return ['success' => true, 'error' => null];
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
