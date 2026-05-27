<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaEstudiante extends Model
{
    protected $table = 'notas_estudiantes';

    protected $fillable = [
        'student_id',
        'pensum_academico_id',
        'periodo',
        'nota',
        'fallas',
        'mejoramiento',
        'observacion',
        'mejoramiento_01',
        'mejoramiento_02',
        'mejoramiento_03',
        'mejoramiento_04',
        'pgc',
    ];

    protected $casts = [
        'nota' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function pensumAcademico()
    {
        return $this->belongsTo(PensumAcademico::class, 'pensum_academico_id');
    }
}