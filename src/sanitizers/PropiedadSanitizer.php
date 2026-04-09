<?php

namespace App\Controllers;

function sanitizarPropiedad($data) {
    return [
        'titulo' => htmlspecialchars(trim($data['titulo'] ?? '')),
        'descripcion' => isset($data['descripcion']) 
            ? htmlspecialchars(trim($data['descripcion'])) 
            : null,
        'precio' => (int) filter_var($data['precio'] ?? 0, FILTER_SANITIZE_NUMBER_INT),
        'ubicacion' => htmlspecialchars(trim($data['ubicacion'] ?? '')),
        'cantidad_ambientes' => (int) filter_var($data['cantidad_ambientes'] ?? 0, FILTER_SANITIZE_NUMBER_INT),
        'cantidad_dormitorios' => (int) filter_var($data['cantidad_dormitorios'] ?? 0, FILTER_SANITIZE_NUMBER_INT),
        'cantidad_banos' => (int) filter_var($data['cantidad_banos'] ?? 0, FILTER_SANITIZE_NUMBER_INT),
        'capacidad' => isset($data['capacidad']) 
            ? (int) filter_var($data['capacidad'], FILTER_SANITIZE_NUMBER_INT) 
            : null,
        'disponible' => isset($data['disponible']) 
            ? filter_var($data['disponible'], FILTER_VALIDATE_BOOLEAN) 
            : true,
        'categoria_id' => (int) filter_var($data['categoria_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT)
    ];
}