<?php
/**
 * Controlador del módulo de Consultas
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/ConsultaSanitizer.php';
require_once SRC_PATH . 'validators/ConsultaValidator.php';

use App\Models\Consulta;
use App\Models\Propiedad;
use App\Models\Usuario;
use App\Controllers\ConsultaSanitizer;
use App\Validators\ConsultaValidator;

class ConsultaController {
    
    /**
     * Listar todas las consultas
     * GET /api/consultas
     */

    public function listar() {
        try {
            $consultas = Consulta::all();
            
            echo json_encode([
                'success' => true,
                'data' => $consultas,
                'total' => $consultas->count()
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
     * Obtener una consulta específica
     * GET /api/consultas/{id}
     */
    public function obtener($id) {
        try {
            $resultadoId = validarConsultaId($id);

            if (!$resultadoId['success']) {
                http_response_code(400);
                echo json_encode($resultadoId, JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $consulta = Consulta::find($id);
            
            if ($consulta) {
                echo json_encode([
                    'success' => true,
                    'data' => $consulta
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Consulta no encontrada'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Listar consultas por propiedad
     * GET /api/consultas/propiedad/{id}
     */
    public function listarPorPropiedad($propiedadId) {
    try {
        if (!Propiedad::find($propiedadId)) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Propiedad no encontrada'
            ]);
            return;
        }

        $consultas = Consulta::where('propiedad_id', $propiedadId)->get();

        echo json_encode([
            'success' => true,
            'data' => $consultas,
            'total' => $consultas->count(),
            'propiedad_id' => $propiedadId
        ]);
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
    
    /**
     * Listar consultas por inquilino
     * GET /api/consultas/inquilino/{id}
     */
    public function listarPorInquilino($inquilinoId) {
        try {            
            $inquilino = Usuario::where('id', $inquilinoId)  // Valida si existe y tiene rol de inquilino
                ->where('rol_id', 2) // rol 2 = inquilino
                ->first();

            if (!$inquilino) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'El inquilino no existe o no es válido'
                ]);
                return;
            }

            $consultas = Consulta::where('inquilino_id', $inquilinoId)->get();
            
            echo json_encode([
                'success' => true,
                'data' => $consultas,
                'total' => $consultas->count(),
                'inquilino_id' => $inquilinoId
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
     * Crear una nueva consulta
     * POST /api/consultas
     */
    public function crear() {
    header('Content-Type: application/json; charset=utf-8');

    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    if (!$data) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Datos inválidos o no proporcionados'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    // Validación
    $resultadoValidacion = validarConsulta($data);

    if (!$resultadoValidacion['success']) {
        http_response_code(400);
        echo json_encode($resultadoValidacion, JSON_UNESCAPED_UNICODE);
        return;
    }

    $dataValida = $resultadoValidacion['data'];

    try {
        // Verificar propiedad (y opcionalmente que esté disponible)
        $propiedad = Propiedad::where('id', $dataValida['propiedad_id'])
            ->whereNull('deleted_at') // soft delete
            ->first();

        if (!$propiedad) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'La propiedad no existe o no está disponible'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Verificar inquilino (según rol)
        $inquilino = Usuario::where('id', $dataValida['inquilino_id'])
            ->where('rol_id', 2) // rol 2 = inquilino
            ->whereNull('deleted_at') // opcional
            ->first();

        if (!$inquilino) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'El inquilino no existe o no es válido'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Crear consulta con Eloquent
        $consulta = Consulta::create([
            'propiedad_id'   => $dataValida['propiedad_id'],
            'inquilino_id'   => $dataValida['inquilino_id'],
            'mensaje'        => $dataValida['mensaje'],
            'fecha_consulta' => date('Y-m-d H:i:s')
        ]);

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Consulta creada exitosamente',
            'data' => $consulta
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
 * Actualizar una consulta
 * PUT /api/consultas/{id}
 */
public function actualizar($id) {
    header('Content-Type: application/json; charset=utf-8');

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
    $resultadoValidacion = validarConsulta($data, true);

    if (!$resultadoValidacion['success']) {
        http_response_code(400);
        echo json_encode($resultadoValidacion, JSON_UNESCAPED_UNICODE);
        return;
    }

    $dataValida = $resultadoValidacion['data'];

    try {
        // Buscar consulta
        $consulta = Consulta::find($id);

        if (!$consulta) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Consulta no encontrada'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validar que la propiedad existe
        if (!Propiedad::where('id', $dataValida['propiedad_id'])->exists()) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'La propiedad no existe'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validar que el usuario es inquilino
        if (!Usuario::where('id', $dataValida['inquilino_id'])
            ->where('rol_id', 2) // 👈 ajustá según tu sistema
            ->exists()) {

            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'El inquilino no existe o no es válido'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Actualizar con Eloquent
        $consulta->update([
            'propiedad_id' => $dataValida['propiedad_id'],
            'inquilino_id' => $dataValida['inquilino_id'],
            'mensaje' => $dataValida['mensaje']
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Consulta actualizada exitosamente',
            'data' => $consulta // devuelve el modelo actualizado
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
 * Eliminar una consulta (soft delete)
 * DELETE /api/consultas/{id}
 */
public function eliminar($id) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        // Validar ID
        $resultadoId = validarConsultaId($id);

        if (!$resultadoId['success']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $resultadoId['error']
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Buscar consulta
        $consulta = \App\Models\Consulta::find($id);

        if (!$consulta) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Consulta no encontrada'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Eliminamos consulta
        $consulta->delete();

        echo json_encode([
            'success' => true,
            'message' => 'Consulta eliminada exitosamente'
        ], JSON_UNESCAPED_UNICODE);

    } catch (\Exception $e) {2
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}
}