<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtroCostoEstudiante extends Model
{
    protected $table = 'otros_costos_estudiante';

    protected $fillable = [
        'ficha_costo_estudiante_id',
        'concepto_cobro_id',
        'nombre_concepto',
        'valor_base',
        'valor_personalizado',
        'modificado_manual',
    ];

    protected $casts = [
        'modificado_manual' => 'boolean',
    ];

    public function ficha()
    {
        return $this->belongsTo(FichaCostoEstudiante::class, 'ficha_costo_estudiante_id');
    }
}