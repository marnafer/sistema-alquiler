<?php
/**
 * Controlador de Servicios
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/servicio_sanitizer.php';
require_once SRC_PATH . 'validators/servicio_validator.php';

use App\Models\Servicio;

class ServicioController {
    
    private $model;
    
    public function __construct() {
        $this->model = new Servicio();
        header('Content-Type: application/json');
    }
    
    /**
     * GET /api/servicios
     */
    public function index() {
        try {
            $servicios = $this->model->getAll();
            echo json_encode([
                'success' => true,
                'data' => $servicios,
                'total' => count($servicios)
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
     * GET /api/servicios/con-propiedades
     */
    public function indexWithCount() {
        try {
            $servicios = $this->model->getAllWithCount();
            echo json_encode([
                'success' => true,
                'data' => $servicios,
                'total' => count($servicios)
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
     * GET /api/servicios/populares?limit=10
     */
    public function getPopulares() {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        if ($limit < 1 || $limit > 50) {
            $limit = 10;
        }
        
        try {
            $servicios = $this->model->getPopulares($limit);
            echo json_encode([
                'success' => true,
                'data' => $servicios,
                'total' => count($servicios),
                'limit' => $limit
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
     * GET /api/servicios/propiedad/{id}
     */
    public function getByPropiedad($propiedadId) {
        try {
            $servicios = $this->model->getByPropiedad($propiedadId);
            echo json_encode([
                'success' => true,
                'data' => $servicios,
                'total' => count($servicios),
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
     * GET /api/servicios/{id}
     */
    public function show($id) {
        try {
            $validacion = validarSoloIdServicio($id);
            if (!$validacion['success']) {
                http_response_code(400);
                echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $servicio = $this->model->getById($id);
            
            if ($servicio) {
                echo json_encode([
                    'success' => true,
                    'data' => $servicio
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Servicio no encontrado'
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
     * POST /api/servicios
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
        $datosSanitizados = sanitizarServicio($data);
        
        // Validar
        $validacion = validarCrearServicio($datosSanitizados);
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            // Verificar si ya existe un servicio con el mismo nombre
            if ($this->model->existsByNombre($datosSanitizados['nombre'])) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe un servicio con este nombre'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $id = $this->model->create($datosSanitizados);
            $datosSanitizados['id'] = $id;
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Servicio creado exitosamente',
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
     * PUT /api/servicios/{id}
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
        $datosSanitizados = sanitizarServicio($data);
        
        $validacion = validarActualizarServicio($datosSanitizados);
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
                    'error' => 'Servicio no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar si ya existe otro servicio con el mismo nombre
            if ($this->model->existsByNombre($datosSanitizados['nombre'], $id)) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe otro servicio con este nombre'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->update($id, $datosSanitizados);
            
            echo json_encode([
                'success' => true,
                'message' => 'Servicio actualizado exitosamente',
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
     * DELETE /api/servicios/{id}
     */
    public function delete($id) {
        $validacion = validarSoloIdServicio($id);
        
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
                    'error' => 'Servicio no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar si tiene propiedades asociadas
            if ($this->model->hasPropiedades($id)) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se puede eliminar el servicio porque tiene propiedades asociadas'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->delete($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Servicio eliminado exitosamente'
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