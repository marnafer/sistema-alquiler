<?php

use App\Models\Usuario;

/**
 * Valida los datos del usuario
 * @param array $datos Datos ya sanitizados
 * @return array Lista de errores (vacía si todo está OK)
 */
function validarUsuario(array $datos): array {
    $errores = [];

    // Validar Nombre y Apellido
    if (empty($datos['nombre']) || strlen($datos['nombre']) < 2) {
        $errores['nombre'] = "El nombre es obligatorio y debe tener al menos 2 caracteres.";
    }
    if (empty($datos['apellido']) || strlen($datos['apellido']) < 2) {
        $errores['apellido'] = "El apellido es obligatorio.";
    }

    // Validar Email
    if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = "El formato del correo electrónico no es válido.";
    } else {
        // REGLA CLAVE: Verificar si el email ya existe en la DB usando Eloquent
        $existe = Usuario::where('email', $datos['email'])->first();
        if ($existe) {
            $errores['email'] = "Este correo electrónico ya está registrado.";
        }
    }

    // Validar Teléfono (Mínimo de números para Argentina)
    if (empty($datos['telefono']) || strlen($datos['telefono']) < 8) {
        $errores['telefono'] = "Ingrese un número de teléfono válido.";
    }

    // Validar Contraseña 
    if (empty($datos['contrasena']) || strlen($datos['contrasena']) < 8) {
        $errores['contrasena'] = "La contraseña debe tener al menos 8 caracteres.";
    }

    // Validar Rol
    if (empty($datos['rol_id']) || !is_numeric($datos['rol_id'])) {
        $errores['rol_id'] = "Debe seleccionar un rol válido.";
    }

    return $errores;
}