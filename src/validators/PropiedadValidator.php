<?php

// Importamos los modelos para verificar la existencia en la DB
use App\Models\Categoria;
use App\Models\Localidad;
use App\Models\Usuario;

/**
 * Validador robusto para la tabla PROPIEDADES
 * @param array $data Datos ya sanitizados
 * @return array Lista de errores (vacía si todo está ok)
 */

function validarPropiedad(array $data): array {
    $errores = [];

    // 1. VALIDACIÓN DE TEXTOS (Límites de VARCHAR en SQL)
    if (empty($data['titulo'])) {
        $errores['titulo'] = "El título es obligatorio.";
    } elseif (strlen($data['titulo']) > 150) {
        $errores['titulo'] = "El título no puede superar los 150 caracteres.";
    }

    if (empty($data['direccion'])) {
        $errores['direccion'] = "La dirección exacta es obligatoria.";
    } elseif (strlen($data['direccion']) > 125) {
        $errores['direccion'] = "La dirección es demasiado larga (máximo 125 caracteres).";
    }

    // 2. VALIDACIÓN DE NÚMEROS Y TIPOS (Lógica de Negocio)
    if (!is_numeric($data['precio']) || $data['precio'] <= 0) {
        $errores['precio'] = "El precio debe ser un número positivo válido.";
    }

    if (!is_numeric($data['expensas']) || $data['expensas'] < 0) {
    $errores['expensas'] = "Las expensas deben ser un número (0 o más).";
}

    // Validamos cantidades (TINYINT UNSIGNED en la DB)
    $campos_numericos = [
        'cantidad_ambientes'   => 'ambientes',
        'cantidad_dormitorios' => 'dormitorios',
        'cantidad_banos'       => 'baños'
    ];

    foreach ($campos_numericos as $campo => $nombre) {
        if ($data[$campo] < 1) {
            $errores[$campo] = "La cantidad de $nombre debe ser al menos 1.";
        }
    }

    // Capacidad (Opcional, pero si está, debe ser lógica)
    if ($data['capacidad'] !== null && $data['capacidad'] <= 0) {
        $errores['capacidad'] = "Si se define la capacidad, debe ser mayor a 0.";
    }

    // 3. INTEGRIDAD REFERENCIAL (Uso de Eloquent)
    // Verificamos que los IDs existan realmente en las tablas relacionadas
    if (!Categoria::find($data['categoria_id'])) {
        $errores['categoria_id'] = "La categoría seleccionada no existe en el sistema.";
    }

    if (!Localidad::find($data['localidad_id'])) {
        $errores['localidad_id'] = "La localidad seleccionada no es válida.";
    }

    if (!Usuario::find($data['administrador_id'])) {
        $errores['administrador_id'] = "El administrador asignado no es un usuario registrado.";
    }

    // 4. VALIDACIÓN DE ESTADO
    if (!in_array($data['disponible'], [0, 1])) {
        $errores['disponible'] = "El estado de disponibilidad no es válido.";
    }

    return $errores;
}