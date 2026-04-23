<?php
/**
 * Controlador de LogActividad
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/log_actividad_sanitizer.php';
require_once SRC_PATH . 'validators/log_actividad_validator.php';

use App\Models\LogActividadModel;

class LogActividadController {
    
    private $model;
    
    public function __construct() {
        $this->model = new LogActividadModel();
        header('Content-Type: application/json');
    }
    
    /**
     * GET /api/logs-actividad
     */
    public function index() {
        try {
            $logs = $this->model->getAll();
            echo json_encode([
                'success' => true,
                'data' => $logs,
                'total' => count($logs)
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
     * GET /api/logs-actividad/{id}
     */
    public function show($id) {
        try {
            $validacion = validarSoloIdLogActividad($id);
            if (!$validacion['success']) {
                http_response_code(400);
                echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $log = $this->model->getById($id);
            
            if ($log) {
                echo json_encode([
                    'success' => true,
                    'data' => $log
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Log no encontrado'
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
     * GET /api/logs-actividad/usuario/{id}
     */
    public function getByUsuario($usuarioId) {
        try {
            $logs = $this->model->getByUsuario($usuarioId);
            echo json_encode([
                'success' => true,
                'data' => $logs,
                'total' => count($logs),
                'usuario_id' => $usuarioId
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
     * GET /api/logs-actividad/fecha?desde=X&hasta=Y
     */
    public function getByFecha() {
        $fechaDesde = $_GET['desde'] ?? null;
        $fechaHasta = $_GET['hasta'] ?? null;
        
        if (!$fechaDesde || !$fechaHasta) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Las fechas desde y hasta son requeridas'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $logs = $this->model->getByFechaRango($fechaDesde, $fechaHasta . ' 23:59:59');
            echo json_encode([
                'success' => true,
                'data' => $logs,
                'total' => count($logs),
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta
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
     * GET /api/logs-actividad/buscar?q=texto
     */
    public function search() {
        $busqueda = $_GET['q'] ?? null;
        
        if (!$busqueda) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'El término de búsqueda es requerido'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $logs = $this->model->getByAccion($busqueda);
            echo json_encode([
                'success' => true,
                'data' => $logs,
                'total' => count($logs),
                'busqueda' => $busqueda
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
     * GET /api/logs-actividad/estadisticas
     */
    public function getEstadisticas() {
        try {
            $estadisticas = $this->model->getEstadisticas();
            echo json_encode([
                'success' => true,
                'data' => $estadisticas
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
     * POST /api/logs-actividad/registrar
     */
    public function registrar() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['accion'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'La acción es requerida'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Sanitizar
        $datosSanitizados = sanitizarLogActividad($data);
        
        // Validar
        $validacion = validarCrearLogActividad($datosSanitizados);
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Si no viene IP, obtener automáticamente
        if (!isset($datosSanitizados['ip_address']) || !$datosSanitizados['ip_address']) {
            $datosSanitizados['ip_address'] = obtenerIpClienteLog();
        }
        
        try {
            $id = $this->model->create($datosSanitizados);
            $datosSanitizados['id'] = $id;
            $datosSanitizados['fecha'] = date('Y-m-d H:i:s');
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Log registrado exitosamente',
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
     * DELETE /api/logs-actividad/limpiar/antiguos?dias=30
     */
    public function limpiarAntiguos() {
        $dias = $_GET['dias'] ?? 30;
        
        if (!is_numeric($dias) || $dias <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'El número de días debe ser un número positivo'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $eliminados = $this->model->deleteOldLogs($dias);
            echo json_encode([
                'success' => true,
                'message' => "Se eliminaron {$eliminados} logs antiguos",
                'data' => [
                    'dias' => $dias,
                    'eliminados' => $eliminados
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
    
    /**
     * DELETE /api/logs-actividad/usuario/{id}
     */
    public function limpiarPorUsuario($usuarioId) {
        try {
            $eliminados = $this->model->deleteByUsuario($usuarioId);
            echo json_encode([
                'success' => true,
                'message' => "Se eliminaron {$eliminados} logs del usuario",
                'data' => [
                    'usuario_id' => $usuarioId,
                    'eliminados' => $eliminados
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
    
    /**
     * DELETE /api/logs-actividad/{id}
     */
    public function delete($id) {
        $validacion = validarSoloIdLogActividad($id);
        
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
                    'error' => 'Log no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->delete($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Log eliminado exitosamente'
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