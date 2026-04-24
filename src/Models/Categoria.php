<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    /**
     * Relación: Una categoría tiene muchas propiedades.
     */
    public function propiedades(): HasMany
    {
        return $this->hasMany(Propiedad::class, 'categoria_id');
    }

   //* public static function listar()
    //{
     //   return self::all();
    //}
    
}