<?php

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/FavoritoSanitizer.php';
require_once SRC_PATH . 'validators/FavoritoValidator.php';

use App\Models\Favorito;
use App\Models\Usuario; 
use App\Models\Propiedad;
use App\Sanitizers\FavoritoSanitizer;
use App\Validators\FavoritoValidator;
use Exception;

class FavoritoController {

    /**
     * GET /api/favoritos
     * Listar todos los favoritos (opcional)
     */
    public function listarTodos() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $favoritos = Favorito::with(['usuario', 'propiedad'])->get();

            echo json_encode([
                'success' => true,
                'data' => $favoritos,
                'total' => $favoritos->count()
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
     * GET /api/usuarios/{id}/favoritos
     * Listar favoritos por usuario
     */
    public function listarFavoritos($usuario_id) {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Verificar que el usuario existe
            $usuario = Usuario::find($usuario_id);
            if (!$usuario) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Usuario no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $favoritos = Favorito::where('usuario_id', $usuario_id)
                ->with('propiedad')
                ->get();

            echo json_encode([
                'success' => true,
                'data' => $favoritos,
                'total' => $favoritos->count()
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
     * POST /api/favoritos
     * Agregar favorito
     */
    public function agregarFavorito() {
        header('Content-Type: application/json; charset=utf-8');

        // Soporte para JSON o $_POST tradicional
        $inputRaw = file_get_contents("php://input");
        $inputData = json_decode($inputRaw, true) ?? $_POST;

        // 1. Sanitización
        $datosLimpios = FavoritoSanitizer::sanitizarFavorito($inputData);
        
        // 2. Validación
        $errores = FavoritoValidator::validarFavorito($datosLimpios);

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'errors' => $errores
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // Verificar que el usuario existe
            $usuario = Usuario::find($datosLimpios['usuario_id']);
            if (!$usuario) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Usuario no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Verificar que la propiedad existe
            $propiedad = Propiedad::find($datosLimpios['propiedad_id']);
            if (!$propiedad) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Propiedad no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Verificar si ya existe para evitar duplicados
            $existe = Favorito::where('usuario_id', $datosLimpios['usuario_id'])
                              ->where('propiedad_id', $datosLimpios['propiedad_id'])
                              ->first();
            
            if ($existe) {
                http_response_code(409);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Ya está en favoritos'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $favorito = Favorito::create($datosLimpios);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Agregado a favoritos',
                'data' => $favorito
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
     * DELETE /api/favoritos
     * Eliminar favorito (por usuario_id y propiedad_id)
     */
    public function eliminarFavorito() {
        header('Content-Type: application/json; charset=utf-8');

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // 1. Sanitizar
        $datosLimpios = FavoritoSanitizer::sanitizarFavorito($data);

        // 2. Validar existencia
        $errores = FavoritoValidator::validarQuitarFavorito($datosLimpios);

        if (!empty($errores)) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'errors' => $errores
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // Verificar que existe el favorito
            $existe = Favorito::where('usuario_id', $datosLimpios['usuario_id'])
                ->where('propiedad_id', $datosLimpios['propiedad_id'])
                ->first();

            if (!$existe) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Favorito no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Eliminar
            Favorito::where('usuario_id', $datosLimpios['usuario_id'])
                ->where('propiedad_id', $datosLimpios['propiedad_id'])
                ->delete();

            echo json_encode([
                'success' => true,
                'message' => 'Favorito eliminado correctamente'
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
     * DELETE /api/favoritos/{id}
     * Eliminar favorito por ID
     */
    public function eliminarFavoritoPorId($id) {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $favorito = Favorito::find($id);

            if (!$favorito) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Favorito no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $favorito->delete();

            echo json_encode([
                'success' => true,
                'message' => 'Favorito eliminado exitosamente'
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