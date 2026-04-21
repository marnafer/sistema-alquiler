<?php

namespace App\Validators;

use App\Models\Usuario;

class UsuarioValidator {
    /**
     * Valida las reglas de negocio para un nuevo usuario
     */
    public static function validarUsuario(array $datos): array {
        $errores = [];

        // 1. Validación del Nombre
        if (empty($datos['nombre'])) {
            $errores['nombre'] = "El nombre es obligatorio.";
        } elseif (strlen($datos['nombre']) < 3) {
            $errores['nombre'] = "El nombre debe tener al menos 3 caracteres.";
        }

        // 2. Validación del Email
        if (empty($datos['email'])) {
            $errores['email'] = "El correo electrónico es obligatorio.";
        } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = "El formato del correo no es válido.";
        } else {
            // Verificar si el email ya existe en la DB (Clave para API profesional)
            $existe = Usuario::where('email', $datos['email'])->first();
            if ($existe) {
                $errores['email'] = "Este correo ya está registrado.";
            }
        }

        // 3. Validación del Password
        if (empty($datos['password'])) {
            $errores['password'] = "La contraseña es obligatoria.";
        } elseif (strlen($datos['password']) < 8) {
            $errores['password'] = "La contraseña debe tener al menos 8 caracteres.";
        }

        // 4. Validación del Rol
        if (empty($datos['rol_id'])) {
            $errores['rol_id'] = "Debe seleccionar un rol.";
        }

        return $errores;
    }
}