<?php
/**
 * Modelo de Usuarios (versión sencilla)
 */

namespace App\Models;

use PDO;
use Exception;

class Usuario {
    
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Obtener todos los usuarios (excluye eliminados)
     */
    public function getAll() {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM usuarios u
                  JOIN roles r ON u.rol_id = r.id
                  WHERE u.deleted_at IS NULL
                  ORDER BY u.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Eliminar contraseña por seguridad
        foreach ($usuarios as &$usuario) {
            unset($usuario['contrasena']);
        }
        
        return $usuarios;
    }
    
    /**
     * Obtener un usuario por ID
     */
    public function getById($id) {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM usuarios u
                  JOIN roles r ON u.rol_id = r.id
                  WHERE u.id = :id AND u.deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            unset($usuario['contrasena']);
        }
        
        return $usuario;
    }
    
    /**
     * Obtener usuario por email (incluye contraseña para login)
     */
    public function getByEmail($email) {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM usuarios u
                  JOIN roles r ON u.rol_id = r.id
                  WHERE u.email = :email AND u.deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear un nuevo usuario
     */
    public function create($data) {
        $query = "INSERT INTO usuarios (nombre, apellido, email, telefono, domicilio, contrasena, rol_id) 
                  VALUES (:nombre, :apellido, :email, :telefono, :domicilio, :contrasena, :rol_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':apellido' => $data['apellido'],
            ':email' => $data['email'],
            ':telefono' => $data['telefono'] ?? null,
            ':domicilio' => $data['domicilio'] ?? null,
            ':contrasena' => $data['contrasena'],
            ':rol_id' => $data['rol_id']
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar un usuario
     */
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['nombre'])) {
            $fields[] = "nombre = :nombre";
            $params[':nombre'] = $data['nombre'];
        }
        if (isset($data['apellido'])) {
            $fields[] = "apellido = :apellido";
            $params[':apellido'] = $data['apellido'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        if (isset($data['telefono'])) {
            $fields[] = "telefono = :telefono";
            $params[':telefono'] = $data['telefono'];
        }
        if (isset($data['domicilio'])) {
            $fields[] = "domicilio = :domicilio";
            $params[':domicilio'] = $data['domicilio'];
        }
        if (isset($data['contrasena'])) {
            $fields[] = "contrasena = :contrasena";
            $params[':contrasena'] = $data['contrasena'];
        }
        if (isset($data['rol_id'])) {
            $fields[] = "rol_id = :rol_id";
            $params[':rol_id'] = $data['rol_id'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $query = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }
    
    /**
     * Eliminar usuario (soft delete)
     */
    public function delete($id) {
        $query = "UPDATE usuarios SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Verificar si existe un usuario
     */
    public function exists($id) {
        $query = "SELECT id FROM usuarios WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si ya existe un email
     */
    public function existsByEmail($email, $excluirId = null) {
        if ($excluirId) {
            $query = "SELECT id FROM usuarios WHERE email = :email AND id != :id AND deleted_at IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':email' => $email,
                ':id' => $excluirId
            ]);
        } else {
            $query = "SELECT id FROM usuarios WHERE email = :email AND deleted_at IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
        }
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar credenciales para login
     */
    public function verifyCredentials($email, $contrasena) {
        $usuario = $this->getByEmail($email);
        
        if (!$usuario) {
            return false;
        }
        
        // Verificar contraseña (asumiendo que está hasheada con password_hash)
        if (password_verify($contrasena, $usuario['contrasena'])) {
            unset($usuario['contrasena']);
            return $usuario;
        }
        
        return false;
    }
    
    /**
     * Obtener usuarios por rol
     */
    public function getByRol($rolId) {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM usuarios u
                  JOIN roles r ON u.rol_id = r.id
                  WHERE u.rol_id = :rol_id AND u.deleted_at IS NULL
                  ORDER BY u.nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':rol_id' => $rolId]);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($usuarios as &$usuario) {
            unset($usuario['contrasena']);
        }
        
        return $usuarios;
    }
    
    /**
     * Restaurar usuario eliminado
     */
    public function restore($id) {
        $query = "UPDATE usuarios SET deleted_at = NULL WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}