<?php
/**
 * Modelo de Provincias (versión sencilla)
 */

namespace App\Models;

use PDO;
use Exception;

class Provincia {
    
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Obtener todas las provincias
     */
    public function getAll() {
        $query = "SELECT * FROM provincias ORDER BY nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener una provincia por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM provincias WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear una nueva provincia
     */
    public function create($data) {
        $query = "INSERT INTO provincias (nombre) VALUES (:nombre)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':nombre' => $data['nombre']]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar una provincia
     */
    public function update($id, $data) {
        $query = "UPDATE provincias SET nombre = :nombre WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':id' => $id
        ]);
    }
    
    /**
     * Eliminar una provincia
     */
    public function delete($id) {
        $query = "DELETE FROM provincias WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Verificar si existe una provincia
     */
    public function exists($id) {
        $query = "SELECT id FROM provincias WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si ya existe una provincia con el mismo nombre
     */
    public function existsByNombre($nombre, $excluirId = null) {
        if ($excluirId) {
            $query = "SELECT id FROM provincias WHERE nombre = :nombre AND id != :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':nombre' => $nombre,
                ':id' => $excluirId
            ]);
        } else {
            $query = "SELECT id FROM provincias WHERE nombre = :nombre";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':nombre' => $nombre]);
        }
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si tiene localidades asociadas
     */
    public function hasLocalidades($id) {
        $query = "SELECT COUNT(*) as total FROM localidades WHERE provincia_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }
    
    /**
     * Obtener provincias con conteo de localidades
     */
    public function getAllWithCount() {
        $query = "SELECT p.*, COUNT(l.id) as total_localidades 
                  FROM provincias p
                  LEFT JOIN localidades l ON p.id = l.provincia_id
                  GROUP BY p.id
                  ORDER BY p.nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}