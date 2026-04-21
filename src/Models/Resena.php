<?php
/**
 * Modelo de Reseñas (versión sencilla)
 */

namespace App\Models;

use PDO;
use Exception;

class ResenaModel {
    
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Obtener todas las reseñas
     */
    public function getAll() {
        $query = "SELECT r.*, 
                         CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre,
                         p.titulo as propiedad_titulo
                  FROM reseñas r
                  JOIN reservas re ON r.reserva_id = re.id
                  JOIN usuarios u ON re.inquilino_id = u.id
                  JOIN propiedades p ON re.propiedad_id = p.id
                  ORDER BY r.fecha_publicacion DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener una reseña por ID
     */
    public function getById($id) {
        $query = "SELECT r.*, 
                         CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre,
                         u.email as usuario_email,
                         p.titulo as propiedad_titulo,
                         p.id as propiedad_id
                  FROM reseñas r
                  JOIN reservas re ON r.reserva_id = re.id
                  JOIN usuarios u ON re.inquilino_id = u.id
                  JOIN propiedades p ON re.propiedad_id = p.id
                  WHERE r.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener reseñas por propiedad
     */
    public function getByPropiedad($propiedadId) {
        $query = "SELECT r.*, 
                         CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                  FROM reseñas r
                  JOIN reservas re ON r.reserva_id = re.id
                  JOIN usuarios u ON re.inquilino_id = u.id
                  WHERE re.propiedad_id = :propiedad_id
                  ORDER BY r.fecha_publicacion DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':propiedad_id' => $propiedadId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener reseñas por usuario (inquilino)
     */
    public function getByUsuario($usuarioId) {
        $query = "SELECT r.*, 
                         p.titulo as propiedad_titulo
                  FROM reseñas r
                  JOIN reservas re ON r.reserva_id = re.id
                  JOIN propiedades p ON re.propiedad_id = p.id
                  WHERE re.inquilino_id = :usuario_id
                  ORDER BY r.fecha_publicacion DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener calificación promedio de una propiedad
     */
    public function getPromedioByPropiedad($propiedadId) {
        $query = "SELECT AVG(r.calificacion) as promedio, COUNT(*) as total
                  FROM reseñas r
                  JOIN reservas re ON r.reserva_id = re.id
                  WHERE re.propiedad_id = :propiedad_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':propiedad_id' => $propiedadId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'promedio' => round($result['promedio'] ?? 0, 1),
            'total' => (int)($result['total'] ?? 0)
        ];
    }
    
    /**
     * Crear una nueva reseña
     */
    public function create($data) {
        $query = "INSERT INTO reseñas (reserva_id, calificacion, comentario, fecha_publicacion) 
                  VALUES (:reserva_id, :calificacion, :comentario, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':reserva_id' => $data['reserva_id'],
            ':calificacion' => $data['calificacion'],
            ':comentario' => $data['comentario']
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar una reseña
     */
    public function update($id, $data) {
        $query = "UPDATE reseñas 
                  SET calificacion = :calificacion, 
                      comentario = :comentario
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':calificacion' => $data['calificacion'],
            ':comentario' => $data['comentario'],
            ':id' => $id
        ]);
    }
    
    /**
     * Eliminar una reseña
     */
    public function delete($id) {
        $query = "DELETE FROM reseñas WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Verificar si existe una reseña
     */
    public function exists($id) {
        $query = "SELECT id FROM reseñas WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si ya existe una reseña para una reserva
     */
    public function existePorReserva($reservaId) {
        $query = "SELECT id FROM reseñas WHERE reserva_id = :reserva_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':reserva_id' => $reservaId]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si la reserva existe y está finalizada
     */
    public function reservaExistsAndFinalizada($reservaId) {
        $query = "SELECT id FROM reservas WHERE id = :id AND estado = 'finalizada' AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $reservaId]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Obtener estadísticas de reseñas
     */
    public function getEstadisticas() {
        $estadisticas = [];
        
        // Total de reseñas
        $query = "SELECT COUNT(*) as total FROM reseñas";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $estadisticas['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Promedio general
        $query = "SELECT AVG(calificacion) as promedio FROM reseñas";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $estadisticas['promedio_general'] = round($stmt->fetch(PDO::FETCH_ASSOC)['promedio'] ?? 0, 1);
        
        // Distribución de calificaciones
        $query = "SELECT calificacion, COUNT(*) as cantidad 
                  FROM reseñas 
                  GROUP BY calificacion 
                  ORDER BY calificacion DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $estadisticas['distribucion'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $estadisticas;
    }
}