<?php

namespace App\Controllers;

function validarPropiedad($data) {
    $errores = [];

    // TITULO
    if (!$data['titulo']) {
        $errores[] = "Campo obligatorio: titulo";
    } elseif (strlen($data['titulo']) < 5 || strlen($data['titulo']) > 30) {
        $errores[] = "El titulo debe contener entre 5 y 30 caracteres";
    }

    // DESCRIPCION (opcional)
    if ($data['descripcion'] !== null) {
        if (strlen($data['descripcion']) < 10 || strlen($data['descripcion']) > 255) {
            $errores[] = "La descripcion debe contener entre 10 y 255 caracteres";
        }
    }

    // PRECIO
    if ($data['precio'] === null) {
        $errores[] = "Campo obligatorio: precio";
    } elseif ($data['precio'] <= 0) {
        $errores[] = "El precio debe ser mayor que 0";
    }

    // UBICACION
    if (!$data['ubicacion']) {
        $errores[] = "Campo obligatorio: ubicacion";
    } elseif (strlen($data['ubicacion']) < 10 || strlen($data['ubicacion']) > 100) {
        $errores[] = "La ubicacion debe contener entre 10 y 100 caracteres";
    }

    // CANTIDAD AMBIENTES
    if ($data['cantidad_ambientes'] === null) {
        $errores[] = "Campo obligatorio: cantidad_ambientes";
    } elseif ($data['cantidad_ambientes'] < 1 || $data['cantidad_ambientes'] > 10) {
        $errores[] = "Debe ser un numero entre 1 y 10 (cantidad_ambientes)";
    }

    // CANTIDAD DORMITORIOS
    if ($data['cantidad_dormitorios'] === null) {
        $errores[] = "Campo obligatorio: cantidad_dormitorios";
    } elseif ($data['cantidad_dormitorios'] < 1 || $data['cantidad_dormitorios'] > 10) {
        $errores[] = "Debe ser un numero entre 1 y 10 (cantidad_dormitorios)";
    } elseif ($data['cantidad_dormitorios'] > $data['cantidad_ambientes']) {
        $errores[] = "Los dormitorios no pueden ser mayores que la cantidad de ambientes";
    }

    // CANTIDAD BAÑOS
    if ($data['cantidad_banos'] === null) {
        $errores[] = "Campo obligatorio: cantidad_banos";
    } elseif ($data['cantidad_banos'] < 1 || $data['cantidad_banos'] > 10) {
        $errores[] = "Debe ser un numero entre 1 y 10 (cantidad_banos)";
    } elseif ($data['cantidad_banos'] > $data['cantidad_ambientes']) {
        $errores[] = "Los baños no pueden ser mayores que la cantidad de ambientes";
    }

    // CAPACIDAD (opcional)
    if ($data['capacidad'] !== null) {
        if ($data['capacidad'] < 1 || $data['capacidad'] > 20) {
            $errores[] = "La capacidad debe ser un numero entre 1 y 20";
        }
    }

    // DISPONIBLE
    if (!isset($data['disponible'])) {
        $errores[] = "Campo obligatorio: disponible";
    }

    // CATEGORIA_ID
    if ($data['categoria_id'] === null) {
        $errores[] = "Campo obligatorio: categoria_id";
    } elseif ($data['categoria_id'] <= 0) {
        $errores[] = "Categoria invalida";
    }

    return $errores;
}