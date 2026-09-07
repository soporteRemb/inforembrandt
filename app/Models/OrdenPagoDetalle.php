<?php

namespace App\Models;
use App\Models\Sede;
use App\Models\PeriodoLectivo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenPagoDetalle extends Model
{
    use HasFactory;

    protected $table = 'ordenes_pago_detalles';

    protected $fillable = [
        'orden_pago_id',

        'student_id',

        'movimiento_cartera_estudiante_id',
        'concepto_cobro_id',

        'concepto',
        'descripcion',
        'tipo_concepto',
        'mes',
        'mes_numero',
        'fecha_vencimiento',

        'valor_obligacion',
        'saldo_al_seleccionar',
        'valor_a_pagar',

        'estado',

        'aplicacion_pago_id',

        'sede_id',
        'periodo_lectivo_id',
    ];

    protected $casts = [
        'mes_numero' => 'integer',
        'fecha_vencimiento' => 'date',

        'valor_obligacion' => 'decimal:2',
        'saldo_al_seleccionar' => 'decimal:2',
        'valor_a_pagar' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function ordenPago(): BelongsTo
    {
        return $this->belongsTo(
            OrdenPago::class,
            'orden_pago_id'
        );
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(
            Student::class,
            'student_id'
        );
    }

    public function movimientoCartera(): BelongsTo
    {
        return $this->belongsTo(
            MovimientoCarteraEstudiante::class,
            'movimiento_cartera_estudiante_id'
        );
    }

    public function conceptoCobro(): BelongsTo
    {
        return $this->belongsTo(
            ConceptoCobro::class,
            'concepto_cobro_id'
        );
    }

    public function aplicacionPago(): BelongsTo
    {
        return $this->belongsTo(
            AplicacionPago::class,
            'aplicacion_pago_id'
        );
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function periodoLectivo()
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }
}