<?php
/**
 * Controlador de Reservas
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/ReservaSanitizer.php';
require_once SRC_PATH . 'validators/ReservaValidator.php';

use App\Models\Reserva;
use App\Sanitizers\ReservaSanitizer;
use App\Validators\ReservaValidator;
use Exception;

class ReservaController
{
    private $model;

    public function __construct()
    {
        $this->model = new Reserva();
        header('Content-Type: application/json');
    }

    /**
     * GET /api/reservas
     */
    public function index()
    {
        try {
            $reservas = $this->model->getAll();
            echo json_encode([
                'success' => true,
                'data' => $reservas,
                'total' => count($reservas)
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /api/reservas/{id}
     */
    public function show($id)
    {
        try {
            $validacion = ReservaValidator::validarSoloId($id);
            if (!$validacion['success']) {
                http_response_code(400);
                echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
                return;
            }

            $reserva = $this->model->getById($id);

            if ($reserva) {
                echo json_encode([
                    'success' => true,
                    'data' => $reserva
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Reserva no encontrada'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /api/reservas/propiedad/{id}
     */
    public function getByPropiedad($propiedadId)
    {
        try {
            $reservas = $this->model->getByPropiedad($propiedadId);
            echo json_encode([
                'success' => true,
                'data' => $reservas,
                'total' => count($reservas),
                'propiedad_id' => $propiedadId
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /api/reservas/inquilino/{id}
     */
    public function getByInquilino($inquilinoId)
    {
        try {
            $reservas = $this->model->getByInquilino($inquilinoId);
            echo json_encode([
                'success' => true,
                'data' => $reservas,
                'total' => count($reservas),
                'inquilino_id' => $inquilinoId
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * POST /api/reservas
     */
    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos inválidos'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // 1. SANITIZAR
        $datosSanitizados = ReservaSanitizer::sanitizar($data);

        // 2. VALIDAR
        $errores = ReservaValidator::validarCrear($datosSanitizados);

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $errores
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // Verificar propiedad
            if (!$this->model->propiedadExists($datosSanitizados['propiedad_id'])) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Propiedad no existe o no está disponible'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Verificar inquilino
            if (!$this->model->inquilinoExists($datosSanitizados['inquilino_id'])) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Inquilino no existe'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Verificar disponibilidad
            $disponible = $this->model->checkAvailability(
                $datosSanitizados['propiedad_id'],
                $datosSanitizados['fecha_desde'],
                $datosSanitizados['fecha_hasta']
            );

            if (!$disponible) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Propiedad no disponible en esas fechas'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Crear reserva
            $id = $this->model->createReserva($datosSanitizados);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Reserva creada exitosamente',
                'data' => ['id' => $id]
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * PUT /api/reservas/{id}
     */
    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos inválidos'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $data['id'] = $id;

        // 1. SANITIZAR
        $datosSanitizados = ReservaSanitizer::sanitizar($data);

        // 2. VALIDAR
        $errores = ReservaValidator::validarActualizar($datosSanitizados);

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $errores
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            if (!$this->model->exists($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Reserva no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->model->updateReserva($id, $datosSanitizados);

            echo json_encode([
                'success' => true,
                'message' => 'Reserva actualizada exitosamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * PATCH /api/reservas/{id}/estado
     */
    public function changeStatus($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['estado'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'El estado es requerido'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validar ID
        $validacionId = ReservaValidator::validarSoloId($id);
        if (!$validacionId['success']) {
            http_response_code(400);
            echo json_encode($validacionId, JSON_UNESCAPED_UNICODE);
            return;
        }

        // Sanitizar y validar estado
        $estadoSanitizado = ReservaSanitizer::sanitizarSoloEstado($data['estado']);
        $validacionEstado = ReservaValidator::validarSoloEstado($estadoSanitizado);

        if (!$validacionEstado['success']) {
            http_response_code(400);
            echo json_encode($validacionEstado, JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            if (!$this->model->exists($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Reserva no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->model->changeStatus($id, $estadoSanitizado);

            echo json_encode([
                'success' => true,
                'message' => 'Estado actualizado',
                'data' => ['id' => $id, 'estado' => $estadoSanitizado]
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * DELETE /api/reservas/{id}
     */
    public function delete($id)
    {
        $validacion = ReservaValidator::validarSoloId($id);

        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            if (!$this->model->exists($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Reserva no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->model->deleteReserva($id);

            echo json_encode([
                'success' => true,
                'message' => 'Reserva eliminada exitosamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /api/reservas/disponibilidad?propiedad_id=X&fecha_desde=Y&fecha_hasta=Z
     */
    public function checkAvailability()
    {
        $propiedadId = $_GET['propiedad_id'] ?? null;
        $fechaDesde = $_GET['fecha_desde'] ?? null;
        $fechaHasta = $_GET['fecha_hasta'] ?? null;

        if (!$propiedadId) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'propiedad_id es requerido'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Sanitizar fechas
        $fechasSanitizadas = ReservaSanitizer::sanitizarFechas([
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta
        ]);

        // Validar fechas
        $validacion = ReservaValidator::validarFechasDisponibilidad($fechasSanitizadas);

        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $disponible = $this->model->checkAvailability(
                $propiedadId,
                $fechasSanitizadas['fecha_desde'],
                $fechasSanitizadas['fecha_hasta']
            );

            echo json_encode([
                'success' => true,
                'data' => [
                    'disponible' => $disponible,
                    'propiedad_id' => $propiedadId,
                    'fecha_desde' => $fechasSanitizadas['fecha_desde'],
                    'fecha_hasta' => $fechasSanitizadas['fecha_hasta']
                ]
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}