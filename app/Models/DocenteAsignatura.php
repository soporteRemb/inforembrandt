<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocenteAsignatura extends Model
{
    protected $table = 'docente_asignaturas';

    protected $fillable = [
        'docente_id',
        'course_id',
        'pensum_academico_id',
    ];

    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function pensumAcademico()
    {
        return $this->belongsTo(PensumAcademico::class, 'pensum_academico_id');
    }
}