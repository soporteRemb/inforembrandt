<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $fillable = [
        'nombre',
        'nit',
        'razon_social',
        'email',
        'telefono',
        'direccion',
        'logo',
        'activa',
    ];

    public function sedes()
    {
        return $this->hasMany(Sede::class);
    }
}
