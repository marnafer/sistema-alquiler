<?php

// Importamos los modelos para verificar la existencia en la DB
use App\Models\Categoria;
use App\Models\Localidad;
use App\Models\Usuario;

/**
 * Validador robusto para la tabla PROPIEDADES
 * @param array $data Datos ya sanitizados
 * @return array Lista de errores (vac�a si todo est� ok)
 */

function validarPropiedad(array $data): array {
    $errores = [];

    // 1. VALIDACI�N DE TEXTOS (L�mites de VARCHAR en SQL)
    if (empty($data['titulo'])) {
        $errores['titulo'] = "El t�tulo es obligatorio.";
    } elseif (strlen($data['titulo']) > 150) {
        $errores['titulo'] = "El t�tulo no puede superar los 150 caracteres.";
    }

    if (empty($data['direccion'])) {
        $errores['direccion'] = "La direcci�n exacta es obligatoria.";
    } elseif (strlen($data['direccion']) > 125) {
        $errores['direccion'] = "La direcci�n es demasiado larga (m�ximo 125 caracteres).";
    }

    // 2. VALIDACI�N DE N�MEROS Y TIPOS (L�gica de Negocio)
    if (!is_numeric($data['precio']) || $data['precio'] <= 0) {
        $errores['precio'] = "El precio debe ser un n�mero positivo v�lido.";
    }

    if (!is_numeric($data['expensas']) || $data['expensas'] < 0) {
    $errores['expensas'] = "Las expensas deben ser un n�mero (0 o m�s).";
}

    // Validamos cantidades (TINYINT UNSIGNED en la DB)
    $campos_numericos = [
        'cantidad_ambientes'   => 'ambientes',
        'cantidad_dormitorios' => 'dormitorios',
        'cantidad_banos'       => 'ba�os'
    ];

    foreach ($campos_numericos as $campo => $nombre) {
        if (!isset($data[$campo]) || $data[$campo] < 1) {
            $errores[$campo] = "La cantidad de $nombre debe ser al menos 1.";
        }
    }

    // Capacidad (Opcional, pero si est�, debe ser l�gica)
    if ($data['capacidad'] !== null && $data['capacidad'] <= 0) {
        $errores['capacidad'] = "Si se define la capacidad, debe ser mayor a 0.";
    }

    // 3. INTEGRIDAD REFERENCIAL (Uso de Eloquent)
    // Verificamos que los IDs existan realmente en las tablas relacionadas
    if (!Categoria::find($data['categoria_id'])) {
        $errores['categoria_id'] = "La categor�a seleccionada no existe en el sistema.";
    }

    if (!Localidad::find($data['localidad_id'])) {
        $errores['localidad_id'] = "La localidad seleccionada no es v�lida.";
    }

    // 4. VALIDACI�N DE ESTADO
    if (!in_array($data['disponible'], [0, 1])) {
        $errores['disponible'] = "El estado de disponibilidad no es v�lido.";
    }

    return $errores;
}