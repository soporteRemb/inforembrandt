<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoAcademico extends Model
{
    protected $table = 'periodos_academicos';

    protected $fillable = [
        'periodo_lectivo_id',
        'numero',
        'nombre',
        'estado',
    ];

    public function periodoLectivo()
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }
}
