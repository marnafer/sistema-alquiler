<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropiedadServicio extends Model
{
    // Nombre exacto de la tabla en la BD.
    protected $table = 'propiedad_servicio';

    // Si la tabla no tiene created_at/updated_at:
    public $timestamps = false;

    // Campos que se pueden llenar en masa
    protected $fillable = [
        'propiedad_id',
        'servicio_id'
    ];

    /**
     * Relación: este registro pertenece a una Propiedad
     */
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }

    /**
     * Relación: este registro pertenece a un Servicio
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}