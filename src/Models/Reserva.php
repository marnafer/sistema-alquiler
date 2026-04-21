<?php
/**
 * Modelo de Reservas (versión sencilla)
 */

namespace App\Models;

use PDO;
use Exception;

class Reserva {
    
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Obtener todas las reservas
     */
    public function getAll() {
        $query = "SELECT r.*, 
                         p.titulo as propiedad_titulo,
                         CONCAT(u.nombre, ' ', u.apellido) as inquilino_nombre
                  FROM reservas r
                  JOIN propiedades p ON r.propiedad_id = p.id
                  JOIN usuarios u ON r.inquilino_id = u.id
                  WHERE r.deleted_at IS NULL
                  ORDER BY r.fecha_reserva DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener una reserva por ID
     */
    public function getById($id) {
        $query = "SELECT r.*, 
                         p.titulo as propiedad_titulo,
                         CONCAT(u.nombre, ' ', u.apellido) as inquilino_nombre
                  FROM reservas r
                  JOIN propiedades p ON r.propiedad_id = p.id
                  JOIN usuarios u ON r.inquilino_id = u.id
                  WHERE r.id = :id AND r.deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear una nueva reserva
     */
    public function create($data) {
        $query = "INSERT INTO reservas (propiedad_id, inquilino_id, fecha_desde, fecha_hasta, precio_total, estado) 
                  VALUES (:propiedad_id, :inquilino_id, :fecha_desde, :fecha_hasta, :precio_total, :estado)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':propiedad_id' => $data['propiedad_id'],
            ':inquilino_id' => $data['inquilino_id'],
            ':fecha_desde' => $data['fecha_desde'],
            ':fecha_hasta' => $data['fecha_hasta'],
            ':precio_total' => $data['precio_total'],
            ':estado' => $data['estado']
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar una reserva
     */
    public function update($id, $data) {
        $query = "UPDATE reservas 
                  SET propiedad_id = :propiedad_id, 
                      inquilino_id = :inquilino_id, 
                      fecha_desde = :fecha_desde, 
                      fecha_hasta = :fecha_hasta, 
                      precio_total = :precio_total,
                      estado = :estado
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':propiedad_id' => $data['propiedad_id'],
            ':inquilino_id' => $data['inquilino_id'],
            ':fecha_desde' => $data['fecha_desde'],
            ':fecha_hasta' => $data['fecha_hasta'],
            ':precio_total' => $data['precio_total'],
            ':estado' => $data['estado'],
            ':id' => $id
        ]);
    }
    
    /**
     * Eliminar reserva (soft delete)
     */
    public function delete($id) {
        $query = "UPDATE reservas SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Verificar si existe una reserva
     */
    public function exists($id) {
        $query = "SELECT id FROM reservas WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Cambiar estado de una reserva
     */
    public function changeStatus($id, $estado) {
        $query = "UPDATE reservas SET estado = :estado WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':estado' => $estado,
            ':id' => $id
        ]);
    }
    
    /**
     * Verificar disponibilidad de una propiedad
     */
    public function checkAvailability($propiedadId, $fechaDesde, $fechaHasta) {
        $query = "SELECT COUNT(*) as total 
                  FROM reservas 
                  WHERE propiedad_id = :propiedad_id 
                  AND deleted_at IS NULL
                  AND estado IN ('pendiente', 'confirmada')
                  AND fecha_desde <= :fecha_hasta 
                  AND fecha_hasta >= :fecha_desde";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':propiedad_id' => $propiedadId,
            ':fecha_desde' => $fechaDesde,
            ':fecha_hasta' => $fechaHasta
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] == 0;
    }
    
    /**
     * Verificar si la propiedad existe
     */
    public function propiedadExists($id) {
        $query = "SELECT id FROM propiedades WHERE id = :id AND deleted_at IS NULL AND disponible = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si el inquilino existe
     */
    public function inquilinoExists($id) {
        $query = "SELECT id FROM usuarios WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}