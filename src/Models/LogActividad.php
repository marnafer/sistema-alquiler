<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    }
}