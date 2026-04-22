<?php
/**
 * Validador para la entidad Reserva (versión simplificada)
 * SOLO valida los datos, NO sanitiza
 */

/**
 * Validar todos los datos de una reserva
 */
function validarReserva($data, $requerirId = false) {
    $errores = [];
    
    // Validar ID (solo si se requiere)
    if ($requerirId) {
        $resultado = validarIdRequerido($data['id'] ?? null);
        if (!$resultado['valido']) {
            $errores['id'] = $resultado['error'];
        }
    }
    
    // Validar propiedad_id
    $resultado = validarIdRequerido($data['propiedad_id'] ?? null, 'propiedad');
    if (!$resultado['valido']) {
        $errores['propiedad_id'] = $resultado['error'];
    }
    
    // Validar inquilino_id
    $resultado = validarIdRequerido($data['inquilino_id'] ?? null, 'inquilino');
    if (!$resultado['valido']) {
        $errores['inquilino_id'] = $resultado['error'];
    }
    
    // Validar fecha_desde
    $resultado = validarFechaDesde($data['fecha_desde'] ?? null);
    if (!$resultado['valido']) {
        $errores['fecha_desde'] = $resultado['error'];
    }
    
    // Validar fecha_hasta
    $resultado = validarFechaHasta($data['fecha_hasta'] ?? null);
    if (!$resultado['valido']) {
        $errores['fecha_hasta'] = $resultado['error'];
    }
    
    // Validar relación entre fechas
    if (!isset($errores['fecha_desde']) && !isset($errores['fecha_hasta'])) {
        $resultado = validarRelacionFechas(
            $data['fecha_desde'] ?? null,
            $data['fecha_hasta'] ?? null
        );
        if (!$resultado['valido']) {
            $errores['fechas'] = $resultado['error'];
        }
    }
    
    // Validar precio_total
    $resultado = validarPrecio($data['precio_total'] ?? null);
    if (!$resultado['valido']) {
        $errores['precio_total'] = $resultado['error'];
    }
    
    // Validar estado (opcional)
    if (isset($data['estado']) && !empty($data['estado'])) {
        $resultado = validarEstado($data['estado']);
        if (!$resultado['valido']) {
            $errores['estado'] = $resultado['error'];
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
 * Validar fecha desde
 */
function validarFechaDesde($fecha) {
    if ($fecha === null || $fecha === '') {
        return ['valido' => false, 'error' => 'La fecha de inicio es requerida'];
    }
    
    $timestamp = strtotime($fecha);
    
    if ($timestamp === false) {
        return ['valido' => false, 'error' => 'La fecha de inicio no es válida'];
    }
    
    $hoy = strtotime(date('Y-m-d'));
    
    if ($timestamp < $hoy) {
        return ['valido' => false, 'error' => 'La fecha de inicio no puede ser anterior a hoy'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar fecha hasta
 */
function validarFechaHasta($fecha) {
    if ($fecha === null || $fecha === '') {
        return ['valido' => false, 'error' => 'La fecha de fin es requerida'];
    }
    
    $timestamp = strtotime($fecha);
    
    if ($timestamp === false) {
        return ['valido' => false, 'error' => 'La fecha de fin no es válida'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar relación entre fechas
 */
function validarRelacionFechas($fechaDesde, $fechaHasta) {
    $timestampDesde = strtotime($fechaDesde);
    $timestampHasta = strtotime($fechaHasta);
    
    if ($timestampHasta <= $timestampDesde) {
        return ['valido' => false, 'error' => 'La fecha de fin debe ser posterior a la fecha de inicio'];
    }
    
    $dias = ($timestampHasta - $timestampDesde) / (60 * 60 * 24);
    
    if ($dias < 1) {
        return ['valido' => false, 'error' => 'La reserva debe ser de al menos 1 día'];
    }
    
    if ($dias > 365) {
        return ['valido' => false, 'error' => 'La reserva no puede exceder los 365 días'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar precio
 */
function validarPrecio($precio) {
    if ($precio === null || $precio === '') {
        return ['valido' => false, 'error' => 'El precio total es requerido'];
    }
    
    if (!is_numeric($precio)) {
        return ['valido' => false, 'error' => 'El precio debe ser un número'];
    }
    
    if ($precio <= 0) {
        return ['valido' => false, 'error' => 'El precio debe ser mayor a 0'];
    }
    
    if ($precio > 999999999.99) {
        return ['valido' => false, 'error' => 'El precio excede el límite permitido'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar estado
 */
function validarEstado($estado) {
    $estadosValidos = ['pendiente', 'confirmada', 'cancelada', 'finalizada'];
    
    $estado = strtolower(trim($estado));
    
    if (!in_array($estado, $estadosValidos)) {
        return [
            'valido' => false,
            'error' => 'Estado inválido. Valores permitidos: ' . implode(', ', $estadosValidos)
        ];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar para crear nueva reserva
 */
function validarCrearReserva($data) {
    return validarReserva($data, false);
}

/**
 * Validar para actualizar reserva existente
 */
function validarActualizarReserva($data) {
    return validarReserva($data, true);
}

/**
 * Validar solo ID
 */
function validarSoloIdReserva($id) {
    $resultado = validarIdRequerido($id, 'reserva');
    
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
 * Validar solo fechas (para disponibilidad)
 */
function validarFechasDisponibilidadReserva($data) {
    $errores = [];
    
    // Validar fecha_desde
    $resultado = validarFechaDesde($data['fecha_desde'] ?? null);
    if (!$resultado['valido']) {
        $errores['fecha_desde'] = $resultado['error'];
    }
    
    // Validar fecha_hasta
    $resultado = validarFechaHasta($data['fecha_hasta'] ?? null);
    if (!$resultado['valido']) {
        $errores['fecha_hasta'] = $resultado['error'];
    }
    
    // Validar relación
    if (!isset($errores['fecha_desde']) && !isset($errores['fecha_hasta'])) {
        $resultado = validarRelacionFechas(
            $data['fecha_desde'] ?? null,
            $data['fecha_hasta'] ?? null
        );
        if (!$resultado['valido']) {
            $errores['fechas'] = $resultado['error'];
        }
    }
    
    if (count($errores) > 0) {
        return [
            'success' => false,
            'message' => 'Error de validación de fechas',
            'errors' => $errores
        ];
    }
    
    return [
        'success' => true,
        'message' => 'Fechas válidas',
        'errors' => null
    ];
}

/**
 * Validar solo estado
 */
function validarSoloEstadoReserva($estado) {
    $resultado = validarEstado($estado);
    
    if (!$resultado['valido']) {
        return [
            'success' => false,
            'message' => 'Estado inválido',
            'errors' => ['estado' => $resultado['error']]
        ];
    }
    
    return [
        'success' => true,
        'message' => 'Estado válido',
        'errors' => null
    ];
}