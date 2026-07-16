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

public function consecutivosRecibos() { return $this->hasMany(ConsecutivoRecibo::class); }
    public function operacionesPago() { return $this->hasMany(OperacionPago::class); }
    public function recibosPago() { return $this->hasMany(ReciboPago::class); }
    public function saldosFavorEstudiantes() { return $this->hasMany(SaldoFavorEstudiante::class); }
    public function acuerdosPagoEstudiantes() { return $this->hasMany(AcuerdoPagoEstudiante::class); }
    public function extractosEstudiantes() { return $this->hasMany(ExtractoEstudiante::class); }

}