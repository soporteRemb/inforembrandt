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

    


}