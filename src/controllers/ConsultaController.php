<?php
/**
 * Controlador del módulo de Consultas
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/ConsultaSanitizer.php';
require_once SRC_PATH . 'validators/ConsultaValidator.php';
require_once SRC_PATH . 'middlewares/AutenticadorMiddleware.php';

use App\Models\Consulta;
use App\Models\Propiedad;
use App\Models\Usuario;
use App\Controllers\ConsultaSanitizer;
use App\Validators\ConsultaValidator;
use App\Middlewares\AutenticadorMiddleware;
use SoftDeletes;

class ConsultaController {
    
    /**
     * Listar todas las consultas
     * GET /api/consultas
     */

   public function listar() {

        AutenticadorMiddleware::soloAdmin();

        try {
            $consultas = Consulta::all();

            renderJson([
                'success' => true,
                'data' => $consultas,
                'total' => $consultas->count()
            ], 200);

        } catch (\Exception $e) {
            renderJson(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Obtener una consulta específica
     * GET /api/consultas/{id}
     */
   public function obtener($id) {

        $user = AutenticadorMiddleware::verificar();

        try {
            $consulta = Consulta::find($id);

            if (!$consulta) {
                return renderJson(['error' => 'No encontrada'], 404);
            }

            // REGLA DE ACCESO
            if ($user->rol != 3 && $consulta->inquilino_id != $user->sub) {
                return renderJson(['error' => 'No autorizado'], 403);
            }

            renderJson([
                'success' => true,
                'data' => $consulta
            ], 200);

        } catch (\Exception $e) {
            renderJson(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Listar consultas por propiedad
     * GET /api/consultas/propiedad/{id}
     */
    public function listarPorPropiedad($propiedadId) {

        $user = AutenticadorMiddleware::verificar();

        try {

            $propiedad = Propiedad::find($propiedadId);

            if (!$propiedad) {
                return renderJson(['error' => 'Propiedad no encontrada'], 404);
            }

            // propietario dueño o admin
            if ($user->rol != 3 && $propiedad->usuario_id != $user->sub) {
                return renderJson(['error' => 'No autorizado'], 403);
            }

            $consultas = Consulta::where('propiedad_id', $propiedadId)->get();

            renderJson([
                'success' => true,
                'data' => $consultas,
                'total' => $consultas->count()
            ], 200);

        } catch (\Exception $e) {
            renderJson(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Listar consultas por inquilino
     * GET /api/consultas/inquilino/{id}
     */
    public function listarPorInquilino($inquilinoId) {

        $user = AutenticadorMiddleware::verificar();

        try {

            if ($user->rol != 3 && $user->sub != $inquilinoId) {
                return renderJson(['error' => 'No autorizado'], 403);
            }

            $consultas = Consulta::where('inquilino_id', $inquilinoId)->get();

            renderJson([
                'success' => true,
                'data' => $consultas,
                'total' => $consultas->count()
            ], 200);

        } catch (\Exception $e) {
            renderJson(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Crear una nueva consulta
     * POST /api/consultas
     */
    public function crear() {

        $user = AutenticadorMiddleware::soloInquilino();

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (!$data) {
            return renderJson(['error' => 'Datos inválidos'], 400);
        }

        $resultadoValidacion = validarConsulta($data);

        if (!$resultadoValidacion['success']) {
            return renderJson($resultadoValidacion, 400);
        }

        $dataValida = $resultadoValidacion['data'];

        try {

            $propiedad = Propiedad::find($dataValida['propiedad_id']);

            if (!$propiedad) {
                return renderJson(['error' => 'Propiedad no existe'], 404);
            }

            // INQUILINO REAL = TOKEN (NO DEL BODY)
            $consulta = Consulta::create([
                'propiedad_id'   => $dataValida['propiedad_id'],
                'inquilino_id'   => $user->sub,
                'mensaje'        => $dataValida['mensaje'],
                'fecha_consulta' => date('Y-m-d H:i:s')
            ]);

            renderJson([
                'success' => true,
                'message' => 'Consulta creada',
                'data' => $consulta
            ], 201);

        } catch (\Exception $e) {
            renderJson(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
 * Actualizar una consulta
 * PUT /api/consultas/{id}
 */
public function actualizar($id) {

        $user = AutenticadorMiddleware::verificar();

        $consulta = Consulta::find($id);

        if (!$consulta) {
            return renderJson(['error' => 'No existe'], 404);
        }

        if ($user->rol != 3 && $consulta->inquilino_id != $user->sub) {
            return renderJson(['error' => 'No autorizado'], 403);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $consulta->update($data);

        renderJson([
            'success' => true,
            'message' => 'Actualizada',
            'data' => $consulta
        ]);
    }
    
    /**
 * Eliminar una consulta (soft delete)
 * DELETE /api/consultas/{id}
 */
public function eliminar($id) {

        AutenticadorMiddleware::soloAdmin();

        $consulta = Consulta::find($id);

        if (!$consulta) {
            return renderJson(['error' => 'No existe'], 404);
        }

        $consulta->delete();

        renderJson([
            'success' => true,
            'message' => 'Eliminada'
        ]);
    }
}