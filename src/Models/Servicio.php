<?php
/**
 * Modelo de Servicios (versión sencilla)
 */

namespace App\Models;

use PDO;
use Exception;

class Servicio {
    
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Obtener todos los servicios
     */
    public function getAll() {
        $query = "SELECT * FROM servicios ORDER BY nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener un servicio por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM servicios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener un servicio por nombre
     */
    public function getByNombre($nombre) {
        $query = "SELECT * FROM servicios WHERE nombre = :nombre";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':nombre' => $nombre]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener servicios por propiedad
     */
    public function getByPropiedad($propiedadId) {
        $query = "SELECT s.* 
                  FROM servicios s
                  JOIN propiedad_servicio ps ON s.id = ps.servicio_id
                  WHERE ps.propiedad_id = :propiedad_id
                  ORDER BY s.nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':propiedad_id' => $propiedadId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear un nuevo servicio
     */
    public function create($data) {
        $query = "INSERT INTO servicios (nombre) VALUES (:nombre)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':nombre' => $data['nombre']]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar un servicio
     */
    public function update($id, $data) {
        $query = "UPDATE servicios SET nombre = :nombre WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':id' => $id
        ]);
    }
    
    /**
     * Eliminar un servicio
     */
    public function delete($id) {
        $query = "DELETE FROM servicios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Verificar si existe un servicio
     */
    public function exists($id) {
        $query = "SELECT id FROM servicios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si ya existe un servicio con el mismo nombre
     */
    public function existsByNombre($nombre, $excluirId = null) {
        if ($excluirId) {
            $query = "SELECT id FROM servicios WHERE nombre = :nombre AND id != :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':nombre' => $nombre,
                ':id' => $excluirId
            ]);
        } else {
            $query = "SELECT id FROM servicios WHERE nombre = :nombre";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':nombre' => $nombre]);
        }
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si tiene propiedades asociadas
     */
    public function hasPropiedades($id) {
        $query = "SELECT COUNT(*) as total FROM propiedad_servicio WHERE servicio_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }
    
    /**
     * Obtener servicios con conteo de propiedades
     */
    public function getAllWithCount() {
        $query = "SELECT s.*, COUNT(ps.propiedad_id) as total_propiedades 
                  FROM servicios s
                  LEFT JOIN propiedad_servicio ps ON s.id = ps.servicio_id
                  GROUP BY s.id
                  ORDER BY s.nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener servicios populares (más usados)
     */
    public function getPopulares($limit = 10) {
        $query = "SELECT s.*, COUNT(ps.propiedad_id) as total_propiedades 
                  FROM servicios s
                  JOIN propiedad_servicio ps ON s.id = ps.servicio_id
                  GROUP BY s.id
                  ORDER BY total_propiedades DESC
                  LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}