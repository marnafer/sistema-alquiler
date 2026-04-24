<?php

namespace App\Sanitizers;

class UsuarioSanitizer
{
    /**
     * Sanitiza el payload de un usuario.
     * No transforma la contraseña (se debe hashear en el controlador).
     *
     * @param array $data
     * @return array
     */
    public static function sanitizarUsuario(array $data): array
    {
        return [
            'id' => isset($data['id']) ? (int) filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT) : null,
            'nombre' => isset($data['nombre']) ? htmlspecialchars(trim((string)$data['nombre']), ENT_QUOTES, 'UTF-8') : null,
            'apellido' => isset($data['apellido']) ? htmlspecialchars(trim((string)$data['apellido']), ENT_QUOTES, 'UTF-8') : null,
            'email' => isset($data['email']) ? filter_var(trim(strtolower((string)$data['email'])), FILTER_SANITIZE_EMAIL) : null,
            // La contraseña no se altera aquí (se hashea en el controlador)
            'contrasena' => $data['contrasena'] ?? null,
            'rol_id' => isset($data['rol_id']) ? (int) filter_var($data['rol_id'], FILTER_SANITIZE_NUMBER_INT) : null,
            'telefono' => isset($data['telefono']) && $data['telefono'] !== '' 
                ? preg_replace('/[^0-9\+]/', '', (string)$data['telefono']) 
                : null,
            'domicilio' => isset($data['domicilio']) && $data['domicilio'] !== '' 
                ? htmlspecialchars(trim((string)$data['domicilio']), ENT_QUOTES, 'UTF-8') 
                : null,
        ];
    }

    /**
     * Sanitiza un ID (por ejemplo recibido en la URL)
     * @param mixed $id
     * @return int
     */
    public static function sanitizeId($id): int
    {
        return (int) filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    }
}

/**
 * Wrapper procedural por compatibilidad con código existente que llama a sanitizarUsuario()
 */
function sanitizarUsuario(array $data): array
{
    return \App\Sanitizers\UsuarioSanitizer::sanitizarUsuario($data);
}