<?php
/**
 * Modelo de PropiedadServicio (tabla pivote)
 */

namespace App\Models;

use PDO;
use Exception;

class PropiedadServicio {
    
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Obtener todas las relaciones
     */
    public function getAll() {
        $query = "SELECT ps.*, 
                         p.titulo as propiedad_titulo,
                         s.nombre as servicio_nombre
                  FROM propiedad_servicio ps
                  JOIN propiedades p ON ps.propiedad_id = p.id
                  JOIN servicios s ON ps.servicio_id = s.id
                  ORDER BY ps.id ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener una relación por ID
     */
    public function getById($id) {
        $query = "SELECT ps.*, 
                         p.titulo as propiedad_titulo,
                         s.nombre as servicio_nombre
                  FROM propiedad_servicio ps
                  JOIN propiedades p ON ps.propiedad_id = p.id
                  JOIN servicios s ON ps.servicio_id = s.id
                  WHERE ps.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener relaciones por propiedad
     */
    public function getByPropiedad($propiedadId) {
        $query = "SELECT ps.*, s.nombre as servicio_nombre
                  FROM propiedad_servicio ps
                  JOIN servicios s ON ps.servicio_id = s.id
                  WHERE ps.propiedad_id = :propiedad_id
                  ORDER BY s.nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':propiedad_id' => $propiedadId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener relaciones por servicio
     */
    public function getByServicio($servicioId) {
        $query = "SELECT ps.*, p.titulo as propiedad_titulo
                  FROM propiedad_servicio ps
                  JOIN propiedades p ON ps.propiedad_id = p.id
                  WHERE ps.servicio_id = :servicio_id
                  ORDER BY p.titulo ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':servicio_id' => $servicioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear una nueva relación
     */
    public function create($data) {
        $query = "INSERT INTO propiedad_servicio (propiedad_id, servicio_id) 
                  VALUES (:propiedad_id, :servicio_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':propiedad_id' => $data['propiedad_id'],
            ':servicio_id' => $data['servicio_id']
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Eliminar una relación
     */
    public function delete($id) {
        $query = "DELETE FROM propiedad_servicio WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Eliminar todas las relaciones de una propiedad
     */
    public function deleteByPropiedad($propiedadId) {
        $query = "DELETE FROM propiedad_servicio WHERE propiedad_id = :propiedad_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':propiedad_id' => $propiedadId]);
    }
    
    /**
     * Eliminar todas las relaciones de un servicio
     */
    public function deleteByServicio($servicioId) {
        $query = "DELETE FROM propiedad_servicio WHERE servicio_id = :servicio_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':servicio_id' => $servicioId]);
    }
    
    /**
     * Verificar si existe una relación
     */
    public function exists($id) {
        $query = "SELECT id FROM propiedad_servicio WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si ya existe una relación (para evitar duplicados)
     */
    public function existsRelacion($propiedadId, $servicioId) {
        $query = "SELECT id FROM propiedad_servicio 
                  WHERE propiedad_id = :propiedad_id AND servicio_id = :servicio_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':propiedad_id' => $propiedadId,
            ':servicio_id' => $servicioId
        ]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Obtener IDs de servicios por propiedad
     */
    public function getServicioIdsByPropiedad($propiedadId) {
        $query = "SELECT servicio_id FROM propiedad_servicio WHERE propiedad_id = :propiedad_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':propiedad_id' => $propiedadId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Sincronizar servicios de una propiedad (eliminar los que no están y agregar los nuevos)
     */
    public function syncServiciosByPropiedad($propiedadId, $serviciosIds) {
        // Eliminar relaciones existentes
        $this->deleteByPropiedad($propiedadId);
        
        // Agregar nuevas relaciones
        $count = 0;
        foreach ($serviciosIds as $servicioId) {
            $data = [
                'propiedad_id' => $propiedadId,
                'servicio_id' => $servicioId
            ];
            $this->create($data);
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Obtener estadísticas de relaciones
     */
    public function getEstadisticas() {
        $estadisticas = [];
        
        // Total de relaciones
        $query = "SELECT COUNT(*) as total FROM propiedad_servicio";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $estadisticas['total_relaciones'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Promedio de servicios por propiedad
        $query = "SELECT AVG(total) as promedio 
                  FROM (SELECT COUNT(*) as total 
                        FROM propiedad_servicio 
                        GROUP BY propiedad_id) as sub";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $estadisticas['promedio_servicios_por_propiedad'] = round($stmt->fetch(PDO::FETCH_ASSOC)['promedio'] ?? 0, 2);
        
        // Propiedad con más servicios
        $query = "SELECT p.titulo, COUNT(ps.servicio_id) as total_servicios
                  FROM propiedad_servicio ps
                  JOIN propiedades p ON ps.propiedad_id = p.id
                  GROUP BY ps.propiedad_id
                  ORDER BY total_servicios DESC
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $estadisticas['propiedad_max_servicios'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Servicio más usado
        $query = "SELECT s.nombre, COUNT(ps.propiedad_id) as total_propiedades
                  FROM propiedad_servicio ps
                  JOIN servicios s ON ps.servicio_id = s.id
                  GROUP BY ps.servicio_id
                  ORDER BY total_propiedades DESC
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $estadisticas['servicio_mas_usado'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $estadisticas;
    }
}