<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    protected $table = 'reseñas';
    public $timestamps = false;

    protected $fillable = [
        'reserva_id',
        'calificacion',
        'comentario',
        'fecha_publicacion'
    ];

    protected $casts = [
        'calificacion' => 'integer',
        'fecha_publicacion' => 'datetime'
    ];

    /**
     * Relación con Reserva
     */
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    /**
     * Obtener todas las reseñas
     */
    public static function getAll()
    {
        return self::with(['reserva.propiedad', 'reserva.inquilino'])
            ->orderBy('fecha_publicacion', 'desc')
            ->get()
            ->map(function($resena) {
                return [
                    'id' => $resena->id,
                    'reserva_id' => $resena->reserva_id,
                    'calificacion' => $resena->calificacion,
                    'comentario' => $resena->comentario,
                    'fecha_publicacion' => $resena->fecha_publicacion,
                    'usuario_nombre' => $resena->reserva->inquilino ? 
                        ($resena->reserva->inquilino->nombre . ' ' . $resena->reserva->inquilino->apellido) : null,
                    'propiedad_titulo' => $resena->reserva->propiedad->titulo ?? null
                ];
            });
    }

    /**
     * Obtener una reseña por ID
     */
    public static function getById($id)
    {
        $resena = self::with(['reserva.propiedad', 'reserva.inquilino'])->find($id);
        
        if (!$resena) {
            return null;
        }
        
        return [
            'id' => $resena->id,
            'reserva_id' => $resena->reserva_id,
            'calificacion' => $resena->calificacion,
            'comentario' => $resena->comentario,
            'fecha_publicacion' => $resena->fecha_publicacion,
            'usuario_nombre' => $resena->reserva->inquilino ? 
                ($resena->reserva->inquilino->nombre . ' ' . $resena->reserva->inquilino->apellido) : null,
            'usuario_email' => $resena->reserva->inquilino->email ?? null,
            'propiedad_titulo' => $resena->reserva->propiedad->titulo ?? null,
            'propiedad_id' => $resena->reserva->propiedad_id ?? null
        ];
    }

    /**
     * Obtener reseñas por propiedad
     */
    public static function getByPropiedad($propiedadId)
    {
        return self::whereHas('reserva', function($query) use ($propiedadId) {
                $query->where('propiedad_id', $propiedadId);
            })
            ->with(['reserva.inquilino'])
            ->orderBy('fecha_publicacion', 'desc')
            ->get()
            ->map(function($resena) {
                return [
                    'id' => $resena->id,
                    'calificacion' => $resena->calificacion,
                    'comentario' => $resena->comentario,
                    'fecha_publicacion' => $resena->fecha_publicacion,
                    'usuario_nombre' => $resena->reserva->inquilino ? 
                        ($resena->reserva->inquilino->nombre . ' ' . $resena->reserva->inquilino->apellido) : null
                ];
            });
    }

    /**
     * Obtener reseñas por usuario (inquilino)
     */
    public static function getByUsuario($usuarioId)
    {
        return self::whereHas('reserva', function($query) use ($usuarioId) {
                $query->where('inquilino_id', $usuarioId);
            })
            ->with(['reserva.propiedad'])
            ->orderBy('fecha_publicacion', 'desc')
            ->get()
            ->map(function($resena) {
                return [
                    'id' => $resena->id,
                    'calificacion' => $resena->calificacion,
                    'comentario' => $resena->comentario,
                    'fecha_publicacion' => $resena->fecha_publicacion,
                    'propiedad_titulo' => $resena->reserva->propiedad->titulo ?? null
                ];
            });
    }

    /**
     * Obtener calificación promedio de una propiedad
     */
    public static function getPromedioByPropiedad($propiedadId)
    {
        $result = self::whereHas('reserva', function($query) use ($propiedadId) {
                $query->where('propiedad_id', $propiedadId);
            })
            ->selectRaw('AVG(calificacion) as promedio, COUNT(*) as total')
            ->first();
        
        return [
            'promedio' => round($result->promedio ?? 0, 1),
            'total' => (int)($result->total ?? 0)
        ];
    }

    /**
     * Crear una nueva reseña
     */
    public static function createResena($data)
    {
        $data['fecha_publicacion'] = date('Y-m-d H:i:s');
        return self::create($data);
    }

    /**
     * Actualizar una reseña
     */
    public static function updateResena($id, $data)
    {
        $resena = self::find($id);
        if (!$resena) {
            return false;
        }
        return $resena->update($data);
    }

    /**
     * Eliminar una reseña
     */
    public static function deleteResena($id)
    {
        $resena = self::find($id);
        if (!$resena) {
            return false;
        }
        return $resena->delete();
    }

    /**
     * Verificar si existe una reseña
     */
    public static function exists($id)
    {
        return self::where('id', $id)->exists();
    }

    /**
     * Verificar si ya existe una reseña para una reserva
     */
    public static function existePorReserva($reservaId)
    {
        return self::where('reserva_id', $reservaId)->exists();
    }

    /**
     * Verificar si la reserva existe y está finalizada
     */
    public static function reservaExistsAndFinalizada($reservaId)
    {
        return Reserva::where('id', $reservaId)
            ->where('estado', 'finalizada')
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * Obtener estadísticas de reseñas
     */
    public static function getEstadisticas()
    {
        $total = self::count();
        
        $promedioGeneral = self::avg('calificacion');
        
        $distribucion = self::selectRaw('calificacion, COUNT(*) as cantidad')
            ->groupBy('calificacion')
            ->orderBy('calificacion', 'desc')
            ->get()
            ->toArray();
        
        return [
            'total' => $total,
            'promedio_general' => round($promedioGeneral ?? 0, 1),
            'distribucion' => $distribucion
        ];
    }
}