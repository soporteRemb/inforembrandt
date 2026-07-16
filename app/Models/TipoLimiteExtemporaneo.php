<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoLimiteExtemporaneo extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'orden',
        'activo',
    ];
}