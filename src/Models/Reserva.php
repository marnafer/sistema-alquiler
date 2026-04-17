<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reservas';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'propiedad_id', 'usuario_id', 'fecha_inicio', 'fecha_fin',
        'estado', 'observaciones'
    ];
}