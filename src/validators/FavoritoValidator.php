<?php

use App\Models\Usuario;
use App\Models\Propiedad;
use App\Models\Favorito;

/**
 * Valida la existencia y evita duplicados al agregar
 */
function validarFavorito(array $data): array {
    $errores = [];

    // 1. Validación de existencia de IDs
    if ($data['usuario_id'] <= 0) {
        $errores['usuario_id'] = "ID de usuario inválido.";
    } elseif (!Usuario::find($data['usuario_id'])) {
        $errores['usuario_id'] = "El usuario no existe.";
    }

    if ($data['propiedad_id'] <= 0) {
        $errores['propiedad_id'] = "ID de propiedad inválido.";
    } elseif (!Propiedad::find($data['propiedad_id'])) {
        $errores['propiedad_id'] = "La propiedad no existe o fue eliminada.";
    }

    // 2. Validación de Duplicados
    if (empty($errores)) {
        $existe = Favorito::where('usuario_id', $data['usuario_id'])
                          ->where('propiedad_id', $data['propiedad_id'])
                          ->first();
        
        if ($existe) {
            $errores['duplicado'] = "Esta propiedad ya está en tu lista de favoritos.";
        }
    }

    return $errores;
}

/**
 * Valida que el favorito exista antes de intentar borrarlo
 */
function validarQuitarFavorito(array $data): array {
    $errores = [];

    // Verificamos si la relación existe en la tabla
    // Usamos Favorito directamente porque ya está en el 'use' superior
    $existe = Favorito::where('usuario_id', $data['usuario_id'])
                      ->where('propiedad_id', $data['propiedad_id'])
                      ->first();
    
    if (!$existe) {
        $errores['inexistente'] = "El favorito que intentas quitar no existe.";
    }

    return $errores;
}