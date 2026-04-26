<?php

namespace App\Validators;

class ReservaValidator
{
    /**
     * Validar todos los datos de una reserva
     */
    public static function validar(array $data, bool $requerirId = false): array
    {
        $errores = [];

        // Validar ID (solo si se requiere para actualizaciones)
        if ($requerirId) {
            $error = self::validarId($data['id'] ?? null);
            if ($error) {
                $errores['id'] = $error;
            }
        }

        // Validar propiedad_id
        $error = self::validarPropiedadId($data['propiedad_id'] ?? null);
        if ($error) {
            $errores['propiedad_id'] = $error;
        }

        // Validar inquilino_id
        $error = self::validarInquilinoId($data['inquilino_id'] ?? null);
        if ($error) {
            $errores['inquilino_id'] = $error;
        }

        // Validar fecha_desde
        $error = self::validarFechaDesde($data['fecha_desde'] ?? null);
        if ($error) {
            $errores['fecha_desde'] = $error;
        }

        // Validar fecha_hasta
        $error = self::validarFechaHasta($data['fecha_hasta'] ?? null);
        if ($error) {
            $errores['fecha_hasta'] = $error;
        }

        // Validar relación entre fechas
        if (!isset($errores['fecha_desde']) && !isset($errores['fecha_hasta'])) {
            $error = self::validarRelacionFechas($data['fecha_desde'] ?? null, $data['fecha_hasta'] ?? null);
            if ($error) {
                $errores['fechas'] = $error;
            }
        }

        // Validar precio_total
        $error = self::validarPrecio($data['precio_total'] ?? null);
        if ($error) {
            $errores['precio_total'] = $error;
        }

        // Validar estado (opcional)
        if (isset($data['estado']) && !empty($data['estado'])) {
            $error = self::validarEstado($data['estado']);
            if ($error) {
                $errores['estado'] = $error;
            }
        }

        return $errores;
    }

    /**
     * Validar ID de reserva
     */
    public static function validarId($id): ?string
    {
        if ($id === null || $id === '') {
            return 'El ID de reserva es requerido';
        }

        if (!is_numeric($id)) {
            return 'El ID debe ser un número';
        }

        if ($id <= 0) {
            return 'El ID debe ser un número positivo';
        }

        if (filter_var($id, FILTER_VALIDATE_INT) === false) {
            return 'El ID debe ser un número entero';
        }

        return null;
    }

    /**
     * Validar ID de propiedad
     */
    public static function validarPropiedadId($id): ?string
    {
        if ($id === null || $id === '') {
            return 'El ID de propiedad es requerido';
        }

        if (!is_numeric($id)) {
            return 'El ID de propiedad debe ser un número';
        }

        if ($id <= 0) {
            return 'El ID de propiedad debe ser un número positivo';
        }

        return null;
    }

    /**
     * Validar ID de inquilino
     */
    public static function validarInquilinoId($id): ?string
    {
        if ($id === null || $id === '') {
            return 'El ID del inquilino es requerido';
        }

        if (!is_numeric($id)) {
            return 'El ID del inquilino debe ser un número';
        }

        if ($id <= 0) {
            return 'El ID del inquilino debe ser un número positivo';
        }

        return null;
    }

    /**
     * Validar fecha desde
     */
    public static function validarFechaDesde($fecha): ?string
    {
        if ($fecha === null || $fecha === '') {
            return 'La fecha de inicio es requerida';
        }

        $timestamp = strtotime($fecha);

        if ($timestamp === false) {
            return 'La fecha de inicio no es válida';
        }

        $hoy = strtotime(date('Y-m-d'));

        if ($timestamp < $hoy) {
            return 'La fecha de inicio no puede ser anterior a hoy';
        }

        return null;
    }

    /**
     * Validar fecha hasta
     */
    public static function validarFechaHasta($fecha): ?string
    {
        if ($fecha === null || $fecha === '') {
            return 'La fecha de fin es requerida';
        }

        $timestamp = strtotime($fecha);

        if ($timestamp === false) {
            return 'La fecha de fin no es válida';
        }

        return null;
    }

    /**
     * Validar relación entre fechas
     */
    public static function validarRelacionFechas($fechaDesde, $fechaHasta): ?string
    {
        $timestampDesde = strtotime($fechaDesde);
        $timestampHasta = strtotime($fechaHasta);

        if ($timestampHasta <= $timestampDesde) {
            return 'La fecha de fin debe ser posterior a la fecha de inicio';
        }

        $dias = ($timestampHasta - $timestampDesde) / (60 * 60 * 24);

        if ($dias < 1) {
            return 'La reserva debe ser de al menos 1 día';
        }

        if ($dias > 365) {
            return 'La reserva no puede exceder los 365 días';
        }

        return null;
    }

    /**
     * Validar precio total
     */
    public static function validarPrecio($precio): ?string
    {
        if ($precio === null || $precio === '') {
            return 'El precio total es requerido';
        }

        if (!is_numeric($precio)) {
            return 'El precio debe ser un número';
        }

        if ($precio <= 0) {
            return 'El precio debe ser mayor a 0';
        }

        if ($precio > 999999999.99) {
            return 'El precio excede el límite permitido';
        }

        return null;
    }

    /**
     * Validar estado de reserva
     */
    public static function validarEstado($estado): ?string
    {
        $estadosValidos = ['pendiente', 'confirmada', 'cancelada', 'finalizada'];

        $estado = strtolower(trim($estado));

        if (!in_array($estado, $estadosValidos)) {
            return 'Estado inválido. Valores permitidos: ' . implode(', ', $estadosValidos);
        }

        return null;
    }

    /**
     * Validar para crear nueva reserva
     */
    public static function validarCrear(array $data): array
    {
        return self::validar($data, false);
    }

    /**
     * Validar para actualizar reserva existente
     */
    public static function validarActualizar(array $data): array
    {
        return self::validar($data, true);
    }

    /**
     * Validar solo ID
     */
    public static function validarSoloId($id): array
    {
        $error = self::validarId($id);

        if ($error) {
            return [
                'success' => false,
                'message' => 'ID inválido',
                'errors' => ['id' => $error]
            ];
        }

        return [
            'success' => true,
            'message' => 'ID válido',
            'errors' => null
        ];
    }

    /**
     * Validar solo fechas (para disponibilidad)
     */
    public static function validarFechasDisponibilidad(array $data): array
    {
        $errores = [];

        $error = self::validarFechaDesde($data['fecha_desde'] ?? null);
        if ($error) {
            $errores['fecha_desde'] = $error;
        }

        $error = self::validarFechaHasta($data['fecha_hasta'] ?? null);
        if ($error) {
            $errores['fecha_hasta'] = $error;
        }

        if (!isset($errores['fecha_desde']) && !isset($errores['fecha_hasta'])) {
            $error = self::validarRelacionFechas($data['fecha_desde'] ?? null, $data['fecha_hasta'] ?? null);
            if ($error) {
                $errores['fechas'] = $error;
            }
        }

        if (count($errores) > 0) {
            return [
                'success' => false,
                'message' => 'Error de validación de fechas',
                'errors' => $errores
            ];
        }

        return [
            'success' => true,
            'message' => 'Fechas válidas',
            'errors' => null
        ];
    }

    /**
     * Validar solo estado
     */
    public static function validarSoloEstado($estado): array
    {
        $error = self::validarEstado($estado);

        if ($error) {
            return [
                'success' => false,
                'message' => 'Estado inválido',
                'errors' => ['estado' => $error]
            ];
        }

        return [
            'success' => true,
            'message' => 'Estado válido',
            'errors' => null
        ];
    }
}