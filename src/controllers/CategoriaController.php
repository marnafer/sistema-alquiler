<?php
/**
 * Controlador del módulo de Categorías
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/CategoriaSanitizer.php';
require_once SRC_PATH . 'validators/CategoriaValidator.php';

use App\Sanitizers\CategoriaSanitizer;
use App\Validators\CategoriaValidator;
use App\Models\Categoria;

class CategoriaController {

    /**
     * Listar todas las categorías (API)
     * GET /api/categorias
     */
    public function listar() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $categorias = Categoria::all();
            echo json_encode([
                'success' => true,
                'data' => $categorias,
                'total' => count($categorias)
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
     * Obtener una categoría específica (API)
     * GET /api/categorias/{id}
     */
    public function obtener($id) {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $idS = CategoriaSanitizer::sanitizeId($id);
            $validacion = CategoriaValidator::validarId($idS);
            if (!$validacion['success']) {
                http_response_code(400);
                echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
                return;
            }

            $categoria = Categoria::find($idS);
            if ($categoria) {
                echo json_encode(['success' => true, 'data' => $categoria], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Categoría no encontrada'], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Crear una nueva categoría (API)
     * POST /api/categorias
     */
    public function crear() {
        header('Content-Type: application/json; charset=utf-8');
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // Sanitizar primero
        $san = CategoriaSanitizer::sanitizarCategoria($data);
        $resultado = CategoriaValidator::validarCategoria($san, false);

        if (!$resultado['success']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $resultado['errors']], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // Verificar si ya existe por nombre (supone método en modelo)
            if (Categoria::where('nombre', $san['nombre'])->exists()) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Ya existe una categoría con este nombre'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $categoria = Categoria::create($resultado['data']);
           
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Categoría creada exitosamente', 'data' => $categoria], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Actualizar una categoría (API)
     * PUT /api/categorias/{id}
     */
    public function actualizar($id) {
        header('Content-Type: application/json; charset=utf-8');

        $raw = json_decode(file_get_contents('php://input'), true) ?? [];
        $raw['id'] = $id;

        $san = CategoriaSanitizer::sanitizarCategoria($raw);
        $resultado = CategoriaValidator::validarCategoria($san, true);

        if (!$resultado['success']) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'errors' => $resultado['errors']
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $id = $resultado['data']['id'];

            $categoria = Categoria::find($id);

            if (!$categoria) {
                http_response_code(404);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Categoría no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Validar nombre duplicado
            if (Categoria::where('nombre', $resultado['data']['nombre'])
                ->where('id', '!=', $id)
                ->exists()) {

                http_response_code(409);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Ya existe otra categoría con este nombre'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $categoria->update($resultado['data']);

            echo json_encode([
                'success' => true, 
                'message' => 'Categoría actualizada exitosamente', 
                'data' => $categoria // devolver el modelo
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
     * Eliminar una categoría (API)
     * DELETE /api/categorias/{id}
     */
    public function eliminar($id) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        // Sanitizar ID
        $idS = CategoriaSanitizer::sanitizeId($id);

        // Validar SOLO el ID (no toda la categoría)
        $validacion = CategoriaValidator::validarId($idS);

        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $validacion['error']
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Buscar con Eloquent
        $categoria = Categoria::find($idS);

        if (!$categoria) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Categoría no encontrada'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validar relaciones
        if ($categoria->propiedades()->exists()) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'No se puede eliminar la categoría porque tiene propiedades asociadas'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Eliminar con Eloquent
        $categoria->delete();

        echo json_encode([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente'
        ], JSON_UNESCAPED_UNICODE);

    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}

