<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    protected $fillable = [
        'empresa_id',
        'nombre',
        'codigo',
        'direccion',
        'telefono',
        'email',
        'logo',
        'activa',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}