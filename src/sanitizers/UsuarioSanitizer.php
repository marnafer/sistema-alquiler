<?php

/**
 * Limpia los datos de entrada del usuario
 * @param array $input Datos provenientes de $_POST
 * @return array Datos limpios
 */
function sanitizarUsuario(array $input): array {
    $limpio = [];

    $limpio['nombre']    = isset($input['nombre']) ? filter_var(trim($input['nombre']), FILTER_SANITIZE_SPECIAL_CHARS) : '';
    $limpio['apellido']  = isset($input['apellido']) ? filter_var(trim($input['apellido']), FILTER_SANITIZE_SPECIAL_CHARS) : '';
    $limpio['email']     = isset($input['email']) ? filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL) : '';
    $limpio['telefono']  = isset($input['telefono']) ? filter_var(trim($input['telefono']), FILTER_SANITIZE_NUMBER_INT) : '';
    $limpio['domicilio'] = isset($input['domicilio']) ? filter_var(trim($input['domicilio']), FILTER_SANITIZE_SPECIAL_CHARS) : '';
    
    // La contraseña NO se sanitiza con SPECIAL_CHARS porque podría alterar caracteres especiales válidos
    $limpio['contrasena'] = $input['contrasena'] ?? '';
    
    $limpio['rol_id']    = isset($input['rol_id']) ? (int)$input['rol_id'] : null;

    return $limpio;
}