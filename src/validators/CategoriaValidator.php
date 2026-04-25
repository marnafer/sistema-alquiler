<?php
/**
 * Validador para la entidad Categoría
 * SOLO valida los datos, NO sanitiza
 */

function validarCategoria($data, $requerirId = false) {
    $errores = [];
    
    // Validar ID (solo si se requiere)
    if ($requerirId) {
        if (!isset($data['id']) || empty($data['id'])) {
            $errores['id'] = 'El ID de categoría es requerido';
        } elseif (!is_numeric($data['id']) || $data['id'] <= 0) {
            $errores['id'] = 'El ID de categoría debe ser un número positivo';
        }
    }
    
    // Validar nombre
    if (!isset($data['nombre']) || empty(trim($data['nombre']))) {
        $errores['nombre'] = 'El nombre de la categoría es requerido';
    } else {
        $nombreLimpio = trim($data['nombre']);
        $longitud = strlen($nombreLimpio);
        
        if ($longitud < 3) {
            $errores['nombre'] = 'El nombre debe tener al menos 3 caracteres';
        } elseif ($longitud > 50) {
            $errores['nombre'] = 'El nombre no puede exceder los 50 caracteres';
        } elseif (!preg_match('/^[a-zA-ZáéíóúñÑÁÉÍÓÚ\s]+$/u', $nombreLimpio)) {
            $errores['nombre'] = 'El nombre solo puede contener letras y espacios';
        }
    }
    
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

function validarCrearCategoria($data) {
    return validarCategoria($data, false);
}

function validarActualizarCategoria($data) {
    return validarCategoria($data, true);
}