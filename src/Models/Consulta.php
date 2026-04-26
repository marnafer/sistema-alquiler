<?php
/**
 * Modelo de Consultas
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consulta extends Model
{

    protected $table = 'consultas';

    protected $fillable = [
        'propiedad_id',
        'inquilino_id',
        'mensaje',
        'fecha_consulta'
    ];

    public $timestamps = false; // fecha_consulta manual

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }

    public function inquilino()
    {
        return $this->belongsTo(Usuario::class, 'inquilino_id');
    }
}