<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PensionEstudiante;
use App\Models\OtroCostoEstudiante;
use App\Models\CostoMoraEstudiante;

class FichaCostoEstudiante extends Model
{
    protected $table = 'fichas_costos_estudiantes';

    protected $fillable = [
        'student_id',
        'sede_id',
        'periodo_lectivo_id',
        'tipo_pago',
        'mes_causado',
        'saldo_anterior',
        'matricula',
        'costos_academicos',
        'deudas',
        'otras_deudas',
        'abonos',
        'total_deuda',
        'pension_inicial',
        'pagare_no',
        'observaciones',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function periodoLectivo()
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }

    public function pensiones()
    {
        return $this->hasMany(PensionEstudiante::class, 'ficha_costo_estudiante_id');
    }

    public function otrosCostos()
    {
        return $this->hasMany(OtroCostoEstudiante::class, 'ficha_costo_estudiante_id');
    }

    public function moras()
    {
        return $this->hasMany(CostoMoraEstudiante::class, 'ficha_costo_estudiante_id');
    }

    

}