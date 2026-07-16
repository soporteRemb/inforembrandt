<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoLectivo extends Model
{
    protected $table = 'periodos_lectivos';

    protected $fillable = [
        'sede_id',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }


    public function pensumAcademicos()
    {
        return $this->hasMany(PensumAcademico::class);
    }


    public function periodosAcademicos()
    {
        return $this->hasMany(PeriodoAcademico::class);
    }

public function operacionesPago() { return $this->hasMany(OperacionPago::class); }
    public function recibosPago() { return $this->hasMany(ReciboPago::class); }
    public function saldosFavorEstudiantes() { return $this->hasMany(SaldoFavorEstudiante::class); }
    public function acuerdosPagoEstudiantes() { return $this->hasMany(AcuerdoPagoEstudiante::class); }
    public function extractosEstudiantes() { return $this->hasMany(ExtractoEstudiante::class); }

}