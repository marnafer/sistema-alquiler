<?php

namespace App\Controllers;

use App\Models\Localidad;
use App\Sanitizers\LocalidadSanitizer;
use App\Validators\LocalidadValidator;

class LocalidadController
{
    /**
     * VISTA HTML: listado de localidades
     * Ruta esperada: /localidades (GET)
     */
    public function listarLocalidades()
    {
        try {
            $localidades = Localidad::all();
            require_once SRC_PATH . 'views/localidades/localidades_listar.php';
        } catch (\Exception $e) {
            die("Error al listar localidades: " . $e->getMessage());
        }
    }

    /**
     * API: Listar (GET /api/localidades)
     */
    public function indexApi()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $localidades = Localidad::all();
            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => $localidades], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Mostrar (GET /api/localidades/{id})
     */
    public function mostrarApi($id)
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = LocalidadSanitizer::sanitizarId($id);
        if (!LocalidadValidator::validarId($id)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            return;
        }

        try {
            $localidad = Localidad::find($id);
            if (!$localidad) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Localidad no encontrada']);
                return;
            }
            echo json_encode(['status' => 'success', 'data' => $localidad], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Crear (POST /api/localidades)
     * Acepta JSON crudo o form-data
     */
    public function crear()
    {
        header('Content-Type: application/json; charset=utf-8');

        $inputRaw = file_get_contents('php://input');
        $input = json_decode($inputRaw, true) ?? $_POST;

        $datos = LocalidadSanitizer::sanitizarLocalidad($input);
        $errores = LocalidadValidator::validarLocalidad($datos);

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'errors' => $errores], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $localidad = Localidad::create($datos);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Localidad creada', 'data' => ['id' => $localidad->id]], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Actualizar (PUT /api/localidades/{id})
     * Espera payload completo o parcial en JSON
     */
    public function actualizar($id)
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = LocalidadSanitizer::sanitizarId($id);
        if (!LocalidadValidator::validarId($id)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            return;
        }

        $localidad = Localidad::find($id);
        if (!$localidad) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Localidad no encontrada']);
            return;
        }

        $inputRaw = file_get_contents('php://input');
        $input = json_decode($inputRaw, true) ?? $_POST;

        $datos = LocalidadSanitizer::sanitizarLocalidad($input);
        $errores = LocalidadValidator::validarLocalidad($datos, $isUpdate = true);

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'errors' => $errores], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $localidad->fill($datos);
            $localidad->save();
            echo json_encode(['status' => 'success', 'message' => 'Localidad actualizada', 'data' => ['id' => $localidad->id]], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Eliminar (DELETE /api/localidades/{id})
     */
    public function eliminar($id)
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = LocalidadSanitizer::sanitizarId($id);
        if (!LocalidadValidator::validarId($id)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            return;
        }

        try {
            $localidad = Localidad::find($id);
            if (!$localidad) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Localidad no encontrada']);
                return;
            }
            $localidad->delete();
            echo json_encode(['status' => 'success', 'message' => "Localidad #$id eliminada"]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}