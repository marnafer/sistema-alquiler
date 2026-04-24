<?php

namespace App\Validators;

use App\Models\Usuario;

class UsuarioValidator
{
    /**
     * Valida el payload de usuario.
     * Retorna array con keys: success (bool), message (string), errors (array|null)
     *
     * @param array $data
     * @param bool $requerirId
     * @param bool $requerirContrasena
     * @return array
     */
    public static function validarUsuario(array $data, bool $requerirId = false, bool $requerirContrasena = true): array
    {
        $errores = [];

        // ID requerido (para updates)
        if ($requerirId) {
            if (!isset($data['id']) || !is_numeric($data['id']) || (int)$data['id'] <= 0) {
                $errores['id'] = 'ID inválido o requerido.';
            }
        }

        // Nombre
        $nombre = $data['nombre'] ?? null;
        if (empty($nombre)) {
            $errores['nombre'] = 'El nombre es requerido.';
        } else {
            $nombre = trim((string)$nombre);
            $len = mb_strlen($nombre);
            if ($len < 2) $errores['nombre'] = 'El nombre debe tener al menos 2 caracteres.';
            elseif ($len > 50) $errores['nombre'] = 'El nombre no puede exceder 50 caracteres.';
            elseif (!preg_match('/^[\p{L}\s]+$/u', $nombre)) $errores['nombre'] = 'El nombre solo puede contener letras y espacios.';
        }

        // Apellido
        $apellido = $data['apellido'] ?? null;
        if (empty($apellido)) {
            $errores['apellido'] = 'El apellido es requerido.';
        } else {
            $apellido = trim((string)$apellido);
            $len = mb_strlen($apellido);
            if ($len < 2) $errores['apellido'] = 'El apellido debe tener al menos 2 caracteres.';
            elseif ($len > 50) $errores['apellido'] = 'El apellido no puede exceder 50 caracteres.';
            elseif (!preg_match('/^[\p{L}\s]+$/u', $apellido)) $errores['apellido'] = 'El apellido solo puede contener letras y espacios.';
        }

        // Email
        $email = $data['email'] ?? null;
        if (empty($email)) {
            $errores['email'] = 'El email es requerido.';
        } else {
            $email = trim((string)$email);
            if (mb_strlen($email) > 100) {
                $errores['email'] = 'El email no puede exceder 100 caracteres.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores['email'] = 'El email no es válido.';
            }
        }

        // Teléfono (opcional)
        if (isset($data['telefono']) && $data['telefono'] !== '') {
            $telefono = preg_replace('/[^0-9\+]/', '', (string)$data['telefono']);
            if (mb_strlen($telefono) < 6) $errores['telefono'] = 'El teléfono debe tener al menos 6 dígitos.';
            elseif (mb_strlen($telefono) > 15) $errores['telefono'] = 'El teléfono no puede exceder 15 dígitos.';
        }

        // Domicilio (opcional)
        if (isset($data['domicilio']) && $data['domicilio'] !== '') {
            $dom = trim((string)$data['domicilio']);
            $len = mb_strlen($dom);
            if ($len < 5) $errores['domicilio'] = 'El domicilio debe tener al menos 5 caracteres.';
            elseif ($len > 100) $errores['domicilio'] = 'El domicilio no puede exceder 100 caracteres.';
        }

        // Contraseña
        $contrasena = $data['contrasena'] ?? null;
        if ($requerirContrasena) {
            if (empty($contrasena)) {
                $errores['contrasena'] = 'La contraseña es requerida.';
            } else {
                $len = mb_strlen((string)$contrasena);
                if ($len < 6) $errores['contrasena'] = 'La contraseña debe tener al menos 6 caracteres.';
                elseif ($len > 255) $errores['contrasena'] = 'La contraseña no puede exceder 255 caracteres.';
            }
        } elseif (!empty($contrasena)) {
            $len = mb_strlen((string)$contrasena);
            if ($len < 6) $errores['contrasena'] = 'La contraseña debe tener al menos 6 caracteres.';
            elseif ($len > 255) $errores['contrasena'] = 'La contraseña no puede exceder 255 caracteres.';
        }

        // rol_id
        if (!isset($data['rol_id']) || $data['rol_id'] === '' || !is_numeric($data['rol_id'])) {
            $errores['rol_id'] = 'El rol es requerido y debe ser numérico.';
        } else {
            $rolesValidos = [1, 2]; // Ajustar si la BD tiene otros roles
            if (!in_array((int)$data['rol_id'], $rolesValidos, true)) {
                $errores['rol_id'] = 'Rol inválido.';
            }
        }

        // Validaciones de unicidad / existencia en BD (solo si email presente y no hay errores previos en email)
        if (empty($errores['email']) && !empty($email)) {
            try {
                $query = Usuario::where('email', $email);
                // Si es update con id, excluir ese registro
                if (!empty($data['id']) && is_numeric($data['id'])) {
                    $query->where('id', '!=', (int)$data['id']);
                }
                $existe = $query->first();
                if ($existe) {
                    $errores['email'] = 'Este correo ya está registrado.';
                }
            } catch (\Throwable $e) {
                // No fallamos la validación por error DB aquí; dejar que el controlador capture excepciones si ocurre
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

    /**
     * Validar solo ID
     */
    public static function validarSoloIdUsuario($id): array
    {
        if (!isset($id) || !is_numeric($id) || (int)$id <= 0) {
            return [
                'success' => false,
                'message' => 'ID inválido',
                'errors' => ['id' => 'ID inválido o requerido']
            ];
        }

        return ['success' => true, 'message' => 'ID válido', 'errors' => null];
    }

    /**
     * Validar email para login
     */
    public static function validarEmailLogin($email): array
    {
        $resultado = self::validarUsuario(['email' => $email], false, false);
        if (!$resultado['success']) {
            return [
                'success' => false,
                'message' => 'Email inválido',
                'errors' => ['email' => $resultado['errors']['email'] ?? 'Email inválido']
            ];
        }
        return ['success' => true, 'message' => 'Email válido', 'errors' => null];
    }
}

/**
 * Wrappers procedurales para compatibilidad con código existente
 */
function validarUsuario($data, $requerirId = false, $requerirContrasena = true)
{
    return \App\Validators\UsuarioValidator::validarUsuario($data, $requerirId, $requerirContrasena);
}

function validarCrearUsuario($data)
{
    return \App\Validators\UsuarioValidator::validarUsuario($data, false, true);
}

function validarActualizarUsuario($data, $requerirContrasena = false)
{
    return \App\Validators\UsuarioValidator::validarUsuario($data, true, $requerirContrasena);
}

function validarSoloIdUsuario($id)
{
    return \App\Validators\UsuarioValidator::validarSoloIdUsuario($id);
}

function validarEmailLogin($email)
{
    return \App\Validators\UsuarioValidator::validarEmailLogin($email);
}