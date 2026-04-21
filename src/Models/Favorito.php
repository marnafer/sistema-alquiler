<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorito extends Model {
    protected $table = 'favoritos';
    
    protected $primaryKey = ['usuario_id', 'propiedad_id'];
    public $incrementing = false; 
    public $timestamps = false; 

    protected $fillable = ['usuario_id', 'propiedad_id'];
    
    /**
     * Relacion para traer los datos de la propiedad vinculada
     */
    public function propiedad() { 
        return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }
}