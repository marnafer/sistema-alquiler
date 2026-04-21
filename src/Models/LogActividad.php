<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

<<<<<<< HEAD
class LogActividad extends Model
{
    protected $table = 'logs_actividad';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'accion',
        'ip_address'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
=======
class LogActividad extends Model {
    // Nombre de la tabla en la DB
    protected $table = 'logs_actividad';
    
    // Si tu tabla no tiene las columnas 'created_at' y 'updated_at' de Eloquent:
    public $timestamps = false;

    // Campos que se pueden cargar masivamente
    protected $fillable = ['usuario_id', 'accion', 'detalle', 'fecha', 'ip'];

    /**
     * Relación: Un log pertenece a un usuario
     */
    public function usuario() {
        return $this->belongsTo(Usuario::class, 'usuario_id');
>>>>>>> b9894038b043ce5e35b5f1beb01e7e56dbe6aea8
    }
}