<?php
/**
 * Controlador del módulo de Categorías
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/categoria_sanitizer.php';

use App\Models\Categoria;

class CategoriaController {
    
    private $model;
    
    public function __construct() {
        $this->model = new Categoria();
        header('Content-Type: application/json');
    }
    
    /**
     * Listar todas las categorías (API)
     * GET /api/categorias
     */
    public function listar() {
        try {
            $categorias = $this->model->listar();
            echo json_encode([
                'success' => true,
                'data' => $categorias,
                'total' => count($categorias)
            ]);
        } catch (\Exception $e) {  // ← Agregar \ antes de Exception
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtener una categoría específica (API)
     * GET /api/categorias/{id}
     */
    public function obtener($id) {
        try {
            $categoria = $this->model->obtener($id);
            if ($categoria) {
                echo json_encode([
                    'success' => true,
                    'data' => $categoria
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
                ]);
            }
        } catch (\Exception $e) {  // ← Agregar \ antes de Exception
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Crear una nueva categoría (API)
     * POST /api/categorias
     */
    public function crear() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos inválidos o no proporcionados'
            ]);
            return;
        }
        
        $resultado = validarCategoria($data, false);
        
        if (!$resultado['success']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'errors' => $resultado['errors']
            ]);
            return;
        }
        
        try {
            // Verificar si ya existe
            if ($this->model->existeNombre($resultado['data']['nombre'])) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe una categoría con este nombre'
                ]);
                return;
            }
            
            $id = $this->model->crear($resultado['data']);
            $resultado['data']['id'] = $id;
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'data' => $resultado['data']
            ]);
        } catch (\Exception $e) {  // ← Agregar \ antes de Exception
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Actualizar una categoría (API)
     * PUT /api/categorias/{id}
     */
    public function actualizar($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos inválidos o no proporcionados'
            ]);
            return;
        }
        
        $data['id'] = $id;
        $resultado = validarCategoria($data, true);
        
        if (!$resultado['success']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'errors' => $resultado['errors']
            ]);
            return;
        }
        
        try {
            // Verificar si existe
            if (!$this->model->existe($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
                ]);
                return;
            }
            
            // Verificar si el nombre ya existe en otra categoría
            if ($this->model->existeNombreExcepto($resultado['data']['nombre'], $id)) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe otra categoría con este nombre'
                ]);
                return;
            }
            
            $this->model->actualizar($id, $resultado['data']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente',
                'data' => $resultado['data']
            ]);
        } catch (\Exception $e) {  // ← Agregar \ antes de Exception
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Eliminar una categoría (API)
     * DELETE /api/categorias/{id}
     */
    public function eliminar($id) {
        try {
            // Verificar si existe
            if (!$this->model->existe($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
                ]);
                return;
            }
            
            // Verificar si tiene propiedades asociadas
            if ($this->model->tienePropiedadesAsociadas($id)) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se puede eliminar la categoría porque tiene propiedades asociadas'
                ]);
                return;
            }
            
            $this->model->eliminar($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría eliminada exitosamente'
            ]);
        } catch (\Exception $e) {  // ← Agregar \ antes de Exception
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Listar categorías para VISTA (también JSON)
     * GET /categorias
     */
    public function listarVista() {
        try {
            $categorias = $this->model->listar();
            
            echo json_encode([
                'success' => true,
                'view' => 'categorias',
                'data' => $categorias,
                'total' => count($categorias),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {  // ← Agregar \ antes de Exception
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}