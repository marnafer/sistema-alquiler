<?php
/**
 * Sanitizador para la entidad Categoría
 * SOLO sanitiza los datos, NO valida
 */

function sanitizarCategoria($data) {
    return [
        'id' => isset($data['id']) ? filter_var($data['id'], FILTER_VALIDATE_INT) : null,
        'nombre' => isset($data['nombre']) ? sanitizarNombreCategoria($data['nombre']) : null
    ];
}

function sanitizarNombreCategoria($nombre) {
    $nombre = trim($nombre);
    $nombre = preg_replace('/\s+/', ' ', $nombre);
    $nombre = ucwords(strtolower($nombre));
    $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    $nombre = preg_replace('/[^a-zA-ZáéíóúñÑÁÉÍÓÚ0-9\s\-\.]/u', '', $nombre);
    return $nombre;
}