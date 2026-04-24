<?php
/**
 * Controlador del módulo de Categorías
 * Respuestas JSON para la API; vistas HTML si se exponen
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/CategoriaSanitizer.php';
require_once SRC_PATH . 'validators/CategoriaValidator.php';

use App\Sanitizers\CategoriaSanitizer;
use App\Validators\CategoriaValidator;
use App\Models\Categoria;

class CategoriaController {

    private $model;

    public function __construct() {
        $this->model = new Categoria();
        // No forzar header global; hacerlo por método
    }

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
            if ($this->model->existeNombre($san['nombre'] ?? '')) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Ya existe una categoría con este nombre'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $id = $this->model->crear($resultado['data']);
            $resultado['data']['id'] = $id;
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Categoría creada exitosamente', 'data' => $resultado['data']], JSON_UNESCAPED_UNICODE);
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
            echo json_encode(['success' => false, 'errors' => $resultado['errors']], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            if (!$this->model->existe($id)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Categoría no encontrada'], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($this->model->existeNombreExcepto($san['nombre'], $id)) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Ya existe otra categoría con este nombre'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->model->actualizar($id, $resultado['data']);
            echo json_encode(['success' => true, 'message' => 'Categoría actualizada exitosamente', 'data' => $resultado['data']], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Eliminar una categoría (API)
     * DELETE /api/categorias/{id}
     */
    public function eliminar($id) {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $idS = CategoriaSanitizer::sanitizeId($id);
            $validacion = CategoriaValidator::validarCategoria(['id' => $idS], true);
            if (!$validacion['success']) {
                http_response_code(400);
                echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
                return;
            }

            if (!$this->model->existe($idS)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Categoría no encontrada'], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($this->model->tienePropiedadesAsociadas($idS)) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'No se puede eliminar la categoría porque tiene propiedades asociadas'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->model->eliminar($idS);
            echo json_encode(['success' => true, 'message' => 'Categoría eliminada exitosamente'], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Listar categorías para VISTA (también JSON)
     * GET /categorias
     */
    public function listarVista() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $categorias = $this->model->listar();
            echo json_encode(['success' => true, 'view' => 'categorias', 'data' => $categorias, 'total' => count($categorias), 'timestamp' => date('Y-m-d H:i:s')], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }
}