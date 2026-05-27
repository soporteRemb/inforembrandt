<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RangoDesempenoNota extends Model
{
    protected $table = 'rangos_desempeno_notas';

    protected $fillable = [
        'nombre',
        'desde',
        'hasta',
        'orden',
        'activo',
    ];
}