<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionCostosEstudiante extends Model
{
    protected $table = 'asignacion_costos_estudiantes';

    protected $fillable = [
        'student_id',
        'sede_id',
        'periodo_lectivo_id',
        'saldo_anterior',
        'matricula',
        'costos_academicos',
        'pension_febrero',
        'pension_marzo',
        'pension_abril',
        'pension_mayo',
        'pension_junio',
        'pension_julio',
        'pension_agosto',
        'pension_septiembre',
        'pension_octubre',
        'pension_noviembre',
        'otros_costos_academicos',
    ];

    protected $casts = [
        'saldo_anterior' => 'decimal:2',
        'matricula' => 'decimal:2',
        'costos_academicos' => 'decimal:2',
        'pension_febrero' => 'decimal:2',
        'pension_marzo' => 'decimal:2',
        'pension_abril' => 'decimal:2',
        'pension_mayo' => 'decimal:2',
        'pension_junio' => 'decimal:2',
        'pension_julio' => 'decimal:2',
        'pension_agosto' => 'decimal:2',
        'pension_septiembre' => 'decimal:2',
        'pension_octubre' => 'decimal:2',
        'pension_noviembre' => 'decimal:2',
        'otros_costos_academicos' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function periodoLectivo(): BelongsTo
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }
}