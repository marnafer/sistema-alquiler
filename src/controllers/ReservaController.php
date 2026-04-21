<?php
/**
 * Controlador de Reservas (usando modelo sencillo)
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/reserva_sanitizer.php';
require_once SRC_PATH . 'validators/reserva_validator.php';

use App\Models\Reserva;

class ReservaController {
    
    private $model;
    
    public function __construct() {
        $this->model = new Reserva();
        header('Content-Type: application/json');
    }
    
    /**
     * GET /api/reservas
     */
    public function index() {
        try {
            $reservas = $this->model->getAll();
            echo json_encode([
                'success' => true,
                'data' => $reservas,
                'total' => count($reservas)
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
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
    public function show($id) {
        try {
            $validacion = validarSoloIdReserva($id);
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
        } catch (\Exception $e) {
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
    public function store() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos inválidos'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Sanitizar
        $datosSanitizados = sanitizarReserva($data);
        
        // Validar
        $validacion = validarCrearReserva($datosSanitizados);
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            // Verificar propiedad e inquilino
            if (!$this->model->propiedadExists($datosSanitizados['propiedad_id'])) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Propiedad no existe o no está disponible'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
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
            $id = $this->model->create($datosSanitizados);
            $datosSanitizados['id'] = $id;
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Reserva creada exitosamente',
                'data' => $datosSanitizados
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
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
    public function update($id) {
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
        $datosSanitizados = sanitizarReserva($data);
        
        $validacion = validarActualizarReserva($datosSanitizados);
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
            
            $this->model->update($id, $datosSanitizados);
            
            echo json_encode([
                'success' => true,
                'message' => 'Reserva actualizada exitosamente',
                'data' => $datosSanitizados
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
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
    public function changeStatus($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['estado'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'El estado es requerido'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $validacionId = validarSoloIdReserva($id);
        if (!$validacionId['success']) {
            http_response_code(400);
            echo json_encode($validacionId, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $estadoSanitizado = sanitizarSoloEstadoReserva($data['estado']);
        $validacionEstado = validarSoloEstadoReserva($estadoSanitizado);
        
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
        } catch (\Exception $e) {
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
    public function delete($id) {
        $validacion = validarSoloIdReserva($id);
        
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
            
            $this->model->delete($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Reserva eliminada exitosamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
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
    public function checkAvailability() {
        $propiedadId = $_GET['propiedad_id'] ?? null;
        $fechaDesde = $_GET['fecha_desde'] ?? null;
        $fechaHasta = $_GET['fecha_hasta'] ?? null;
        
        $fechasSanitizadas = sanitizarFechasReserva([
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta
        ]);
        
        $validacion = validarFechasDisponibilidadReserva($fechasSanitizadas);
        
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
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}