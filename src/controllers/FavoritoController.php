<?php

namespace App\Controllers;

use App\Models\Favorito;
use App\Models\Usuario; // Por si se necesitan cargar datos del usuario
use App\Sanitizers\FavoritoSanitizer;
use App\Validators\FavoritoValidator;

class FavoritosController {

    /**
     * GET /favoritos
     * Muestra la vista HTML (Frontend)
     */
    public function listar_Favoritos() {
        // Obtenemos los favoritos del usuario actual (usando Eloquent)
        $usuario_id = $_SESSION['user_id'] ?? null;

        if (!$usuario_id) {
            header('Location: /login'); // Seguridad básica
            exit;
        }

        $misFavoritos = Favorito::where('usuario_id', $usuario_id)->with('propiedad')->get();

        $tituloPagina = "Mis Favoritos";
        require_once SRC_PATH . 'views/favoritos/favoritos_lista.php';
    }

    /**
     * POST /api/favoritos
     */
    public function agregar() {
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
     * DELETE /api/favoritos/{id}
     */
    public function quitar($id) {
        header('Content-Type: application/json');

        try {
            $favorito = Favorito::find($id);

            if (!$favorito) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'El favorito no existe']);
                return;
            }

            // Seguridad: El usuario solo puede borrar sus propios favoritos
            if ($favorito->usuario_id != ($_SESSION['user_id'] ?? 0)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'No tienes permiso']);
                return;
            }

            $favorito->delete();

            echo json_encode([
                'status' => 'success',
                'message' => "Favorito #$id eliminado correctamente"
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}