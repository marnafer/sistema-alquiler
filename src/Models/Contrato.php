<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $table = 'contratos';   // o 'reservas' si preferís
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'propiedad_id', 'usuario_id', 'fecha_inicio', 'fecha_fin',
        'monto_alquiler', 'deposito', 'estado', 'observaciones'
    ];

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}