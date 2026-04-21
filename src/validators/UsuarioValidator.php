<?php
/**
 * Validador para la entidad Usuario
 * SOLO valida los datos, NO sanitiza
 */

/**
 * Validar todos los datos de un usuario
 */
function validarUsuario($data, $requerirId = false, $requerirContrasena = true) {
    $errores = [];
    
    // Validar ID (solo si se requiere)
    if ($requerirId) {
        $resultado = validarIdRequerido($data['id'] ?? null, 'usuario');
        if (!$resultado['valido']) {
            $errores['id'] = $resultado['error'];
        }
    }
    
    // Validar nombre
    $resultado = validarNombre($data['nombre'] ?? null);
    if (!$resultado['valido']) {
        $errores['nombre'] = $resultado['error'];
    }
    
    // Validar apellido
    $resultado = validarApellido($data['apellido'] ?? null);
    if (!$resultado['valido']) {
        $errores['apellido'] = $resultado['error'];
    }
    
    // Validar email
    $resultado = validarEmail($data['email'] ?? null);
    if (!$resultado['valido']) {
        $errores['email'] = $resultado['error'];
    }
    
    // Validar teléfono (opcional)
    if (isset($data['telefono']) && !empty($data['telefono'])) {
        $resultado = validarTelefono($data['telefono']);
        if (!$resultado['valido']) {
            $errores['telefono'] = $resultado['error'];
        }
    }
    
    // Validar domicilio (opcional)
    if (isset($data['domicilio']) && !empty($data['domicilio'])) {
        $resultado = validarDomicilio($data['domicilio']);
        if (!$resultado['valido']) {
            $errores['domicilio'] = $resultado['error'];
        }
    }
    
    // Validar contraseña (solo para creación)
    if ($requerirContrasena) {
        $resultado = validarContrasena($data['contrasena'] ?? null);
        if (!$resultado['valido']) {
            $errores['contrasena'] = $resultado['error'];
        }
    }
    
    // Validar rol_id
    $resultado = validarRolId($data['rol_id'] ?? null);
    if (!$resultado['valido']) {
        $errores['rol_id'] = $resultado['error'];
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
 * Validar nombre
 */
function validarNombre($nombre) {
    if ($nombre === null || $nombre === '') {
        return ['valido' => false, 'error' => 'El nombre es requerido'];
    }
    
    $nombreLimpio = trim($nombre);
    $longitud = strlen($nombreLimpio);
    
    if ($longitud < 2) {
        return ['valido' => false, 'error' => 'El nombre debe tener al menos 2 caracteres'];
    }
    
    if ($longitud > 50) {
        return ['valido' => false, 'error' => 'El nombre no puede exceder los 50 caracteres'];
    }
    
    if (!preg_match('/^[a-zA-ZáéíóúñÑÁÉÍÓÚ\s]+$/u', $nombreLimpio)) {
        return ['valido' => false, 'error' => 'El nombre solo puede contener letras y espacios'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar apellido
 */
function validarApellido($apellido) {
    if ($apellido === null || $apellido === '') {
        return ['valido' => false, 'error' => 'El apellido es requerido'];
    }
    
    $apellidoLimpio = trim($apellido);
    $longitud = strlen($apellidoLimpio);
    
    if ($longitud < 2) {
        return ['valido' => false, 'error' => 'El apellido debe tener al menos 2 caracteres'];
    }
    
    if ($longitud > 50) {
        return ['valido' => false, 'error' => 'El apellido no puede exceder los 50 caracteres'];
    }
    
    if (!preg_match('/^[a-zA-ZáéíóúñÑÁÉÍÓÚ\s]+$/u', $apellidoLimpio)) {
        return ['valido' => false, 'error' => 'El apellido solo puede contener letras y espacios'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar email
 */
function validarEmail($email) {
    if ($email === null || $email === '') {
        return ['valido' => false, 'error' => 'El email es requerido'];
    }
    
    $emailLimpio = trim($email);
    
    if (strlen($emailLimpio) > 100) {
        return ['valido' => false, 'error' => 'El email no puede exceder los 100 caracteres'];
    }
    
    if (!filter_var($emailLimpio, FILTER_VALIDATE_EMAIL)) {
        return ['valido' => false, 'error' => 'El email no es válido'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar teléfono
 */
function validarTelefono($telefono) {
    if ($telefono === null || $telefono === '') {
        return ['valido' => true, 'error' => null]; // Opcional
    }
    
    $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);
    
    if (strlen($telefonoLimpio) < 6) {
        return ['valido' => false, 'error' => 'El teléfono debe tener al menos 6 dígitos'];
    }
    
    if (strlen($telefonoLimpio) > 15) {
        return ['valido' => false, 'error' => 'El teléfono no puede exceder los 15 dígitos'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar domicilio
 */
function validarDomicilio($domicilio) {
    if ($domicilio === null || $domicilio === '') {
        return ['valido' => true, 'error' => null]; // Opcional
    }
    
    $domicilioLimpio = trim($domicilio);
    $longitud = strlen($domicilioLimpio);
    
    if ($longitud < 5) {
        return ['valido' => false, 'error' => 'El domicilio debe tener al menos 5 caracteres'];
    }
    
    if ($longitud > 100) {
        return ['valido' => false, 'error' => 'El domicilio no puede exceder los 100 caracteres'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar contraseña
 */
function validarContrasena($contrasena) {
    if ($contrasena === null || $contrasena === '') {
        return ['valido' => false, 'error' => 'La contraseña es requerida'];
    }
    
    $longitud = strlen($contrasena);
    
    if ($longitud < 6) {
        return ['valido' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres'];
    }
    
    if ($longitud > 255) {
        return ['valido' => false, 'error' => 'La contraseña no puede exceder los 255 caracteres'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar rol ID
 */
function validarRolId($rolId) {
    if ($rolId === null || $rolId === '') {
        return ['valido' => false, 'error' => 'El rol es requerido'];
    }
    
    if (!is_numeric($rolId)) {
        return ['valido' => false, 'error' => 'El rol debe ser un número'];
    }
    
    $rolesValidos = [1, 2]; // 1: admin, 2: inquilino (ajustar según tu BD)
    if (!in_array($rolId, $rolesValidos)) {
        return ['valido' => false, 'error' => 'Rol inválido'];
    }
    
    return ['valido' => true, 'error' => null];
}

/**
 * Validar para crear nuevo usuario
 */
function validarCrearUsuario($data) {
    return validarUsuario($data, false, true);
}

/**
 * Validar para actualizar usuario existente
 */
function validarActualizarUsuario($data, $requerirContrasena = false) {
    return validarUsuario($data, true, $requerirContrasena);
}

/**
 * Validar solo ID
 */
function validarSoloIdUsuario($id) {
    $resultado = validarIdRequerido($id, 'usuario');
    
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
 * Validar solo email (para login)
 */
function validarEmailLogin($email) {
    $resultado = validarEmail($email);
    
    if (!$resultado['valido']) {
        return [
            'success' => false,
            'message' => 'Email inválido',
            'errors' => ['email' => $resultado['error']]
        ];
    }
    
    return [
        'success' => true,
        'message' => 'Email válido',
        'errors' => null
    ];
}