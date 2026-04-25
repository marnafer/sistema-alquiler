<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    protected $table = 'provincias';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    /**
     * Relación: Una provincia tiene muchas localidades.
     */
    public function localidades()
    {
        return $this->hasMany(Localidad::class, 'provincia_id');
    }

    /**
     * Obtener todas las provincias ordenadas por nombre
     */
    public static function getAll()
    {
        return self::orderBy('nombre', 'asc')->get();
    }

    /**
     * Obtener una provincia por ID
     */
    public static function getById($id)
    {
        return self::find($id);
    }

    /**
     * Crear una nueva provincia
     */
    public static function createProvincia($data)
    {
        return self::create($data);
    }

    /**
     * Verificar si existe una provincia por ID
     */
    public static function exists($id)
    {
        return self::where('id', $id)->exists();
    }

    /**
     * Verificar si ya existe una provincia con el mismo nombre
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
     * Verificar si tiene localidades asociadas
     */
    public function hasLocalidades()
    {
        return $this->localidades()->count() > 0;
    }

    /**
     * Obtener provincias con conteo de localidades
     */
    public static function getAllWithCount()
    {
        return self::withCount('localidades')
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(function($provincia) {
                return [
                    'id' => $provincia->id,
                    'nombre' => $provincia->nombre,
                    'total_localidades' => $provincia->localidades_count
                ];
            });
    }
}