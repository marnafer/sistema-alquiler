<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogActividad extends Model
{
    protected $table = 'logs_actividad';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'accion',
        'ip_address',
        'fecha'
    ];

    protected $casts = [
        'fecha' => 'datetime'
    ];

    /**
     * Relación con Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Obtener todos los logs
     */
    public static function getAll()
    {
        return self::with('usuario')
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'usuario_id' => $log->usuario_id,
                    'accion' => $log->accion,
                    'ip_address' => $log->ip_address,
                    'fecha' => $log->fecha,
                    'usuario_nombre' => $log->usuario ? ($log->usuario->nombre . ' ' . $log->usuario->apellido) : null,
                    'usuario_email' => $log->usuario->email ?? null
                ];
            });
    }

    /**
     * Obtener un log por ID
     */
    public static function getById($id)
    {
        $log = self::with('usuario')->find($id);
        
        if (!$log) {
            return null;
        }
        
        return [
            'id' => $log->id,
            'usuario_id' => $log->usuario_id,
            'accion' => $log->accion,
            'ip_address' => $log->ip_address,
            'fecha' => $log->fecha,
            'usuario_nombre' => $log->usuario ? ($log->usuario->nombre . ' ' . $log->usuario->apellido) : null,
            'usuario_email' => $log->usuario->email ?? null
        ];
    }

    /**
     * Obtener logs por usuario
     */
    public static function getByUsuario($usuarioId)
    {
        return self::where('usuario_id', $usuarioId)
            ->with('usuario')
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'accion' => $log->accion,
                    'ip_address' => $log->ip_address,
                    'fecha' => $log->fecha,
                    'usuario_nombre' => $log->usuario ? ($log->usuario->nombre . ' ' . $log->usuario->apellido) : null
                ];
            });
    }

    /**
     * Obtener logs por rango de fechas
     */
    public static function getByFechaRango($fechaDesde, $fechaHasta)
    {
        return self::with('usuario')
            ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'usuario_id' => $log->usuario_id,
                    'accion' => $log->accion,
                    'ip_address' => $log->ip_address,
                    'fecha' => $log->fecha,
                    'usuario_nombre' => $log->usuario ? ($log->usuario->nombre . ' ' . $log->usuario->apellido) : null
                ];
            });
    }

    /**
     * Obtener logs por acción (búsqueda)
     */
    public static function getByAccion($busqueda)
    {
        return self::with('usuario')
            ->where('accion', 'LIKE', "%{$busqueda}%")
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'usuario_id' => $log->usuario_id,
                    'accion' => $log->accion,
                    'ip_address' => $log->ip_address,
                    'fecha' => $log->fecha,
                    'usuario_nombre' => $log->usuario ? ($log->usuario->nombre . ' ' . $log->usuario->apellido) : null
                ];
            });
    }

    /**
     * Crear un nuevo log
     */
    public static function createLog($data)
    {
        $data['fecha'] = date('Y-m-d H:i:s');
        return self::create($data);
    }

    /**
     * Registrar acción de usuario (método auxiliar)
     */
    public static function registrar($usuarioId, $accion, $ip = null)
    {
        return self::createLog([
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'ip_address' => $ip
        ]);
    }

    /**
     * Eliminar logs antiguos (más de X días)
     */
    public static function deleteOldLogs($dias)
    {
        $fechaLimite = date('Y-m-d H:i:s', strtotime("-{$dias} days"));
        return self::where('fecha', '<', $fechaLimite)->delete();
    }

    /**
     * Eliminar logs de un usuario específico
     */
    public static function deleteByUsuario($usuarioId)
    {
        return self::where('usuario_id', $usuarioId)->delete();
    }

    /**
     * Eliminar un log específico
     */
    public static function deleteLog($id)
    {
        $log = self::find($id);
        if (!$log) {
            return false;
        }
        return $log->delete();
    }

    /**
     * Verificar si existe un log
     */
    public static function exists($id)
    {
        return self::where('id', $id)->exists();
    }

    /**
     * Obtener estadísticas de logs
     */
    public static function getEstadisticas()
    {
        $total = self::count();
        
        // Logs por día (últimos 7 días)
        $porDia = self::selectRaw('DATE(fecha) as dia, COUNT(*) as cantidad')
            ->where('fecha', '>=', date('Y-m-d', strtotime('-7 days')))
            ->groupBy('dia')
            ->orderBy('dia', 'desc')
            ->get()
            ->toArray();
        
        // Logs por usuario (top 10)
        $topUsuarios = self::selectRaw('usuario_id, COUNT(*) as cantidad')
            ->whereNotNull('usuario_id')
            ->groupBy('usuario_id')
            ->orderBy('cantidad', 'desc')
            ->limit(10)
            ->with('usuario')
            ->get()
            ->map(function($log) {
                return [
                    'nombre' => $log->usuario->nombre ?? null,
                    'apellido' => $log->usuario->apellido ?? null,
                    'cantidad' => $log->cantidad
                ];
            })
            ->toArray();
        
        // Acciones más comunes
        $accionesComunes = self::selectRaw('accion, COUNT(*) as cantidad')
            ->groupBy('accion')
            ->orderBy('cantidad', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
        
        return [
            'total' => $total,
            'por_dia' => $porDia,
            'top_usuarios' => $topUsuarios,
            'acciones_comunes' => $accionesComunes
        ];
    }
}