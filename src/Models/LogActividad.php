<?php
/**
 * Modelo de LogActividad (versión sencilla)
 */

namespace App\Models;

use PDO;
use Exception;

class LogActividadModel {
    
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Obtener todos los logs
     */
    public function getAll() {
        $query = "SELECT l.*, 
                         CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre,
                         u.email as usuario_email
                  FROM logs_actividad l
                  LEFT JOIN usuarios u ON l.usuario_id = u.id
                  ORDER BY l.fecha DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener un log por ID
     */
    public function getById($id) {
        $query = "SELECT l.*, 
                         CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre,
                         u.email as usuario_email
                  FROM logs_actividad l
                  LEFT JOIN usuarios u ON l.usuario_id = u.id
                  WHERE l.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener logs por usuario
     */
    public function getByUsuario($usuarioId) {
        $query = "SELECT l.*, 
                         CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                  FROM logs_actividad l
                  LEFT JOIN usuarios u ON l.usuario_id = u.id
                  WHERE l.usuario_id = :usuario_id
                  ORDER BY l.fecha DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener logs por rango de fechas
     */
    public function getByFechaRango($fechaDesde, $fechaHasta) {
        $query = "SELECT l.*, 
                         CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                  FROM logs_actividad l
                  LEFT JOIN usuarios u ON l.usuario_id = u.id
                  WHERE l.fecha BETWEEN :fecha_desde AND :fecha_hasta
                  ORDER BY l.fecha DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':fecha_desde' => $fechaDesde,
            ':fecha_hasta' => $fechaHasta
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener logs por acción (búsqueda)
     */
    public function getByAccion($busqueda) {
        $busqueda = "%{$busqueda}%";
        $query = "SELECT l.*, 
                         CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                  FROM logs_actividad l
                  LEFT JOIN usuarios u ON l.usuario_id = u.id
                  WHERE l.accion LIKE :busqueda
                  ORDER BY l.fecha DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':busqueda' => $busqueda]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear un nuevo log
     */
    public function create($data) {
        $query = "INSERT INTO logs_actividad (usuario_id, accion, ip_address, fecha) 
                  VALUES (:usuario_id, :accion, :ip_address, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':usuario_id' => $data['usuario_id'] ?? null,
            ':accion' => $data['accion'],
            ':ip_address' => $data['ip_address'] ?? null
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Registrar acción de usuario (método auxiliar)
     */
    public function registrar($usuarioId, $accion, $ip = null) {
        $data = [
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'ip_address' => $ip
        ];
        return $this->create($data);
    }
    
    /**
     * Eliminar logs antiguos (más de X días)
     */
    public function deleteOldLogs($dias) {
        $query = "DELETE FROM logs_actividad WHERE fecha < DATE_SUB(NOW(), INTERVAL :dias DAY)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':dias' => $dias]);
        return $stmt->rowCount();
    }
    
    /**
     * Eliminar logs de un usuario específico
     */
    public function deleteByUsuario($usuarioId) {
        $query = "DELETE FROM logs_actividad WHERE usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->rowCount();
    }
    
    /**
     * Eliminar un log específico
     */
    public function delete($id) {
        $query = "DELETE FROM logs_actividad WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Verificar si existe un log
     */
    public function exists($id) {
        $query = "SELECT id FROM logs_actividad WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Obtener estadísticas de logs
     */
    public function getEstadisticas() {
        $estadisticas = [];
        
        // Total de logs
        $query = "SELECT COUNT(*) as total FROM logs_actividad";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $estadisticas['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Logs por día (últimos 7 días)
        $query = "SELECT DATE(fecha) as dia, COUNT(*) as cantidad 
                  FROM logs_actividad 
                  WHERE fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  GROUP BY DATE(fecha)
                  ORDER BY dia DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $estadisticas['por_dia'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Logs por usuario (top 10)
        $query = "SELECT u.nombre, u.apellido, COUNT(l.id) as cantidad 
                  FROM logs_actividad l
                  JOIN usuarios u ON l.usuario_id = u.id
                  GROUP BY l.usuario_id
                  ORDER BY cantidad DESC
                  LIMIT 10";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $estadisticas['top_usuarios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Acciones más comunes
        $query = "SELECT accion, COUNT(*) as cantidad 
                  FROM logs_actividad 
                  GROUP BY accion 
                  ORDER BY cantidad DESC 
                  LIMIT 10";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $estadisticas['acciones_comunes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $estadisticas;
    }
}