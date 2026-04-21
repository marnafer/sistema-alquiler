<?php

namespace App\Sanitizers;

class FavoritoSanitizer {
    
    public static function sanitizarId($id) {
        // Eliminamos caracteres no numéricos y forzamos a entero
        return filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Limpia los IDs recibidos para asegurar que sean enteros
     */
    public static function sanitizarFavorito(array $data): array {
            return [
                'usuario_id'   => isset($data['usuario_id']) ? self::sanitizarId($data['usuario_id']) : 0,
                'propiedad_id' => isset($data['propiedad_id']) ? self::sanitizarId($data['propiedad_id']) : 0
            ];
    }
}