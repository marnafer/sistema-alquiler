<?php
/**
 * Sanitizador para la entidad Usuario
 * SOLO sanitiza los datos, NO valida
 */

/**
 * Sanitiza todos los datos de un usuario
 */
function sanitizarUsuario($data) {
    return [
        'id' => sanitizarIdUsuario($data['id'] ?? null),
        'nombre' => sanitizarNombre($data['nombre'] ?? null),
        'apellido' => sanitizarApellido($data['apellido'] ?? null),
        'email' => sanitizarEmail($data['email'] ?? null),
        'telefono' => sanitizarTelefono($data['telefono'] ?? null),
        'domicilio' => sanitizarDomicilio($data['domicilio'] ?? null),
        'contrasena' => isset($data['contrasena']) ? $data['contrasena'] : null,
        'rol_id' => sanitizarRolId($data['rol_id'] ?? null),
        'deleted_at' => sanitizarFechaEliminacion($data['deleted_at'] ?? null)
    ];
}

/**
 * Sanitizar ID de usuario
 */
function sanitizarIdUsuario($id) {
    if ($id === null || $id === '') {
        return null;
    }
    $idSanitizado = filter_var($id, FILTER_VALIDATE_INT);
    return ($idSanitizado !== false && $idSanitizado > 0) ? $idSanitizado : null;
}

/**
 * Sanitizar nombre
 */
function sanitizarNombre($nombre) {
    if ($nombre === null || $nombre === '') {
        return null;
    }
    
    $nombre = trim($nombre);
    $nombre = preg_replace('/\s+/', ' ', $nombre);
    $nombre = ucwords(strtolower($nombre));
    $nombre = preg_replace('/[^a-zA-ZáéíóúñÑÁÉÍÓÚ\s]/u', '', $nombre);
    $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    
    if (strlen($nombre) > 50) {
        $nombre = substr($nombre, 0, 50);
    }
    
    return $nombre;
}

/**
 * Sanitizar apellido
 */
function sanitizarApellido($apellido) {
    if ($apellido === null || $apellido === '') {
        return null;
    }
    
    $apellido = trim($apellido);
    $apellido = preg_replace('/\s+/', ' ', $apellido);
    $apellido = ucwords(strtolower($apellido));
    $apellido = preg_replace('/[^a-zA-ZáéíóúñÑÁÉÍÓÚ\s]/u', '', $apellido);
    $apellido = htmlspecialchars($apellido, ENT_QUOTES, 'UTF-8');
    
    if (strlen($apellido) > 50) {
        $apellido = substr($apellido, 0, 50);
    }
    
    return $apellido;
}

/**
 * Sanitizar email
 */
function sanitizarEmail($email) {
    if ($email === null || $email === '') {
        return null;
    }
    
    $email = trim($email);
    $email = strtolower($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    
    if (strlen($email) > 100) {
        $email = substr($email, 0, 100);
    }
    
    return $email;
}

/**
 * Sanitizar teléfono
 */
function sanitizarTelefono($telefono) {
    if ($telefono === null || $telefono === '') {
        return null;
    }
    
    // Eliminar todo excepto números, +, -, espacios y paréntesis
    $telefono = preg_replace('/[^0-9+\-\s\(\)]/', '', $telefono);
    $telefono = trim($telefono);
    $telefono = htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8');
    
    if (strlen($telefono) > 25) {
        $telefono = substr($telefono, 0, 25);
    }
    
    return $telefono;
}

/**
 * Sanitizar domicilio
 */
function sanitizarDomicilio($domicilio) {
    if ($domicilio === null || $domicilio === '') {
        return null;
    }
    
    $domicilio = trim($domicilio);
    $domicilio = preg_replace('/\s+/', ' ', $domicilio);
    $domicilio = htmlspecialchars($domicilio, ENT_QUOTES, 'UTF-8');
    
    if (strlen($domicilio) > 100) {
        $domicilio = substr($domicilio, 0, 100);
    }
    
    return $domicilio;
}

/**
 * Sanitizar rol ID
 */
function sanitizarRolId($rolId) {
    if ($rolId === null || $rolId === '') {
        return null;
    }
    $rolIdSanitizado = filter_var($rolId, FILTER_VALIDATE_INT);
    return ($rolIdSanitizado !== false && $rolIdSanitizado > 0) ? $rolIdSanitizado : null;
}

/**
 * Sanitizar fecha de eliminación
 */
function sanitizarFechaEliminacion($fecha) {
    if ($fecha === null || $fecha === '') {
        return null;
    }
    $timestamp = strtotime($fecha);
    return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
}

/**
 * Sanitizar solo email (para login/búsqueda)
 */
function sanitizarSoloEmail($email) {
    return sanitizarEmail($email);
}

/**
 * Sanitizar solo contraseña (para hashear)
 */
function sanitizarSoloContrasena($contrasena) {
    if ($contrasena === null || $contrasena === '') {
        return null;
    }
    return $contrasena; // No se sanitiza más, se va a hashear
namespace App\Sanitizers;

class UsuarioSanitizer {
    /**
     * Limpia los datos de entrada para evitar XSS y ruidos en DB
     */
    public static function sanitizarUsuario(array $datos): array {
        $sanitizados = [];

        $sanitizados['nombre'] = isset($datos['nombre']) 
            ? htmlspecialchars(trim($datos['nombre']), ENT_QUOTES, 'UTF-8') 
            : null;

        $sanitizados['email'] = isset($datos['email']) 
            ? filter_var(trim(strtolower($datos['email'])), FILTER_SANITIZE_EMAIL) 
            : null;

        $sanitizados['password'] = $datos['password'] ?? null; // El password no se sanitiza (se hashea luego)

        $sanitizados['rol_id'] = isset($datos['rol_id']) 
            ? filter_var($datos['rol_id'], FILTER_SANITIZE_NUMBER_INT) 
            : null;

        return $sanitizados;
    }
}