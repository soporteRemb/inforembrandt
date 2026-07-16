<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaldoFavorEstudiante extends Model
{
    protected $table = 'saldos_favor_estudiantes';
    protected $fillable = ['sede_id', 'periodo_lectivo_id', 'student_id', 'saldo_disponible'];
    protected $casts = ['saldo_disponible' => 'decimal:2'];

    public function sede(): BelongsTo { return $this->belongsTo(Sede::class); }
    public function periodoLectivo(): BelongsTo { return $this->belongsTo(PeriodoLectivo::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function movimientos(): HasMany { return $this->hasMany(MovimientoSaldoFavor::class); }
}
