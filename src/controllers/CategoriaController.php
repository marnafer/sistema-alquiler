<?php
/**
 * Controlador del módulo de Categorías
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/CategoriaSanitizer.php';
require_once SRC_PATH . 'validators/CategoriaValidator.php';

use App\Models\Categoria;
use Exception;

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
            // Eloquent: all() en lugar de listar()
            $categorias = Categoria::all();
            echo json_encode([
                'success' => true,
                'data' => $categorias,
                'total' => count($categorias)
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
     * Obtener una categoría específica (API)
     * GET /api/categorias/{id}
     */
    public function obtener($id) {
        try {
            // Eloquent: find() en lugar de obtener()
            $categoria = Categoria::find($id);
            if ($categoria) {
                echo json_encode([
                    'success' => true,
                    'data' => $categoria
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
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
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $resultado = validarCategoria($data, false);
        
        if (!$resultado['success']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'errors' => $resultado['errors']
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            // Verificar si ya existe usando Eloquent
            $existe = Categoria::where('nombre', $resultado['data']['nombre'])->exists();
            if ($existe) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe una categoría con este nombre'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Eloquent: create()
            $categoria = Categoria::create($resultado['data']);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'data' => $categoria
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
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $data['id'] = $id;
        $resultado = validarCategoria($data, true);
        
        if (!$resultado['success']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'errors' => $resultado['errors']
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            // Eloquent: find()
            $categoria = Categoria::find($id);
            
            if (!$categoria) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar si el nombre ya existe en otra categoría
            $existe = Categoria::where('nombre', $resultado['data']['nombre'])
                              ->where('id', '!=', $id)
                              ->exists();
            if ($existe) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe otra categoría con este nombre'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Eloquent: update()
            $categoria->update($resultado['data']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente',
                'data' => $categoria
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
     * Eliminar una categoría (API)
     * DELETE /api/categorias/{id}
     */
    public function eliminar($id) {
        try {
            // Eloquent: find()
            $categoria = Categoria::find($id);
            
            if (!$categoria) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar si tiene propiedades asociadas usando Eloquent
            if ($categoria->propiedades()->count() > 0) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se puede eliminar la categoría porque tiene propiedades asociadas'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Eloquent: delete()
            $categoria->delete();
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría eliminada exitosamente'
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
     * Listar categorías para VISTA (también JSON)
     * GET /categorias
     */
    public function listarVista() {
        try {
            // Eloquent: all()
            $categorias = Categoria::all();
            
            echo json_encode([
                'success' => true,
                'view' => 'categorias',
                'data' => $categorias,
                'total' => count($categorias),
                'timestamp' => date('Y-m-d H:i:s')
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