<?php
/**
 * Controlador del módulo de Consultas
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/consulta_sanitizer.php';

use App\Models\Consulta;

class ConsultaController {
    
    /**
     * Listar todas las consultas
     * GET /api/consultas
     */
    public function listar() {
        try {
            $consultas = $this->model->listar();
            
            echo json_encode([
                'success' => true,
                'data' => $consultas,
                'total' => count($consultas)
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
     * Obtener una consulta específica
     * GET /api/consultas/{id}
     */
    public function obtener($id) {
        try {
            $resultadoId = validarIdConsultaRequerido($id);
            if (!$resultadoId['success']) {
                http_response_code(400);
                echo json_encode($resultadoId, JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $consulta = $this->model->obtener($id);
            
            if ($consulta) {
                echo json_encode([
                    'success' => true,
                    'data' => $consulta
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Consulta no encontrada'
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
     * Listar consultas por propiedad
     * GET /api/consultas/propiedad/{id}
     */
    public function listarPorPropiedad($propiedadId) {
        try {
            $consultas = $this->model->listarPorPropiedad($propiedadId);
            
            echo json_encode([
                'success' => true,
                'data' => $consultas,
                'total' => count($consultas),
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
     * Listar consultas por inquilino
     * GET /api/consultas/inquilino/{id}
     */
    public function listarPorInquilino($inquilinoId) {
        try {
            $consultas = $this->model->listarPorInquilino($inquilinoId);
            
            echo json_encode([
                'success' => true,
                'data' => $consultas,
                'total' => count($consultas),
                'inquilino_id' => $inquilinoId
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
     * Crear una nueva consulta
     * POST /api/consultas
     */
    public function crear() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos inválidos o no proporcionados'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Validar datos
        $resultadoValidacion = validarConsulta($data);
        
        if (!$resultadoValidacion['success']) {
            http_response_code(400);
            echo json_encode($resultadoValidacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            // Verificar que la propiedad existe
            $propiedad = $this->model->propiedadExiste($resultadoValidacion['data']['propiedad_id']);
            if (!$propiedad) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'La propiedad no existe o no está disponible'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar que el inquilino existe
            $inquilino = $this->model->inquilinoExiste($resultadoValidacion['data']['inquilino_id']);
            if (!$inquilino) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'El inquilino no existe'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Crear consulta
            $id = $this->model->crear($resultadoValidacion['data']);
            $resultadoValidacion['data']['id'] = $id;
            $resultadoValidacion['data']['fecha_consulta'] = date('Y-m-d H:i:s');
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Consulta creada exitosamente',
                'data' => $resultadoValidacion['data']
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
     * Actualizar una consulta
     * PUT /api/consultas/{id}
     */
    public function actualizar($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos inválidos o no proporcionados'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $data['id'] = $id;
        $resultadoValidacion = validarConsulta($data);
        
        if (!$resultadoValidacion['success']) {
            http_response_code(400);
            echo json_encode($resultadoValidacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            // Verificar si existe
            if (!$this->model->existe($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Consulta no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->actualizar($id, $resultadoValidacion['data']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Consulta actualizada exitosamente',
                'data' => $resultadoValidacion['data']
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
     * Eliminar una consulta (soft delete)
     * DELETE /api/consultas/{id}
     */
    public function eliminar($id) {
        try {
            $resultadoId = validarIdConsultaRequerido($id);
            if (!$resultadoId['success']) {
                http_response_code(400);
                echo json_encode($resultadoId, JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar si existe
            if (!$this->model->existe($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Consulta no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->eliminar($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Consulta eliminada exitosamente'
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