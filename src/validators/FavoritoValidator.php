<?php

namespace App\Validators;

use App\Models\Usuario;
use App\Models\Propiedad;
use App\Models\Favorito;


Class FavoritoValidator {

    /**
     * Valida la existencia y evita duplicados al agregar
     */
    public static function validarFavorito(array $data): array {
        $errores = [];

        // 1. Validaci�n de existencia de IDs
        if ($data['usuario_id'] <= 0) {
            $errores['usuario_id'] = "ID de usuario inv�lido.";
        } elseif (!Usuario::find($data['usuario_id'])) {
            $errores['usuario_id'] = "El usuario no existe.";
        }

        if ($data['propiedad_id'] <= 0) {
            $errores['propiedad_id'] = "ID de propiedad inv�lido.";
        } elseif (!Propiedad::find($data['propiedad_id'])) {
            $errores['propiedad_id'] = "La propiedad no existe o fue eliminada.";
        }

        // 2. Validaci�n de Duplicados
        if (empty($errores)) {
            $existe = Favorito::where('usuario_id', $data['usuario_id'])
                              ->where('propiedad_id', $data['propiedad_id'])
                              ->first();
            
            if ($existe) {
                $errores['duplicado'] = "Esta propiedad ya est� en tu lista de favoritos.";
            }
        }

        return $errores;
    }

    /**
     * Valida que el favorito exista antes de intentar borrarlo
     */
    public static function validarQuitarFavorito(array $data): array {
        $errores = [];

        // Verificamos si la relaci�n existe en la tabla
        // Usamos Favorito directamente porque ya est� en el 'use' superior
        $existe = Favorito::where('usuario_id', $data['usuario_id'])
                          ->where('propiedad_id', $data['propiedad_id'])
                          ->first();
        
        if (!$existe) {
            $errores['inexistente'] = "El favorito que intentas quitar no existe.";
        }

        return $errores;
    }
}
