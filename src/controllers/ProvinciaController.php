<?php
/**
 * Controlador de Provincias
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/ProvinciaSanitizer.php';
require_once SRC_PATH . 'validators/ProvinciaValidator.php';

use App\Models\Provincia;

class ProvinciaController {
    
    private $model;
    
    public function __construct() {
        $this->model = new Provincia();
        header('Content-Type: application/json');
    }
    
    /**
     * GET /api/provincias
     */
    public function index() {
        try {
            $provincias = $this->model->getAll();
            echo json_encode([
                'success' => true,
                'data' => $provincias,
                'total' => count($provincias)
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
     * GET /api/provincias/con-localidades
     */
    public function indexWithCount() {
        try {
            $provincias = $this->model->getAllWithCount();
            echo json_encode([
                'success' => true,
                'data' => $provincias,
                'total' => count($provincias)
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
     * GET /api/provincias/{id}
     */
    public function show($id) {
        try {
            $validacion = validarSoloIdProvincia($id);
            if (!$validacion['success']) {
                http_response_code(400);
                echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $provincia = $this->model->getById($id);
            
            if ($provincia) {
                echo json_encode([
                    'success' => true,
                    'data' => $provincia
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Provincia no encontrada'
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
     * POST /api/provincias
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
        $datosSanitizados = sanitizarProvincia($data);
        
        // Validar
        $validacion = validarCrearProvincia($datosSanitizados);
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            // Verificar si ya existe una provincia con el mismo nombre
            if ($this->model->existsByNombre($datosSanitizados['nombre'])) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe una provincia con este nombre'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $id = $this->model->create($datosSanitizados);
            $datosSanitizados['id'] = $id;
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Provincia creada exitosamente',
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
     * PUT /api/provincias/{id}
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
        $datosSanitizados = sanitizarProvincia($data);
        
        $validacion = validarActualizarProvincia($datosSanitizados);
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
                    'error' => 'Provincia no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar si ya existe otra provincia con el mismo nombre
            if ($this->model->existsByNombre($datosSanitizados['nombre'], $id)) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe otra provincia con este nombre'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->update($id, $datosSanitizados);
            
            echo json_encode([
                'success' => true,
                'message' => 'Provincia actualizada exitosamente',
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
     * DELETE /api/provincias/{id}
     */
    public function delete($id) {
        $validacion = validarSoloIdProvincia($id);
        
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
                    'error' => 'Provincia no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar si tiene localidades asociadas
            if ($this->model->hasLocalidades($id)) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se puede eliminar la provincia porque tiene localidades asociadas'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->delete($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Provincia eliminada exitosamente'
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