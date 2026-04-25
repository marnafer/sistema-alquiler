<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'servicios';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    /**
     * Relación: Un servicio pertenece a muchas propiedades (muchos a muchos)
     */
    public function propiedades()
    {
        return $this->belongsToMany(Propiedad::class, 'propiedad_servicio', 'servicio_id', 'propiedad_id');
    }

    /**
     * Obtener todos los servicios ordenados por nombre
     */
    public static function getAll()
    {
        return self::orderBy('nombre', 'asc')->get();
    }

    /**
     * Obtener un servicio por ID
     */
    public static function getById($id)
    {
        return self::find($id);
    }

    /**
     * Obtener un servicio por nombre
     */
    public static function getByNombre($nombre)
    {
        return self::where('nombre', $nombre)->first();
    }

    /**
     * Obtener servicios por propiedad
     */
    public static function getByPropiedad($propiedadId)
    {
        return self::whereHas('propiedades', function($query) use ($propiedadId) {
            $query->where('propiedad_id', $propiedadId);
        })->orderBy('nombre', 'asc')->get();
    }

    /**
     * Crear un nuevo servicio
     */
    public static function createServicio($data)
    {
        return self::create($data);
    }

    /**
     * Verificar si existe un servicio por ID
     */
    public static function exists($id)
    {
        return self::where('id', $id)->exists();
    }

    /**
     * Verificar si ya existe un servicio con el mismo nombre
     */
    public static function existsByNombre($nombre, $excluirId = null)
    {
        $query = self::where('nombre', $nombre);
        
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }
        
        return $query->exists();
    }

    /**
     * Verificar si tiene propiedades asociadas
     */
    public function hasPropiedades()
    {
        return $this->propiedades()->count() > 0;
    }

    /**
     * Obtener servicios con conteo de propiedades
     */
    public static function getAllWithCount()
    {
        return self::withCount('propiedades')
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(function($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'total_propiedades' => $servicio->propiedades_count
                ];
            });
    }

    /**
     * Obtener servicios populares (más usados)
     */
    public static function getPopulares($limit = 10)
    {
        return self::withCount('propiedades')
            ->having('propiedades_count', '>', 0)
            ->orderBy('propiedades_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'total_propiedades' => $servicio->propiedades_count
                ];
            });
    }
}