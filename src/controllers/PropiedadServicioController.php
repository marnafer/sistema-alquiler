<?php
/**
 * Controlador de PropiedadServicio
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/PropiedadServicioSanitizer.php';
require_once SRC_PATH . 'validators/PropiedadServicioValidator.php';

use App\Models\PropiedadServicio;
use Exception;

class PropiedadServicioController {
    
    private $model;
    
    public function __construct() {
        $this->model = new PropiedadServicio();
        header('Content-Type: application/json');
    }
    
    /**
     * GET /api/propiedades-servicios
     */
    public function index()
    {
        try {
            $relaciones = $this->model->getAll();
            echo json_encode([
                'success' => true,
                'data' => $relaciones,
                'total' => count($relaciones)
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
     * GET /api/propiedades-servicios/estadisticas
     */
    public function getEstadisticas()
    {
        try {
            $estadisticas = PropiedadServicio::getEstadisticas();
            
            echo json_encode([
                'success' => true,
                'data' => $estadisticas
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
     * GET /api/propiedades-servicios/propiedad/{id}
     */
    public function getByPropiedad($propiedadId)
    {
        try {
            $relaciones = PropiedadServicio::getByPropiedad($propiedadId);
            
            echo json_encode([
                'success' => true,
                'data' => $relaciones,
                'total' => count($relaciones),
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
     * GET /api/propiedades-servicios/servicio/{id}
     */
    public function getByServicio($servicioId)
    {
        try {
            $relaciones = PropiedadServicio::getByServicio($servicioId);
            
            echo json_encode([
                'success' => true,
                'data' => $relaciones,
                'total' => count($relaciones),
                'servicio_id' => $servicioId
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
     * POST /api/propiedades-servicios
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
        
        try {
            // Verificar si ya existe la relación
            if (PropiedadServicio::existsRelacion($data['propiedad_id'], $data['servicio_id'])) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Esta propiedad ya tiene asociado este servicio'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $relacion = PropiedadServicio::createRelacion($data);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Servicio asignado a la propiedad exitosamente',
                'data' => [
                    'id' => $relacion->id,
                    'propiedad_id' => $data['propiedad_id'],
                    'servicio_id' => $data['servicio_id']
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
            $count = PropiedadServicio::syncServiciosByPropiedad($propiedadId, $data['servicios_ids']);
            
            echo json_encode([
                'success' => true,
                'message' => "Servicios sincronizados exitosamente",
                'data' => [
                    'propiedad_id' => $propiedadId,
                    'servicios_asignados' => $count,
                    'servicios_ids' => $data['servicios_ids']
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

    /**
     * GET /api/propiedades-servicios/{id}
     */
    public function show($id)
    {
        try {
            $relacion = PropiedadServicio::getById($id);
            
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
        } catch (Exception $e) {
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
    public function delete($id)
    {
        try {
            if (!PropiedadServicio::exists($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Relación no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            PropiedadServicio::deleteRelacion($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Servicio desasignado de la propiedad exitosamente'
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
     * DELETE /api/propiedades-servicios/propiedad/{id}
     * Eliminar todos los servicios de una propiedad
     */
    public function deleteByPropiedad($propiedadId)
    {
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
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}