<?php

namespace App\Validators;

use App\Models\Localidad;

class LocalidadValidator
{
    /**
     * Valida que el id sea un entero válido (>0)
     */
    public static function validarId($id): bool
    {
        return is_numeric($id) && (int)$id > 0;
    }

    /**
     * Valida payload de localidad.
     * Si $isUpdate = true permite ausencia de campos para soportar PATCH parcial.
     * Retorna array asociativo de errores por campo.
     */
    public static function validarLocalidad(array $data, bool $isUpdate = false): array
    {
        $errores = [];

        // Nombre (obligatorio salvo en update parcial)
        if (!$isUpdate || ($isUpdate && array_key_exists('nombre', $data))) {
            $nombre = $data['nombre'] ?? null;
            if (empty($nombre)) {
                $errores['nombre'] = 'El nombre es obligatorio.';
            } elseif (mb_strlen($nombre) > 150) {
                $errores['nombre'] = 'El nombre no puede superar 150 caracteres.';
            } else {
                // Unicidad simple: evitar duplicados exactos al crear
                if (!$isUpdate) {
                    $existe = Localidad::where('nombre', $nombre)->first();
                    if ($existe) {
                        $errores['nombre'] = 'Ya existe una localidad con ese nombre.';
                    }
                }
            }
        }

        // Código postal (opcional pero con límites)
        if (array_key_exists('codigo_postal', $data) && $data['codigo_postal'] !== null) {
            $cp = $data['codigo_postal'];
            if (mb_strlen($cp) > 20) {
                $errores['codigo_postal'] = 'El código postal es demasiado largo (máx. 20 caracteres).';
            }
            // validación ligera: permitir números, letras y guiones
            if (!preg_match('/^[A-Za-z0-9\-\s]{1,20}$/u', $cp)) {
                $errores['codigo_postal'] = 'Formato de código postal inválido.';
            }
        }

        return $errores;
    }
}