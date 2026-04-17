<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorito extends Model {
    protected $table = 'favoritos';
    
    // IMPORTANTE: La tabla no tiene una columna 'id' simple
    protected $primaryKey = ['usuario_id', 'propiedad_id'];
    public $incrementing = false; // No es autoincremental
    
    public $timestamps = false; // No tiene created_at/updated_at

    protected $fillable = ['usuario_id', 'propiedad_id'];
}