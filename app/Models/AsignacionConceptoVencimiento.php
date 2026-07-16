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
        'tipo_limite_extemporaneo_id',
        'valor',
    ];

    public function asignacionConcepto()
    {
        return $this->belongsTo(AsignacionConcepto::class, 'asignacion_concepto_id');
    }

   

    public function tipoLimiteExtemporaneo()
    {
        return $this->belongsTo(TipoLimiteExtemporaneo::class, 'tipo_limite_extemporaneo_id');
    }
}