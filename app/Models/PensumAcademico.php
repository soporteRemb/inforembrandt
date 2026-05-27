<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PensumAcademico extends Model
{
    protected $table = 'pensum_academicos';

    protected $fillable = [
        'sede_id',
        'periodo_lectivo_id',
        'course_id',
        'grado',
        'docente_id',
        'codigo',
        'orden',
        'nombre',
        'nombre_corto',
        'tipo',
        'intensidad_horaria',
        'forma_evaluar',
        'estado',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function periodoLectivo()
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }

    public function notas()
    {
        return $this->hasMany(NotaEstudiante::class, 'pensum_academico_id');
    }
}