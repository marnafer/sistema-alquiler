<?php
/**
 * Modelo de Consultas
 */

namespace App\Models;

use PDO;

class Consulta {
    
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Listar todas las consultas
     */
    public function listar() {
        $query = "SELECT c.*, 
                         p.titulo as propiedad_titulo, 
                         p.direccion,
                         CONCAT(u.nombre, ' ', u.apellido) as inquilino_nombre,
                         u.email as inquilino_email
                  FROM consultas c
                  JOIN propiedades p ON c.propiedad_id = p.id
                  JOIN usuarios u ON c.inquilino_id = u.id
                  WHERE c.deleted_at IS NULL
                  ORDER BY c.fecha_consulta DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener una consulta por ID
     */
    public function obtener($id) {
        $query = "SELECT c.*, 
                         p.titulo as propiedad_titulo, 
                         p.direccion,
                         CONCAT(u.nombre, ' ', u.apellido) as inquilino_nombre,
                         u.email as inquilino_email,
                         u.telefono as inquilino_telefono
                  FROM consultas c
                  JOIN propiedades p ON c.propiedad_id = p.id
                  JOIN usuarios u ON c.inquilino_id = u.id
                  WHERE c.id = :id AND c.deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Listar consultas por propiedad
     */
    public function listarPorPropiedad($propiedadId) {
        $query = "SELECT c.*, 
                         CONCAT(u.nombre, ' ', u.apellido) as inquilino_nombre,
                         u.email as inquilino_email
                  FROM consultas c
                  JOIN usuarios u ON c.inquilino_id = u.id
                  WHERE c.propiedad_id = :propiedad_id AND c.deleted_at IS NULL
                  ORDER BY c.fecha_consulta DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':propiedad_id', $propiedadId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Listar consultas por inquilino
     */
    public function listarPorInquilino($inquilinoId) {
        $query = "SELECT c.*, 
                         p.titulo as propiedad_titulo, 
                         p.direccion
                  FROM consultas c
                  JOIN propiedades p ON c.propiedad_id = p.id
                  WHERE c.inquilino_id = :inquilino_id AND c.deleted_at IS NULL
                  ORDER BY c.fecha_consulta DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':inquilino_id', $inquilinoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear una nueva consulta
     */
    public function crear($data) {
        $query = "INSERT INTO consultas (propiedad_id, inquilino_id, mensaje, fecha_consulta) 
                  VALUES (:propiedad_id, :inquilino_id, :mensaje, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':propiedad_id', $data['propiedad_id'], PDO::PARAM_INT);
        $stmt->bindParam(':inquilino_id', $data['inquilino_id'], PDO::PARAM_INT);
        $stmt->bindParam(':mensaje', $data['mensaje']);
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar una consulta
     */
    public function actualizar($id, $data) {
        $query = "UPDATE consultas 
                  SET propiedad_id = :propiedad_id, 
                      inquilino_id = :inquilino_id, 
                      mensaje = :mensaje
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':propiedad_id', $data['propiedad_id'], PDO::PARAM_INT);
        $stmt->bindParam(':inquilino_id', $data['inquilino_id'], PDO::PARAM_INT);
        $stmt->bindParam(':mensaje', $data['mensaje']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Eliminar una consulta (soft delete)
     */
    public function eliminar($id) {
        $query = "UPDATE consultas SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Verificar si existe una consulta
     */
    public function existe($id) {
        $query = "SELECT id FROM consultas WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si la propiedad existe y está disponible
     */
    public function propiedadExiste($propiedadId) {
        $query = "SELECT id FROM propiedades WHERE id = :id AND deleted_at IS NULL AND disponible = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $propiedadId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si el inquilino existe
     */
    public function inquilinoExiste($inquilinoId) {
        $query = "SELECT id FROM usuarios WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $inquilinoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}