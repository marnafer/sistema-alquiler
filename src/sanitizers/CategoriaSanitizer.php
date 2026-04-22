<?php
/**
 * Sanitizador para la entidad Categoría
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
 * Sanitiza y capitaliza el nombre de la categoría
 * @param string $nombre Nombre crudo
 * @return string Nombre sanitizado
 */
function sanitizarNombreCategoria($nombre) {
    // Eliminar espacios extras y convertir a mayúsculas iniciales
    $nombre = trim($nombre);
    $nombre = preg_replace('/\s+/', ' ', $nombre); // Múltiples espacios a uno
    $nombre = ucwords(strtolower($nombre)); // Capitalizar cada palabra
    return htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
}

/**
 * Valida y sanitiza una categoría completa
 * @param array $data Datos a validar
 * @param bool $requerirId Si se requiere el ID para actualizaciones
 * @return array Respuesta con estado, mensaje y datos sanitizados
 */
function validarCategoria($data, $requerirId = false) {
    $errores = [];
    $sanitized = sanitizarCategoria($data);
    
    // Validación de ID (solo si se requiere)
    if ($requerirId && (!$sanitized['id'] || $sanitized['id'] <= 0)) {
        $errores['id'] = 'El ID de categoría es requerido y debe ser un número positivo';
    }
    
    // Validación del nombre
    if (!$sanitized['nombre']) {
        $errores['nombre'] = 'El nombre de la categoría es requerido';
    } elseif (strlen($sanitized['nombre']) < 3) {
        $errores['nombre'] = 'El nombre debe tener al menos 3 caracteres';
    } elseif (strlen($sanitized['nombre']) > 50) {
        $errores['nombre'] = 'El nombre no puede exceder los 50 caracteres';
    } elseif (!preg_match('/^[a-zA-ZáéíóúñÑÁÉÍÓÚ\s]+$/', $sanitized['nombre'])) {
        $errores['nombre'] = 'El nombre solo puede contener letras y espacios';
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

// Ejemplo de uso y salida JSON
header('Content-Type: application/json');
$categoriaData = $_POST;
$resultado = validarCategoria($categoriaData);
echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>