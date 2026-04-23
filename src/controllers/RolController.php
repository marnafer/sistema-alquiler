<?php
/**
 * Controlador de Roles
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/rol_sanitizer.php';
require_once SRC_PATH . 'validators/rol_validator.php';

use App\Models\Rol;

class RolController {
    
    private $model;
    
    public function __construct() {
        $this->model = new Rol();
        header('Content-Type: application/json');
    }
    
    /**
     * GET /api/roles
     */
    public function index() {
        try {
            $roles = $this->model->getAll();
            echo json_encode([
                'success' => true,
                'data' => $roles,
                'total' => count($roles)
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
     * GET /api/roles/con-usuarios
     */
    public function indexWithCount() {
        try {
            $roles = $this->model->getAllWithCount();
            echo json_encode([
                'success' => true,
                'data' => $roles,
                'total' => count($roles)
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
     * GET /api/roles/{id}
     */
    public function show($id) {
        try {
            $validacion = validarSoloIdRol($id);
            if (!$validacion['success']) {
                http_response_code(400);
                echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $rol = $this->model->getById($id);
            
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
        } catch (\Exception $e) {
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
    public function getDefault() {
        try {
            $rol = $this->model->getDefaultRol();
            
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
        } catch (\Exception $e) {
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
    public function store() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos inválidos'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Sanitizar
        $datosSanitizados = sanitizarRol($data);
        
        // Validar
        $validacion = validarCrearRol($datosSanitizados);
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            // Verificar si ya existe un rol con el mismo nombre
            if ($this->model->existsByNombre($datosSanitizados['nombre'])) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe un rol con este nombre'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $id = $this->model->create($datosSanitizados);
            $datosSanitizados['id'] = $id;
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Rol creado exitosamente',
                'data' => $datosSanitizados
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
     * PUT /api/roles/{id}
     */
    public function update($id) {
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
        $datosSanitizados = sanitizarRol($data);
        
        $validacion = validarActualizarRol($datosSanitizados);
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            if (!$this->model->exists($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Rol no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar si ya existe otro rol con el mismo nombre
            if ($this->model->existsByNombre($datosSanitizados['nombre'], $id)) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe otro rol con este nombre'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->update($id, $datosSanitizados);
            
            echo json_encode([
                'success' => true,
                'message' => 'Rol actualizado exitosamente',
                'data' => $datosSanitizados
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
     * DELETE /api/roles/{id}
     */
    public function delete($id) {
        $validacion = validarSoloIdRol($id);
        
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            if (!$this->model->exists($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Rol no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar si tiene usuarios asociados
            if ($this->model->hasUsuarios($id)) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se puede eliminar el rol porque tiene usuarios asociados'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->delete($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Rol eliminado exitosamente'
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