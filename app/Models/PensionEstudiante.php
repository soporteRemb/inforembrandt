<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PensionEstudiante extends Model
{
    protected $table = 'pensiones_estudiante';

    protected $fillable = [
        'ficha_costo_estudiante_id',
        'mes',
        'mes_numero',
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