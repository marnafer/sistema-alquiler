<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    }
}