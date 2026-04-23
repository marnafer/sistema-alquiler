<?php
/**
 * Modelo de Roles (versión sencilla)
 */

namespace App\Models;

use PDO;
use Exception;

class Rol {
    
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Obtener todos los roles
     */
    public function getAll() {
        $query = "SELECT * FROM roles ORDER BY id ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener un rol por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM roles WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener un rol por nombre
     */
    public function getByNombre($nombre) {
        $query = "SELECT * FROM roles WHERE nombre = :nombre";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':nombre' => $nombre]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear un nuevo rol
     */
    public function create($data) {
        $query = "INSERT INTO roles (nombre) VALUES (:nombre)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':nombre' => $data['nombre']]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar un rol
     */
    public function update($id, $data) {
        $query = "UPDATE roles SET nombre = :nombre WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':id' => $id
        ]);
    }
    
    /**
     * Eliminar un rol
     */
    public function delete($id) {
        $query = "DELETE FROM roles WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Verificar si existe un rol
     */
    public function exists($id) {
        $query = "SELECT id FROM roles WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si ya existe un rol con el mismo nombre
     */
    public function existsByNombre($nombre, $excluirId = null) {
        if ($excluirId) {
            $query = "SELECT id FROM roles WHERE nombre = :nombre AND id != :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':nombre' => $nombre,
                ':id' => $excluirId
            ]);
        } else {
            $query = "SELECT id FROM roles WHERE nombre = :nombre";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':nombre' => $nombre]);
        }
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si tiene usuarios asociados
     */
    public function hasUsuarios($id) {
        $query = "SELECT COUNT(*) as total FROM usuarios WHERE rol_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }
    
    /**
     * Obtener roles con conteo de usuarios
     */
    public function getAllWithCount() {
        $query = "SELECT r.*, COUNT(u.id) as total_usuarios 
                  FROM roles r
                  LEFT JOIN usuarios u ON r.id = u.rol_id
                  GROUP BY r.id
                  ORDER BY r.id ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener rol por defecto (el más básico)
     */
    public function getDefaultRol() {
        $query = "SELECT * FROM roles WHERE nombre = 'inquilino' OR nombre = 'usuario' LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}