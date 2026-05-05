<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'sede_id',
        'periodo_lectivo_id',
        'anio',
        'grado',
        'descripcion',
        'curso',
        'jornada',
        'cupo',
        'estado',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function periodoLectivo()
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}