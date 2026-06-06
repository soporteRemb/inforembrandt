<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoletinDesempeno extends Model
{
    protected $table = 'boletin_desempenos';

    protected $fillable = [
        'sede_id',
        'periodo_lectivo_id',
        'grado',
        'periodo_academico',
        'pensum_academico_id',
        'desempeno_1',
        'desempeno_2',
        'desempeno_3',
        'desempeno_4',
        'created_by',
        'updated_by',
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
}