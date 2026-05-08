<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionConceptoVencimiento extends Model
{
    protected $table = 'asignacion_concepto_vencimientos';

    protected $fillable = [
        'asignacion_concepto_id',
        'mes',
        'fecha_vencimiento',
        'porcentaje',
        'dias',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function asignacionConcepto()
    {
        return $this->belongsTo(
            AsignacionConcepto::class,
            'asignacion_concepto_id'
        );
    }
}