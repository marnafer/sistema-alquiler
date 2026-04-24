<?php
/**
 * Modelo de Usuarios (versión Eloquent con Soft Deletes)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Model
{
    use SoftDeletes;

    protected $table = 'usuarios';
    protected $primaryKey = 'id';

    // Si tu tabla NO tiene created_at/updated_at, mantén false.
    public $timestamps = false;

    protected $dates = ['deleted_at'];

    // Campos rellenables
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'domicilio',
        'contrasena',
        'rol_id'
    ];

    // Ocultar atributos cuando se serializa
    protected $hidden = ['contrasena'];

    /**
     * Obtener todos los usuarios (excluye eliminados)
     * Devuelve array asociativo similar a la implementación previa.
     */
    public function obtenerTodos()
    {
        $rows = self::select('usuarios.*', 'roles.nombre as rol_nombre')
            ->join('roles', 'usuarios.rol_id', '=', 'roles.id')
            ->whereNull('usuarios.deleted_at')
            ->orderBy('usuarios.id', 'desc')
            ->get()
            ->toArray();

        foreach ($rows as &$r) {
            unset($r['contrasena']);
        }

        return $rows;
    }

    /**
     * Obtener un usuario por ID
     */
    public function obtenerPorId($id)
    {
        $row = self::select('usuarios.*', 'roles.nombre as rol_nombre')
            ->join('roles', 'usuarios.rol_id', '=', 'roles.id')
            ->where('usuarios.id', $id)
            ->whereNull('usuarios.deleted_at')
            ->first();

        if (!$row) return null;

        $data = $row->toArray();
        unset($data['contrasena']);
        return $data;
    }

    /**
     * Obtener usuario por email (incluye contraseña para login)
     */
    public function obtenerPorEmail($email)
    {
        $row = self::where('email', $email)
            ->whereNull('deleted_at')
            ->first();

        return $row ? $row->toArray() : null;
    }

    /**
     * Crear un nuevo usuario
     * Retorna el id del nuevo registro
     */
    public function crear(array $data)
    {
        $model = self::create([
            'nombre'    => $data['nombre'],
            'apellido'  => $data['apellido'],
            'email'     => $data['email'],
            'telefono'  => $data['telefono'] ?? null,
            'domicilio' => $data['domicilio'] ?? null,
            'contrasena'=> $data['contrasena'],
            'rol_id'    => $data['rol_id']
        ]);

        return $model->id;
    }

    /**
     * Actualizar un usuario
     */
    public function actualizar($id, array $data)
    {
        $model = self::where('id', $id)->whereNull('deleted_at')->first();
        if (!$model) return false;

        $fillable = $this->getFillable();
        foreach ($data as $k => $v) {
            if (in_array($k, $fillable, true)) {
                $model->{$k} = $v;
            }
        }

        return $model->save();
    }

    /**
     * Eliminar usuario (soft delete)
     */
    public function eliminar($id)
    {
        $model = self::where('id', $id)->whereNull('deleted_at')->first();
        if (!$model) return false;
        return (bool)$model->delete();
    }

    /**
     * Verificar si existe un usuario
     */
    public function existe($id)
    {
        return self::where('id', $id)->whereNull('deleted_at')->exists();
    }

    /**
     * Verificar si ya existe un email
     */
    public function existePorEmail($email, $excluirId = null)
    {
        $query = self::where('email', $email)->whereNull('deleted_at');
        if ($excluirId) $query->where('id', '!=', $excluirId);
        return $query->exists();
    }

    /**
     * Verificar credenciales para login
     * Retorna datos del usuario sin contrasena o false si falla
     */
    public function verificarCredenciales($email, $contrasena)
    {
        $row = self::where('email', $email)->whereNull('deleted_at')->first();
        if (!$row) return false;

        $arr = $row->toArray();
        if (isset($arr['contrasena']) && password_verify($contrasena, $arr['contrasena'])) {
            unset($arr['contrasena']);
            return $arr;
        }
        return false;
    }

    /**
     * Obtener usuarios por rol
     */
    public function obtenerPorRol($rolId)
    {
        $rows = self::select('usuarios.*', 'roles.nombre as rol_nombre')
            ->join('roles', 'usuarios.rol_id', '=', 'roles.id')
            ->where('usuarios.rol_id', $rolId)
            ->whereNull('usuarios.deleted_at')
            ->orderBy('usuarios.nombre', 'asc')
            ->get()
            ->toArray();

        foreach ($rows as &$r) {
            unset($r['contrasena']);
        }

        return $rows;
    }

    /**
     * Restaurar usuario eliminado
     */
    public function restaurar($id)
    {
        $model = self::withTrashed()->where('id', $id)->first();
        if (!$model) return false;
        $model->deleted_at = null;
        return (bool)$model->save();
    }
}