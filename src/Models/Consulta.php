<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    protected $table = 'consultas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'propiedad_id',
        'inquilino_id',
        'mensaje'
    ];

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function inquilino()
    {
        return $this->belongsTo(Usuario::class, 'inquilino_id');
    }
}