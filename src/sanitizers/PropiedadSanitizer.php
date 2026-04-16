<?php

function sanitizarPropiedad(array $data): array {
    return [
        // Campos de texto: Limpieza de espacios y protección contra XSS
        'titulo' => htmlspecialchars(trim($data['titulo'] ?? '')),
        
        // Campo opcional (permite NULL en la DB)
        'descripcion' => isset($data['descripcion']) && trim($data['descripcion']) !== '' 
            ? htmlspecialchars(trim($data['descripcion'])) 
            : null,

        // Precio: IMPORTANTE usar FLOAT para no perder los centavos (decimal 12,2)
        'precio' => filter_var($data['precio'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),

        'direccion' => htmlspecialchars(trim($data['direccion'] ?? '')),

        // Cantidades: Forzamos a entero positivo (unsigned en la DB)
        'cantidad_ambientes' => abs((int)($data['cantidad_ambientes'] ?? 0)),
        'cantidad_dormitorios' => abs((int)($data['cantidad_dormitorios'] ?? 0)),
        'cantidad_banos' => abs((int)($data['cantidad_banos'] ?? 0)),

        // Capacidad: Campo opcional (permite NULL en la DB)
        'capacidad' => isset($data['capacidad']) && $data['capacidad'] !== '' 
            ? abs((int)$data['capacidad']) 
            : null,

        // Disponible: Tinyint(1) tratado como booleano
        'disponible' => isset($data['disponible']) 
            ? (filter_var($data['disponible'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0) 
            : 1,

        // Claves Foráneas: Siempre enteros
        'categoria_id' => (int)($data['categoria_id'] ?? 0),
        'administrador_id' => (int)($data['administrador_id'] ?? 0),
        'localidad_id' => (int)($data['localidad_id'] ?? 0)
    ];
}