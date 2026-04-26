<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    /**
     * Relación con Usuarios
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'rol_id');
    }

    /**
     * Obtener todos los roles
     */
    public static function getAll()
    {
        return self::orderBy('id', 'asc')->get();
    }

    /**
     * Obtener un rol por ID
     */
    public static function getById($id)
    {
        return self::find($id);
    }

    /**
     * Obtener un rol por nombre
     */
    public static function getByNombre($nombre)
    {
        return self::where('nombre', $nombre)->first();
    }

    /**
     * Crear un nuevo rol
     */
    public static function createRol($data)
    {
        return self::create($data);
    }

    /**
     * Actualizar un rol
     */
    public static function updateRol($id, $data)
    {
        $rol = self::find($id);
        if (!$rol) {
            return false;
        }
        return $rol->update($data);
    }

    /**
     * Eliminar un rol
     */
    public static function deleteRol($id)
    {
        $rol = self::find($id);
        if (!$rol) {
            return false;
        }
        return $rol->delete();
    }

    /**
     * Verificar si existe un rol
     */
    public static function exists($id)
    {
        return self::where('id', $id)->exists();
    }

    /**
     * Verificar si ya existe un rol con el mismo nombre
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
     * Verificar si tiene usuarios asociados
     */
    public function hasUsuarios()
    {
        return $this->usuarios()->count() > 0;
    }

    /**
     * Obtener roles con conteo de usuarios
     */
    public static function getAllWithCount()
    {
        return self::withCount('usuarios')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function($rol) {
                return [
                    'id' => $rol->id,
                    'nombre' => $rol->nombre,
                    'total_usuarios' => $rol->usuarios_count
                ];
            });
    }

    /**
     * Obtener rol por defecto (el más básico)
     */
    public static function getDefaultRol()
    {
        return self::where('nombre', 'inquilino')
            ->orWhere('nombre', 'usuario')
            ->first();
    }
}