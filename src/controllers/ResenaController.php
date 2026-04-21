<?php
/**
 * Controlador de Reseñas
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/resena_sanitizer.php';
require_once SRC_PATH . 'validators/resena_validator.php';

use App\Models\ResenaModel;

class ResenaController {
    
    private $model;
    
    public function __construct() {
        $this->model = new ResenaModel();
        header('Content-Type: application/json');
    }
    
    /**
     * GET /api/resenas
     */
    public function index() {
        try {
            $resenas = $this->model->getAll();
            echo json_encode([
                'success' => true,
                'data' => $resenas,
                'total' => count($resenas)
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
     * GET /api/resenas/{id}
     */
    public function show($id) {
        try {
            $validacion = validarSoloIdResena($id);
            if (!$validacion['success']) {
                http_response_code(400);
                echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $resena = $this->model->getById($id);
            
            if ($resena) {
                echo json_encode([
                    'success' => true,
                    'data' => $resena
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Reseña no encontrada'
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
     * GET /api/resenas/propiedad/{id}
     */
    public function getByPropiedad($propiedadId) {
        try {
            $resenas = $this->model->getByPropiedad($propiedadId);
            $promedio = $this->model->getPromedioByPropiedad($propiedadId);
            
            echo json_encode([
                'success' => true,
                'data' => $resenas,
                'total' => count($resenas),
                'promedio' => $promedio['promedio'],
                'total_resenas' => $promedio['total'],
                'propiedad_id' => $propiedadId
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
     * GET /api/resenas/usuario/{id}
     */
    public function getByUsuario($usuarioId) {
        try {
            $resenas = $this->model->getByUsuario($usuarioId);
            
            echo json_encode([
                'success' => true,
                'data' => $resenas,
                'total' => count($resenas),
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
     * GET /api/resenas/estadisticas
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
     * POST /api/resenas
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
        $datosSanitizados = sanitizarResena($data);
        
        // Validar
        $validacion = validarCrearResena($datosSanitizados);
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            // Verificar que la reserva existe y está finalizada
            if (!$this->model->reservaExistsAndFinalizada($datosSanitizados['reserva_id'])) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'La reserva no existe o no está finalizada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar que no existe ya una reseña para esta reserva
            if ($this->model->existePorReserva($datosSanitizados['reserva_id'])) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe una reseña para esta reserva'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Crear reseña
            $id = $this->model->create($datosSanitizados);
            $datosSanitizados['id'] = $id;
            $datosSanitizados['fecha_publicacion'] = date('Y-m-d H:i:s');
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Reseña creada exitosamente',
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
     * PUT /api/resenas/{id}
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
        $datosSanitizados = sanitizarResena($data);
        
        $validacion = validarActualizarResena($datosSanitizados);
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
                    'error' => 'Reseña no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->update($id, $datosSanitizados);
            
            echo json_encode([
                'success' => true,
                'message' => 'Reseña actualizada exitosamente',
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
     * DELETE /api/resenas/{id}
     */
    public function delete($id) {
        $validacion = validarSoloIdResena($id);
        
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
                    'error' => 'Reseña no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->delete($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Reseña eliminada exitosamente'
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