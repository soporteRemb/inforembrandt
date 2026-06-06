<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoletinRecomendacionEstudiante extends Model
{
    protected $table = 'boletin_recomendaciones_estudiante';

    protected $fillable = [
        'student_id',
        'boletin_recomendacion_id',
        'periodo_academico',
        'orden',
        'created_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recomendacion()
    {
        return $this->belongsTo(BoletinRecomendacion::class, 'boletin_recomendacion_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}