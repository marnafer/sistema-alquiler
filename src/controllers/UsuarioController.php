<?php
/**
 * Controlador de Usuarios
 * Maneja API JSON y vistas HTML.
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/UsuarioSanitizer.php';
require_once SRC_PATH . 'validators/UsuarioValidator.php';

use App\Models\Usuario;

class UsuarioController {
    
    private $model;
    
    public function __construct() {
        $this->model = new Usuario();
    }
    
    /**
     * GET /api/usuarios
     */
    public function listarUsuariosApi() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $usuarios = $this->model->obtenerTodos();
            echo json_encode([
                'success' => true,
                'data' => $usuarios,
                'total' => count($usuarios)
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
     * GET /api/usuarios/{id}
     */
    public function mostrar($id) {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $validacion = validarSoloIdUsuario($id);
            if (!$validacion['success']) {
                http_response_code(400);
                echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $usuario = $this->model->obtenerPorId($id);
            
            if ($usuario) {
                echo json_encode([
                    'success' => true,
                    'data' => $usuario
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Usuario no encontrado'
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
     * GET /api/usuarios/rol/{rolId}
     */
    public function obtenerPorRol($rolId) {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $usuarios = $this->model->obtenerPorRol($rolId);
            echo json_encode([
                'success' => true,
                'data' => $usuarios,
                'total' => count($usuarios),
                'rol_id' => $rolId
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
     * POST /api/usuarios
     */
    public function guardar() {
        header('Content-Type: application/json; charset=utf-8');
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
        $datosSanitizados = sanitizarUsuario($data);
        
        // Validar
        $validacion = validarCrearUsuario($datosSanitizados);
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            // Verificar si ya existe el email
            if ($this->model->existePorEmail($datosSanitizados['email'])) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe un usuario con este email'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Hashear contraseña
            $datosSanitizados['contrasena'] = password_hash($datosSanitizados['contrasena'], PASSWORD_DEFAULT);
            
            $id = $this->model->crear($datosSanitizados);
            unset($datosSanitizados['contrasena']);
            $datosSanitizados['id'] = $id;
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
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
     * PUT /api/usuarios/{id}
     */
    public function actualizar($id) {
        header('Content-Type: application/json; charset=utf-8');
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
        
        // Verificar si se actualiza contraseña
        $requerirContrasena = isset($data['contrasena']) && !empty($data['contrasena']);
        
        // Sanitizar
        $datosSanitizados = sanitizarUsuario($data);
        
        // Validar
        $validacion = validarActualizarUsuario($datosSanitizados, $requerirContrasena);
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            if (!$this->model->existe($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Usuario no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar email único
            if (isset($datosSanitizados['email']) && $this->model->existePorEmail($datosSanitizados['email'], $id)) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe otro usuario con este email'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Hashear contraseña si se actualiza
            if ($requerirContrasena) {
                $datosSanitizados['contrasena'] = password_hash($datosSanitizados['contrasena'], PASSWORD_DEFAULT);
            }
            
            $this->model->actualizar($id, $datosSanitizados);
            
            // Eliminar contraseña de la respuesta
            unset($datosSanitizados['contrasena']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
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
     * DELETE /api/usuarios/{id}
     */
    public function eliminar($id) {
        header('Content-Type: application/json; charset=utf-8');
        $validacion = validarSoloIdUsuario($id);
        
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            if (!$this->model->existe($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Usuario no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->eliminar($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
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
     * POST /api/usuarios/login
     */
    public function login() {
        header('Content-Type: application/json; charset=utf-8');
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['email']) || !isset($data['contrasena'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Email y contraseña son requeridos'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Sanitizar email
        $email = sanitizarSoloEmail($data['email']);
        $contrasena = $data['contrasena'];
        
        // Validar email
        $validacion = validarEmailLoginUsuario($email);
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $usuario = $this->model->verificarCredenciales($email, $contrasena);
            
            if ($usuario) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Login exitoso',
                    'data' => $usuario
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Email o contraseña incorrectos'
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
     * POST /api/usuarios/restaurar/{id}
     */
    public function restaurar($id) {
        header('Content-Type: application/json; charset=utf-8');
        $validacion = validarSoloIdUsuario($id);
        
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $this->model->restaurar($id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Usuario restaurado exitosamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // Vistas HTML (en español para el router)
    public function listarUsuarios() {
        if (file_exists(SRC_PATH . 'views/usuarios_views/usuarios_listar.php')) {
            $usuarios = $this->model->obtenerTodos();
            require_once SRC_PATH . 'views/usuarios_views/usuarios_listar.php';
            return;
        }
        $this->listarUsuariosApi();
    }

    public function mostrarFormulario() {
        $datos = [];
        $errores = [];
        if (file_exists(SRC_PATH . 'views/usuarios_views/usuarios_registrar.php')) {
            require_once SRC_PATH . 'views/usuarios_views/usuarios_registrar.php';
            return;
        }
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Vista de registro no encontrada'], JSON_UNESCAPED_UNICODE);
    }
}