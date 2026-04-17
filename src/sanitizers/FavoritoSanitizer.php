<?php
/**
 * Limpia los IDs recibidos para asegurar que sean enteros
 */
function sanitizarFavorito(array $data): array {
    return [
        // intval() convierte cualquier cosa extraña en un número entero o 0
        'usuario_id'   => isset($data['usuario_id']) ? intval($data['usuario_id']) : 0,
        'propiedad_id' => isset($data['propiedad_id']) ? intval($data['propiedad_id']) : 0
    ];
}