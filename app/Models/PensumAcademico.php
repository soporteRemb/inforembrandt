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

    public function docentesAsignados()
    {
        return $this->hasMany(DocenteAsignatura::class, 'pensum_academico_id');
    }

    public function docentePreferido(int $courseId): ?Docente
    {
        // Primero busca un docente ACTIVO
        $activo = $this->docentesAsignados()
            ->where('course_id', $courseId)
            ->whereHas('docente', function ($query) {
                $query->where('estado', 'activo');
            })
            ->with('docente')
            ->first();

        if ($activo) {
            return $activo->docente;
        }

        // Si no existe activo, devuelve el primero disponible
        $cualquiera = $this->docentesAsignados()
            ->where('course_id', $courseId)
            ->with('docente')
            ->first();

        return $cualquiera?->docente;
    }

    public function notas()
    {
        return $this->hasMany(NotaEstudiante::class, 'pensum_academico_id');
    }
}