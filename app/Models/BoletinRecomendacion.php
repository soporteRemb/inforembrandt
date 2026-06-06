<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoletinRecomendacion extends Model
{
    protected $table = 'boletin_recomendaciones';

    protected $fillable = [
        'sede_id',
        'periodo_lectivo_id',
        'grado',
        'periodo_academico',
        'pensum_academico_id',
        'tipo',
        'codigo',
        'descripcion',
        'activo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function periodoLectivo()
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }

    public function pensumAcademico()
    {
        return $this->belongsTo(PensumAcademico::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function asignacionesEstudiantes()
    {
        return $this->hasMany(BoletinRecomendacionEstudiante::class);
    }
}