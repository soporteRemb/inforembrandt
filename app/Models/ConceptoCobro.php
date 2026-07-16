<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConceptoCobro extends Model
{
    protected $table = 'concepto_cobros';

    protected $fillable = [
        'sede_id',
        'periodo_lectivo_id',
        'codigo',
        'descripcion',
        'tipo_movimiento',
        'control',
        'impuesto',
        'facturar',
        'obligatorio',
        'activo',
    ];

    protected $casts = [
        'codigo' => 'integer',
        'impuesto' => 'integer',
        'facturar' => 'boolean',
        'obligatorio' => 'boolean',
        'activo' => 'boolean',
    ];

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function periodoLectivo(): BelongsTo
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }

public function recibosPago()
    {
        return $this->hasMany(ReciboPago::class);
    }

    public function movimientosCartera()
    {
        return $this->hasMany(MovimientoCarteraEstudiante::class);
    }

}