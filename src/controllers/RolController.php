<?php
/**
 * Controlador de Roles
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/RolSanitizer.php';
require_once SRC_PATH . 'validators/RolValidator.php';

use App\Models\Rol;
use App\Sanitizers\RolSanitizer;
use App\Validators\RolValidator;
use Exception;

class RolController
{
    /**
     * GET /api/roles
     */
    public function index()
    {
        try {
            $roles = Rol::getAll();
            
            echo json_encode([
                'success' => true,
                'data' => $roles,
                'total' => count($roles)
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
     * GET /api/roles/con-usuarios
     */
    public function indexWithCount()
    {
        try {
            $roles = Rol::getAllWithCount();
            
            echo json_encode([
                'success' => true,
                'data' => $roles,
                'total' => count($roles)
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
     * GET /api/roles/default
     */
    public function getDefault()
    {
        try {
            $rol = Rol::getDefaultRol();
            
            if ($rol) {
                echo json_encode([
                    'success' => true,
                    'data' => $rol
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se encontró un rol por defecto'
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
     * GET /api/roles/{id}
     */
    public function show($id)
    {
        try {
            $validacion = RolValidator::validarSoloId($id);
            if (!$validacion['success']) {
                http_response_code(400);
                echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $rol = Rol::getById($id);
            
            if ($rol) {
                echo json_encode([
                    'success' => true,
                    'data' => $rol
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Rol no encontrado'
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
     * POST /api/roles
     */
    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos inválidos'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // 1. SANITIZAR
        $datosSanitizados = RolSanitizer::sanitizar($data);
        
        // 2. VALIDAR
        $errores = RolValidator::validarCrear($datosSanitizados);
        
        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $errores
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            // Verificar si ya existe un rol con el mismo nombre
            if (Rol::existsByNombre($datosSanitizados['nombre'])) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe un rol con este nombre'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $id = Rol::createRol($datosSanitizados);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Rol creado exitosamente',
                'data' => ['id' => $id]
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
     * PUT /api/roles/{id}
     */
    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos inválidos'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $data['id'] = $id;
        
        // 1. SANITIZAR
        $datosSanitizados = RolSanitizer::sanitizar($data);
        
        // 2. VALIDAR
        $errores = RolValidator::validarActualizar($datosSanitizados);
        
        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $errores
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            if (!Rol::exists($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Rol no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar si ya existe otro rol con el mismo nombre
            if (Rol::existsByNombre($datosSanitizados['nombre'], $id)) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe otro rol con este nombre'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            Rol::updateRol($id, $datosSanitizados);
            
            echo json_encode([
                'success' => true,
                'message' => 'Rol actualizado exitosamente'
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
     * DELETE /api/roles/{id}
     */
    public function delete($id)
    {
        $validacion = RolValidator::validarSoloId($id);
        
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            if (!Rol::exists($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Rol no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $rol = Rol::find($id);
            
            // Verificar si tiene usuarios asociados
            if ($rol && $rol->hasUsuarios()) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se puede eliminar el rol porque tiene usuarios asociados'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            Rol::deleteRol($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Rol eliminado exitosamente'
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