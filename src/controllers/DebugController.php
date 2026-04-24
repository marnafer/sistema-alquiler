<?php
/**
 * Controlador de debug para API
 */

namespace App\Controllers;

require_once dirname(__DIR__) . '/debug/Debugger.php';

use App\Debug\Debugger;

class DebugController {
    
    /**
     * GET /api/debug/stats
     */
    public function stats() {
        $entidades = [
            'categorias', 'consultas', 'reservas', 'reseñas',
            'propiedades', 'usuarios', 'favoritos', 'provincias',
            'localidades', 'logs_actividad', 'roles', 'servicios',
            'propiedad_servicio', 'propiedad_imagenes'
        ];
        
        global $db;
        $totalRegistros = 0;
        
        foreach ($entidades as $entidad) {
            $query = "SELECT COUNT(*) as total FROM $entidad";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $totalRegistros += $stmt->fetch()['total'];
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'total_entidades' => count($entidades),
                'total_registros' => $totalRegistros,
                'total_logs' => count(Debugger::getStats()),
                'debug_enabled' => true
            ]
        ]);
    }
    
    /**
     * GET /api/debug/logs
     */
    public function logs() {
        $logFile = 'debug.log';
        
        if (!file_exists($logFile)) {
            echo json_encode([
                'success' => true,
                'data' => []
            ]);
            return;
        }
        
        $content = file_get_contents($logFile);
        $lines = explode(PHP_EOL, trim($content));
        $logs = [];
        
        foreach ($lines as $line) {
            if ($line) {
                $logs[] = json_decode($line, true);
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => array_reverse($logs)
        ]);
    }
    
    /**
     * POST /api/debug/clear-log
     */
    public function clearLog() {
        Debugger::clearLog();
        
        echo json_encode([
            'success' => true,
            'message' => 'Log limpiado exitosamente'
        ]);
    }
    
    /**
     * GET /api/debug/test-db
     */
    public function testDB() {
        global $db;
        
        try {
            $query = "SELECT 1 as test";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'message' => 'Conexión a BD exitosa',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * GET /api/debug/phpinfo
     */
    public function phpinfo() {
        phpinfo();
        exit;
    }
}