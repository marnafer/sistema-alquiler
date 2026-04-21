<?php
/**
 * Sanitizador para la entidad Consultas
 * @param array $data Datos crudos a sanitizar
 * @return array Datos sanitizados
 */
function sanitizarConsulta($data) {
    return [
        'id' => isset($data['id']) ? filter_var($data['id'], FILTER_VALIDATE_INT) : null,
        'propiedad_id' => isset($data['propiedad_id']) ? filter_var($data['propiedad_id'], FILTER_VALIDATE_INT) : null,
        'inquilino_id' => isset($data['inquilino_id']) ? filter_var($data['inquilino_id'], FILTER_VALIDATE_INT) : null,
        'mensaje' => isset($data['mensaje']) ? htmlspecialchars(trim($data['mensaje']), ENT_QUOTES, 'UTF-8') : null,
        'fecha_consulta' => isset($data['fecha_consulta']) ? sanitizarFecha($data['fecha_consulta']) : null
    ];
}

/**
 * Valida y sanitiza una consulta completa
 * @param array $data Datos a validar
 * @return array Respuesta con estado, mensaje y datos sanitizados
 */
function validarConsulta($data) {
    $errores = [];
    $sanitized = sanitizarConsulta($data);
    
    // Validaciones
    if (!$sanitized['propiedad_id'] || $sanitized['propiedad_id'] <= 0) {
        $errores['propiedad_id'] = 'El ID de propiedad es requerido y debe ser un número positivo';
    }
    
    if (!$sanitized['inquilino_id'] || $sanitized['inquilino_id'] <= 0) {
        $errores['inquilino_id'] = 'El ID del inquilino es requerido y debe ser un número positivo';
    }
    
    if (!$sanitized['mensaje'] || strlen($sanitized['mensaje']) < 5) {
        $errores['mensaje'] = 'El mensaje es requerido y debe tener al menos 5 caracteres';
    } elseif (strlen($sanitized['mensaje']) > 5000) {
        $errores['mensaje'] = 'El mensaje no puede exceder los 5000 caracteres';
    }
    
    if (count($errores) > 0) {
        return [
            'success' => false,
            'errors' => $errores,
            'data' => null
        ];
    }
    
    return [
        'success' => true,
        'errors' => null,
        'data' => $sanitized
    ];
}

// Función auxiliar para sanitizar fechas
function sanitizarFecha($fecha) {
    $timestamp = strtotime($fecha);
    return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
}

// Ejemplo de uso y salida JSON
header('Content-Type: application/json');
$consultaData = $_POST; // o $_GET según corresponda
$resultado = validarConsulta($consultaData);
echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>