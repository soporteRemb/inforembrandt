<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionConcepto extends Model
{
    protected $table = 'asignacion_conceptos';

    protected $fillable = [
        'sede_id',
        'periodo_lectivo_id',
        'grado',
        'concepto_cobro_id',
        'tarifa_ordinaria',
        'tarifa_extemporanea',
        'orden',
        'activo',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function periodoLectivo()
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }

    public function conceptoCobro()
    {
        return $this->belongsTo(ConceptoCobro::class);
    }

    public function vencimientos()
    {
        return $this->hasMany(
            AsignacionConceptoVencimiento::class,
            'asignacion_concepto_id'
        );
    }
}