<?php
/**
 * Controlador de Usuarios
 * TODAS las respuestas son en JSON
 */

namespace App\Controllers;

require_once SRC_PATH . 'sanitizers/usuario_sanitizer.php';
require_once SRC_PATH . 'validators/usuario_validator.php';

use App\Models\Usuario;

class UsuarioController {
    
    private $model;
    
    public function __construct() {
        $this->model = new Usuario();
        header('Content-Type: application/json');
    }
    
    /**
     * GET /api/usuarios
     */
    public function index() {
        try {
            $usuarios = $this->model->getAll();
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
    public function show($id) {
        try {
            $validacion = validarSoloIdUsuario($id);
            if (!$validacion['success']) {
                http_response_code(400);
                echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $usuario = $this->model->getById($id);
            
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
    public function getByRol($rolId) {
        try {
            $usuarios = $this->model->getByRol($rolId);
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
    public function store() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos inválidos'
            ], JSON_UNESCAPED_UNICODE);
     * API: Procesa la creación (JSON)
     * Ruta: /api/usuarios (POST)
     */
    public function guardar() {
        header('Content-Type: application/json');

        // Soporte para JSON crudo o FormData
        $inputRaw = file_get_contents("php://input");
        $inputData = json_decode($inputRaw, true) ?? $_POST;

        // 1. Sanitización
        $datosLimpios = UsuarioSanitizer::sanitizarUsuario($inputData);
        
        // 2. Validación
        $errores = UsuarioValidator::validarUsuario($datosLimpios);

        if (!empty($errores)) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'errors' => $errores]);
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
            if ($this->model->existsByEmail($datosSanitizados['email'])) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ya existe un usuario con este email'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Hashear contraseña
            $datosSanitizados['contrasena'] = password_hash($datosSanitizados['contrasena'], PASSWORD_DEFAULT);
            
            $id = $this->model->create($datosSanitizados);
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
            if (!$this->model->exists($id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Usuario no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Verificar email único
            if (isset($datosSanitizados['email']) && $this->model->existsByEmail($datosSanitizados['email'], $id)) {
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
            
            $this->model->update($id, $datosSanitizados);
            
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
    public function delete($id) {
        $validacion = validarSoloIdUsuario($id);
        
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
                    'error' => 'Usuario no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $this->model->delete($id);
            
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
        $validacion = validarEmailLogin($email);
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $usuario = $this->model->verifyCredentials($email, $contrasena);
            
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
    public function restore($id) {
        $validacion = validarSoloIdUsuario($id);
        
        if (!$validacion['success']) {
            http_response_code(400);
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $this->model->restore($id);
            
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
            // Hash de contraseña antes de guardar (OBLIGATORIO en APIs)
            if (isset($datosLimpios['password'])) {
                $datosLimpios['password'] = password_hash($datosLimpios['password'], PASSWORD_BCRYPT);
            }

            $usuario = Usuario::create($datosLimpios);

            http_response_code(201); // Created
            echo json_encode([
                'status' => 'success',
                'message' => 'Usuario creado exitosamente',
                'data' => ['id' => $usuario->id, 'email' => $usuario->email]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Elimina un usuario (JSON)
     * Ruta: /api/usuarios/{id} (DELETE)
     */
    public function eliminar($id) {
        header('Content-Type: application/json');
        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
                return;
            }

            $usuario->delete();
            echo json_encode(['status' => 'success', 'message' => "Usuario #$id eliminado"]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}