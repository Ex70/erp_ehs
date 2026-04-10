<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'folio', 'user_id', 'tipo_falla_id', 'categoria_servicio_id',
        'prioridad', 'seguimiento', 'descripcion', 'evidencia',
        'resolucion', 'fecha_cierre', 'calificacion', 'comentario_calificacion',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
    ];

    // Relaciones
    public function solicitante()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoFalla()
    {
        return $this->belongsTo(TipoFalla::class);
    }

    public function categoriaServicio()
    {
        return $this->belongsTo(CategoriaServicio::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(TicketAsignacion::class);
    }

    public function tecnicos()
    {
        return $this->belongsToMany(User::class, 'ticket_asignaciones')
                    ->wherePivot('activo', true);
    }

    public function seguimientos()
    {
        return $this->hasMany(TicketSeguimiento::class)->orderBy('created_at');
    }

    // Helpers
    public static function generarFolio(): string
    {
        $ultimo = static::withTrashed()->orderByDesc('id')->value('folio');
        if (!$ultimo) return 'GQZSIS0001';
        $num = (int) substr($ultimo, 6);
        return 'GQZSIS' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    }

    public static function coloresPrioridad(): array
    {
        return [
            'baja'    => 'secondary',
            'media'   => 'info',
            'alta'    => 'warning',
            'urgente' => 'danger',
        ];
    }

    public static function coloresSeguimiento(): array
    {
        return [
            'pendiente'      => 'warning',
            'en_atencion'    => 'primary',
            'en_desarrollo'  => 'purple',
            'en_pruebas'     => 'info',
            'finalizado'     => 'success',
            'escalado'       => 'danger',
        ];
    }

    public static function etiquetasSeguimiento(): array
    {
        return [
            'pendiente'      => 'Pendiente',
            'en_atencion'    => 'En atención',
            'en_desarrollo'  => 'En desarrollo',
            'en_pruebas'     => 'En pruebas',
            'finalizado'     => 'Finalizado',
            'escalado'       => 'Escalado',
        ];
    }

    public function getPrioridadLabelAttribute(): string
    {
        return ucfirst($this->prioridad);
    }

    public function getSeguimientoLabelAttribute(): string
    {
        return static::etiquetasSeguimiento()[$this->seguimiento] ?? $this->seguimiento;
    }
}