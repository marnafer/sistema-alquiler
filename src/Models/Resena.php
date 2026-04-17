<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    protected $table = 'reseñas';           // ← Aquí sí va con ñ (nombre real de la tabla)
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'reserva_id',
        'calificacion',
        'comentario'
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }
}