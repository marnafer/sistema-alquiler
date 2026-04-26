<?php
/**
 * Modelo de Reservas (versión Eloquent)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reserva extends Model
{
    use SoftDeletes;

    protected $table = 'reservas';
    public $timestamps = false;

    protected $fillable = [
        'propiedad_id',
        'inquilino_id',
        'fecha_desde',
        'fecha_hasta',
        'precio_total',
        'estado'
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
        'fecha_reserva' => 'datetime'
    ];

    /**
     * Relación con Propiedad
     */
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }

    /**
     * Relación con Usuario (inquilino)
     */
    public function inquilino()
    {
        return $this->belongsTo(Usuario::class, 'inquilino_id');
    }

    /**
     * Relación con Reseña
     */
    public function resena()
    {
        return $this->hasOne(Resena::class, 'reserva_id');
    }

    /**
     * Obtener todas las reservas
     */
    public function getAll()
    {
        return self::with(['propiedad', 'inquilino'])
            ->whereNull('deleted_at')
            ->orderBy('fecha_reserva', 'desc')
            ->get()
            ->map(function($reserva) {
                return [
                    'id' => $reserva->id,
                    'propiedad_id' => $reserva->propiedad_id,
                    'inquilino_id' => $reserva->inquilino_id,
                    'fecha_desde' => $reserva->fecha_desde,
                    'fecha_hasta' => $reserva->fecha_hasta,
                    'precio_total' => $reserva->precio_total,
                    'estado' => $reserva->estado,
                    'fecha_reserva' => $reserva->fecha_reserva,
                    'propiedad_titulo' => $reserva->propiedad->titulo ?? null,
                    'inquilino_nombre' => $reserva->inquilino ? ($reserva->inquilino->nombre . ' ' . $reserva->inquilino->apellido) : null
                ];
            });
    }

    /**
     * Obtener una reserva por ID
     */
    public function getById($id)
    {
        $reserva = self::with(['propiedad', 'inquilino'])
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();
        
        if (!$reserva) {
            return null;
        }
        
        return [
            'id' => $reserva->id,
            'propiedad_id' => $reserva->propiedad_id,
            'inquilino_id' => $reserva->inquilino_id,
            'fecha_desde' => $reserva->fecha_desde,
            'fecha_hasta' => $reserva->fecha_hasta,
            'precio_total' => $reserva->precio_total,
            'estado' => $reserva->estado,
            'fecha_reserva' => $reserva->fecha_reserva,
            'propiedad_titulo' => $reserva->propiedad->titulo ?? null,
            'inquilino_nombre' => $reserva->inquilino ? ($reserva->inquilino->nombre . ' ' . $reserva->inquilino->apellido) : null
        ];
    }

    /**
     * Crear una nueva reserva
     */
    public function createReserva($data)
    {
        return self::create($data);
    }

    /**
     * Actualizar una reserva
     */
    public function updateReserva($id, $data)
    {
        $reserva = self::find($id);
        if (!$reserva) {
            return false;
        }
        return $reserva->update($data);
    }

    /**
     * Eliminar reserva (soft delete)
     */
    public function deleteReserva($id)
    {
        $reserva = self::find($id);
        if (!$reserva) {
            return false;
        }
        return $reserva->delete();
    }

    /**
     * Verificar si existe una reserva
     */
    public function exists($id)
    {
        return self::where('id', $id)->whereNull('deleted_at')->exists();
    }

    /**
     * Cambiar estado de una reserva
     */
    public function changeStatus($id, $estado)
    {
        $reserva = self::find($id);
        if (!$reserva) {
            return false;
        }
        $reserva->estado = $estado;
        return $reserva->save();
    }

    /**
     * Verificar disponibilidad de una propiedad
     */
    public function checkAvailability($propiedadId, $fechaDesde, $fechaHasta)
    {
        $existe = self::where('propiedad_id', $propiedadId)
            ->whereNull('deleted_at')
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->where(function($query) use ($fechaDesde, $fechaHasta) {
                $query->where('fecha_desde', '<=', $fechaHasta)
                      ->where('fecha_hasta', '>=', $fechaDesde);
            })
            ->exists();
        
        return !$existe;
    }

    /**
     * Verificar si la propiedad existe
     */
    public function propiedadExists($id)
    {
        return Propiedad::where('id', $id)
            ->whereNull('deleted_at')
            ->where('disponible', 1)
            ->exists();
    }

    /**
     * Verificar si el inquilino existe
     */
    public function inquilinoExists($id)
    {
        return Usuario::where('id', $id)->whereNull('deleted_at')->exists();
    }

    /**
     * Obtener reservas por propiedad
     */
    public function getByPropiedad($propiedadId)
    {
        return self::with('inquilino')
            ->where('propiedad_id', $propiedadId)
            ->whereNull('deleted_at')
            ->orderBy('fecha_desde', 'desc')
            ->get()
            ->map(function($reserva) {
                return [
                    'id' => $reserva->id,
                    'fecha_desde' => $reserva->fecha_desde,
                    'fecha_hasta' => $reserva->fecha_hasta,
                    'precio_total' => $reserva->precio_total,
                    'estado' => $reserva->estado,
                    'inquilino_nombre' => $reserva->inquilino ? ($reserva->inquilino->nombre . ' ' . $reserva->inquilino->apellido) : null
                ];
            });
    }

    /**
     * Obtener reservas por inquilino
     */
    public function getByInquilino($inquilinoId)
    {
        return self::with('propiedad')
            ->where('inquilino_id', $inquilinoId)
            ->whereNull('deleted_at')
            ->orderBy('fecha_reserva', 'desc')
            ->get()
            ->map(function($reserva) {
                return [
                    'id' => $reserva->id,
                    'fecha_desde' => $reserva->fecha_desde,
                    'fecha_hasta' => $reserva->fecha_hasta,
                    'precio_total' => $reserva->precio_total,
                    'estado' => $reserva->estado,
                    'propiedad_titulo' => $reserva->propiedad->titulo ?? null
                ];
            });
    }
}