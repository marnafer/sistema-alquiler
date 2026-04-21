<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    use SoftDeletes;

    protected $table = 'reservas';
    const CREATED_AT = 'fecha_reserva'; // Mapeamos tu columna personalizada
    const UPDATED_AT = null;

    protected $fillable = [
        'propiedad_id', 'inquilino_id', 'fecha_desde', 
        'fecha_hasta', 'precio_total', 'estado'
    ];

    protected $casts = [
        'fecha_desde'  => 'date',
        'fecha_hasta'  => 'date',
        'precio_total' => 'decimal:2',
        'deleted_at'   => 'datetime'
    ];

    // --- Scopes Profesionales ---

    /**
     * Filtra solo los contratos confirmados.
     */
    public function scopeConfirmados($query)
    {
        return $query->where('estado', 'confirmada');
    }

    // --- Relaciones ---

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function inquilino(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'inquilino_id');
    }
}