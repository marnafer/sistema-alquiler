<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consulta extends Model
{
    protected $table = 'consultas';
    public $timestamps = false; // Usamos el default current_timestamp del SQL

    protected $fillable = [
        'propiedad_id', 
        'inquilino_id', 
        'mensaje'
    ];

    protected $casts = [
        'fecha_consulta' => 'datetime',
    ];

    /**
     * Relación: La consulta pertenece a una propiedad específica.
     */
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    /**
     * Relación: La consulta fue realizada por un usuario (inquilino).
     */
    public function inquilino(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'inquilino_id');
    }
}