<?php
/**
 * Controlador de PropiedadServicio
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/PropiedadServicioSanitizer.php';
require_once SRC_PATH . 'validators/PropiedadServicioValidator.php';

use App\Models\PropiedadServicio;

class PropiedadServicioController {
    
    private $model;
    
    public function __construct() {
        $this->model = new PropiedadServicio();
        header('Content-Type: application/json');
    }
    
    /**
     * GET /api/propiedades-servicios
     */
    public function index() {
        try {
            $relaciones = $this->model->getAll();
            echo json_encode([
                'success' => true,
                'data' => $relaciones,
                'total' => count($relaciones)
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
     * GET /api/propiedades-servicios/{id}
     */
    public function show($id) {
        try {
            $validacion = validarSoloIdPropiedadServicio($id);
            if (!$validacion['success']) {
                http_response_code(400);
                echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $relacion = $this->model->getById($id);
            
            if ($relacion) {
                echo json_encode([
                    'success' => true,
                    'data' => $relacion
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Relación no encontrada'
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
     * GET /api/propiedades-servicios/propiedad/{id}
     */
    public function getByPropiedad($propiedadId) {
        try {
            $relaciones = $this->model->getByPropiedad($propiedadId);
            echo json_encode([
                'success' => true,
                'data' => $relaciones,
                'total' => count($relaciones),
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
     * GET /api/propiedades-servicios/servicio/{id}
     */
    public function getByServicio($servicioId) {
        try {
            $relaciones = $this->model->getByServicio($servicioId);
            echo json_encode([
                'success' => true,
                'data' => $relaciones,
                'total' => count($relaciones),
                'servicio_id' => $servicioId
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
     * GET /api/propiedades-servicios/estadisticas
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
     * POST /api/propiedades-servicios
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
        $datosSanitizados = sanitizarPropiedadServicio($data);
        
        // Validar
        $validacion = validarCrearPropiedadServicio($datosSanitizados);
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            // Verificar que la propiedad y el servicio existen
            global $db;
            $existencia = validarExistenciaPropiedadServicio(
                $datosSanitizados['propiedad_id'],
                $datosSanitizados['servicio_id'],
                $db
            );
            
            if (!$existencia['success']) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => $existencia['error']
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar que no exista ya la relación
            if ($this->model->existsRelacion($datosSanitizados['propiedad_id'], $datosSanitizados['servicio_id'])) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Esta propiedad ya tiene asociado este servicio'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $id = $this->model->create($datosSanitizados);
            $datosSanitizados['id'] = $id;
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Servicio asignado a la propiedad exitosamente',
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
     * POST /api/propiedades-servicios/sync/{propiedadId}
     * Sincronizar servicios de una propiedad (reemplaza todos)
     */
    public function sync($propiedadId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['servicios_ids']) || !is_array($data['servicios_ids'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Se requiere un array de IDs de servicios'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $count = $this->model->syncServiciosByPropiedad($propiedadId, $data['servicios_ids']);
            
            echo json_encode([
                'success' => true,
                'message' => "Servicios sincronizados exitosamente",
                'data' => [
                    'propiedad_id' => $propiedadId,
                    'servicios_asignados' => $count,
                    'servicios_ids' => $data['servicios_ids']
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
     * DELETE /api/propiedades-servicios/{id}
     */
    public function delete($id) {
        $validacion = validarSoloIdPropiedadServicio($id);
        
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
                    'error' => 'Relación no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->delete($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Servicio desasignado de la propiedad exitosamente'
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
     * DELETE /api/propiedades-servicios/propiedad/{id}
     * Eliminar todos los servicios de una propiedad
     */
    public function deleteByPropiedad($propiedadId) {
        try {
            $count = $this->model->deleteByPropiedad($propiedadId);
            
            echo json_encode([
                'success' => true,
                'message' => "Se eliminaron {$count} servicios de la propiedad",
                'data' => [
                    'propiedad_id' => $propiedadId,
                    'eliminados' => $count
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