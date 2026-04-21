<?php

namespace App\Controllers;

use App\Models\LogActividad;
use App\Sanitizers\LogActividadSanitizer;
use App\Validators\LogActividadValidator;

class LogActividadController {

    private $logModel;

    public function __construct() {
        $this->logModel = new LogActividad();
    }

    /**
     * Muestra el listado general de actividad
     */
    public function listarLogs() {
        // En el listado general no solemos recibir parámetros, 
        // pero podrías sanitizar si agregaras paginación (ej: ?page=1)
        $logs = $this->logModel->all(); 

        require_once SRC_PATH . 'views/logs/logs_listar.php';
    }

    /**
     * Muestra el detalle de un log específico
     */
    public function verLog() {
        // 1. Sanitización: Limpiamos el ID de la URL
        $id = LogActividadSanitizer::sanitizeId($_GET['id'] ?? null);

        // 2. Validación: Verificamos que sea un ID válido (entero > 0)
        if (!LogActividadValidator::validateId($id)) {
            renderError("El identificador de log proporcionado no es válido.", 400);
            return;
        }

        // 3. Búsqueda
        $log = $this->logModel->find($id);

        if (!$log) {
            renderError("Registro de actividad no encontrado.", 404);
            return;
        }

        require_once SRC_PATH . 'views/logs/log_detalle.php';
    }

    /**
     * Filtrar por usuario
     */
    public function listarPorUsuario() {
        // 1. Sanitización
        $usuarioId = LogActividadSanitizer::sanitizeId($_GET['usuario_id'] ?? null);
        
        // 2. Validación
        if (!LogActividadValidator::validateFiltroUsuario($usuarioId)) {
            // Si el ID de usuario es basura, mejor redirigir al índice de logs
            header('Location: /logs-actividad');
            exit;
        }

        // 3. Obtención de datos filtrados
        $logs = $this->logModel->where('usuario_id', '=', $usuarioId)->get();

        require_once SRC_PATH . 'views/logs/logs_usuario.php';
    }
}