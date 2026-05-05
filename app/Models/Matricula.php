<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    protected $fillable = [
        'sede_id',
        'periodo_lectivo_id',
        'student_id',
        'course_id',
        'consecutivo',
        'fecha_matricula',
        'estado',
        'tipo_matricula',
        'beca_porcentaje',
        'observaciones',
    ];

    protected static function booted(): void
    {
        static::creating(function (Matricula $matricula) {
            $anio = date('Y');
            $ultimo = Matricula::whereYear('created_at', $anio)->max('consecutivo');
            $numero = $ultimo ? intval(substr($ultimo, 4)) + 1 : 1;
            $matricula->consecutivo = $anio . str_pad($numero, 4, '0', STR_PAD_LEFT);
        });
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function periodoLectivo()
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}