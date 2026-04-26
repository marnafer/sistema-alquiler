<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropiedadServicio extends Model
{
    protected $table = 'propiedad_servicio';
    public $timestamps = false;

    protected $fillable = ['propiedad_id', 'servicio_id'];

    /**
     * Relación con Propiedad
     */
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }

    /**
     * Relación con Servicio
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * Obtener todas las relaciones
     */
    public static function getAll()
    {
        return self::with(['propiedad', 'servicio'])
            ->orderBy('id', 'asc')
            ->get()
            ->map(function($relacion) {
                return [
                    'id' => $relacion->id,
                    'propiedad_id' => $relacion->propiedad_id,
                    'servicio_id' => $relacion->servicio_id,
                    'propiedad_titulo' => $relacion->propiedad->titulo ?? null,
                    'servicio_nombre' => $relacion->servicio->nombre ?? null
                ];
            });
    }

    /**
     * Obtener una relación por ID
     */
    public static function getById($id)
    {
        $relacion = self::with(['propiedad', 'servicio'])->find($id);
        
        if (!$relacion) {
            return null;
        }
        
        return [
            'id' => $relacion->id,
            'propiedad_id' => $relacion->propiedad_id,
            'servicio_id' => $relacion->servicio_id,
            'propiedad_titulo' => $relacion->propiedad->titulo ?? null,
            'servicio_nombre' => $relacion->servicio->nombre ?? null
        ];
    }

    /**
     * Obtener relaciones por propiedad
     */
    public static function getByPropiedad($propiedadId)
    {
        return self::where('propiedad_id', $propiedadId)
            ->with('servicio')
            ->orderBy('servicio_id', 'asc')
            ->get()
            ->map(function($relacion) {
                return [
                    'id' => $relacion->id,
                    'propiedad_id' => $relacion->propiedad_id,
                    'servicio_id' => $relacion->servicio_id,
                    'servicio_nombre' => $relacion->servicio->nombre ?? null
                ];
            });
    }

    /**
     * Obtener relaciones por servicio
     */
    public static function getByServicio($servicioId)
    {
        return self::where('servicio_id', $servicioId)
            ->with('propiedad')
            ->orderBy('propiedad_id', 'asc')
            ->get()
            ->map(function($relacion) {
                return [
                    'id' => $relacion->id,
                    'propiedad_id' => $relacion->propiedad_id,
                    'servicio_id' => $relacion->servicio_id,
                    'propiedad_titulo' => $relacion->propiedad->titulo ?? null
                ];
            });
    }

    /**
     * Crear una nueva relación
     */
    public static function createRelacion($data)
    {
        return self::create($data);
    }

    /**
     * Verificar si existe una relación
     */
    public static function exists($id)
    {
        return self::where('id', $id)->exists();
    }

    /**
     * Verificar si ya existe una relación (para evitar duplicados)
     */
    public static function existsRelacion($propiedadId, $servicioId)
    {
        return self::where('propiedad_id', $propiedadId)
            ->where('servicio_id', $servicioId)
            ->exists();
    }

    /**
     * Obtener IDs de servicios por propiedad
     */
    public static function getServicioIdsByPropiedad($propiedadId)
    {
        return self::where('propiedad_id', $propiedadId)
            ->pluck('servicio_id')
            ->toArray();
    }

    /**
     * Sincronizar servicios de una propiedad
     */
    public static function syncServiciosByPropiedad($propiedadId, $serviciosIds)
    {
        // Eliminar relaciones existentes
        self::where('propiedad_id', $propiedadId)->delete();
        
        // Agregar nuevas relaciones
        $count = 0;
        foreach ($serviciosIds as $servicioId) {
            self::create([
                'propiedad_id' => $propiedadId,
                'servicio_id' => $servicioId
            ]);
            $count++;
        }
        
        return $count;
    }

    /**
     * Obtener estadísticas de relaciones
     */
    public static function getEstadisticas()
    {
        $estadisticas = [];
        
        // Total de relaciones
        $estadisticas['total_relaciones'] = self::count();
        
        // Promedio de servicios por propiedad
        $promedio = self::selectRaw('propiedad_id, COUNT(*) as total')
            ->groupBy('propiedad_id')
            ->get()
            ->avg('total');
        
        $estadisticas['promedio_servicios_por_propiedad'] = round($promedio ?? 0, 2);
        
        // Propiedad con más servicios
        $propiedadMax = self::selectRaw('propiedad_id, COUNT(*) as total_servicios')
            ->with('propiedad')
            ->groupBy('propiedad_id')
            ->orderBy('total_servicios', 'desc')
            ->first();
        
        if ($propiedadMax && $propiedadMax->propiedad) {
            $estadisticas['propiedad_max_servicios'] = [
                'titulo' => $propiedadMax->propiedad->titulo,
                'total_servicios' => $propiedadMax->total_servicios
            ];
        } else {
            $estadisticas['propiedad_max_servicios'] = null;
        }
        
        // Servicio más usado
        $servicioMax = self::selectRaw('servicio_id, COUNT(*) as total_propiedades')
            ->with('servicio')
            ->groupBy('servicio_id')
            ->orderBy('total_propiedades', 'desc')
            ->first();
        
        if ($servicioMax && $servicioMax->servicio) {
            $estadisticas['servicio_mas_usado'] = [
                'nombre' => $servicioMax->servicio->nombre,
                'total_propiedades' => $servicioMax->total_propiedades
            ];
        } else {
            $estadisticas['servicio_mas_usado'] = null;
        }
        
        return $estadisticas;
    }

    /**
     * Eliminar una relación
     */
    public static function deleteRelacion($id)
    {
        return self::where('id', $id)->delete();
    }

    /**
     * Eliminar todas las relaciones de una propiedad
     */
    public static function deleteByPropiedad($propiedadId)
    {
        return self::where('propiedad_id', $propiedadId)->delete();
    }

    /**
     * Eliminar todas las relaciones de un servicio
     */
    public static function deleteByServicio($servicioId)
    {
        return self::where('servicio_id', $servicioId)->delete();
    }
}