<?php

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/FavoritoSanitizer.php';
require_once SRC_PATH . 'validators/FavoritoValidator.php';

use App\Models\Favorito;
use App\Models\Usuario; 
use App\Models\Propiedad;
use App\Sanitizers\FavoritoSanitizer;
use App\Validators\FavoritoValidator;

class FavoritoController {

    /**
     * GET /api/usuarios/{id}/favoritos
     */
    public function listarFavoritos($usuario_id) {
    header('Content-Type: application/json; charset=utf-8');

        try {
            $favoritos = Favorito::where('usuario_id', $usuario_id)
                ->with('propiedad')
                ->get();

            echo json_encode([
                'success' => true,
                'data' => $favoritos,
                'total' => $favoritos->count()
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
     * POST /api/favoritos
     */
    public function agregarFavorito() {
        header('Content-Type: application/json');

        // Soporte para JSON o $_POST tradicional
        $inputRaw = file_get_contents("php://input");
        $inputData = json_decode($inputRaw, true) ?? $_POST;

        // 1. Sanitización y Validación
        $datosLimpios = FavoritoSanitizer::sanitizarFavorito($inputData);
        $errores = FavoritoValidator::validarFavorito($datosLimpios);

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'errors' => $errores]);
            exit;
        }

        try {
            // Verificamos si ya existe para evitar duplicados
            $existe = Favorito::where('usuario_id', $datosLimpios['usuario_id'])
                              ->where('propiedad_id', $datosLimpios['propiedad_id'])
                              ->first();
            
            if ($existe) {
                http_response_code(409); // Conflict
                echo json_encode(['status' => 'error', 'message' => 'Ya está en favoritos']);
                return;
            }

            $favorito = Favorito::create($datosLimpios);
            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Agregado a favoritos',
                'data' => $favorito
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
 * Eliminar favorito
 * DELETE /api/favoritos
 */
    public function eliminarFavorito() {
        header('Content-Type: application/json; charset=utf-8');

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // 1. Sanitizar
        $san = FavoritoSanitizer::sanitizarFavorito($data);

        // 2. Validar existencia
        $errores = FavoritoValidator::validarQuitarFavorito($san);

        if (!empty($errores)) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'errors' => $errores
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // 3. Eliminar
            Favorito::where('usuario_id', $san['usuario_id'])
                ->where('propiedad_id', $san['propiedad_id'])
                ->delete();

            echo json_encode([
                'success' => true,
                'message' => 'Favorito eliminado correctamente'
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