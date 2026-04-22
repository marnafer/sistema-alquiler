<?php
/**
 * Validador y sanitizador para la entidad Categoría
 * TODAS las funciones devuelven JSON directamente
 */

/**
 * Sanitiza los datos de una categoría
 * @param array $data Datos crudos a sanitizar
 * @return array Datos sanitizados
 */
function sanitizarCategoria($data) {
    return [
        'id' => isset($data['id']) ? filter_var($data['id'], FILTER_VALIDATE_INT) : null,
        'nombre' => isset($data['nombre']) ? sanitizarNombreCategoria($data['nombre']) : null
    ];
}

/**
 * Sanitiza y formatea el nombre de la categoría
 * @param string $nombre Nombre crudo
 * @return string Nombre sanitizado
 */
function sanitizarNombreCategoria($nombre) {
    $nombre = trim($nombre);
    $nombre = preg_replace('/\s+/', ' ', $nombre);
    $nombre = ucwords(strtolower($nombre));
    $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    $nombre = preg_replace('/[^a-zA-ZáéíóúñÑÁÉÍÓÚ0-9\s\-\.]/u', '', $nombre);
    return $nombre;
}

/**
 * Valida y sanitiza una categoría completa (devuelve JSON)
 * @param array $data Datos a validar
 * @param bool $requerirId Si se requiere el ID para actualizaciones
 */
function validarCategoria($data, $requerirId = false) {
    $errores = [];
    $sanitized = sanitizarCategoria($data);
    
    // Validación de ID
    if ($requerirId) {
        if (!isset($data['id']) || empty($data['id'])) {
            $errores['id'] = 'El ID de categoría es requerido';
        } elseif (!is_numeric($data['id']) || $data['id'] <= 0) {
            $errores['id'] = 'El ID de categoría debe ser un número positivo';
        } elseif (filter_var($data['id'], FILTER_VALIDATE_INT) === false) {
            $errores['id'] = 'El ID de categoría debe ser un número entero válido';
        }
    }
    
    // Validación del nombre
    if (!isset($data['nombre']) || empty(trim($data['nombre']))) {
        $errores['nombre'] = 'El nombre de la categoría es requerido';
    } else {
        $nombreLimpio = trim($data['nombre']);
        $longitud = strlen($nombreLimpio);
        
        if ($longitud < 3) {
            $errores['nombre'] = 'El nombre debe tener al menos 3 caracteres';
        } elseif ($longitud > 50) {
            $errores['nombre'] = 'El nombre no puede exceder los 50 caracteres';
        } elseif (is_numeric($nombreLimpio)) {
            $errores['nombre'] = 'El nombre no puede ser solo números';
        } elseif (!preg_match('/^[a-zA-ZáéíóúñÑÁÉÍÓÚ0-9\s\-\.]+$/u', $nombreLimpio)) {
            $errores['nombre'] = 'El nombre solo puede contener letras, números, espacios, guiones y puntos';
        }
    }
    
    // Devolver JSON directamente
    header('Content-Type: application/json');
    
    if (count($errores) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $errores,
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        return false;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Validación exitosa',
        'errors' => null,
        'data' => $sanitized
    ], JSON_UNESCAPED_UNICODE);
    return true;
}

/**
 * Valida para crear nueva categoría (devuelve JSON)
 */
function validarCrearCategoria($data) {
    return validarCategoria($data, false);
}

/**
 * Valida para actualizar categoría existente (devuelve JSON)
 */
function validarActualizarCategoria($data) {
    return validarCategoria($data, true);
}

/**
 * Valida el ID de categoría (devuelve JSON)
 */
function validarIdCategoria($id) {
    header('Content-Type: application/json');
    
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    
    if ($idSanitizado === false || $idSanitizado <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de categoría inválido',
            'errors' => ['id' => 'El ID debe ser un número entero positivo'],
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        return false;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'ID válido',
        'errors' => null,
        'data' => ['id' => $idSanitizado]
    ], JSON_UNESCAPED_UNICODE);
    return true;
}