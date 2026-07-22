<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AcuerdoPagoEstudiante extends Model
{
    public const ESTADO_VIGENTE = 'vigente';
    public const ESTADO_CUMPLIDO = 'cumplido';
    public const ESTADO_INCUMPLIDO = 'incumplido';
    public const ESTADO_VENCIDO = 'vencido';
    public const ESTADO_ANULADO = 'anulado';

    protected $table = 'acuerdos_pago_estudiantes';
    protected $fillable = ['uuid', 'sede_id', 'periodo_lectivo_id', 'student_id', 'texto_acuerdo', 'persona_acuerdo', 'parentesco', 'fecha_compromiso', 'valor_comprometido', 'estado', 'registrado_por', 'registrado_en', 'anulado_por', 'anulado_en', 'motivo_anulacion', 'estado_modificado_por', 'estado_modificado_en',];
    protected $casts = ['fecha_compromiso' => 'date', 'valor_comprometido' => 'decimal:2', 'registrado_en' => 'datetime', 'anulado_en' => 'datetime', 'estado_modificado_en' => 'datetime',];

    protected static function booted(): void { static::creating(fn (self $modelo) => $modelo->uuid ??= (string) Str::uuid()); }
    public static function estados(): array { return [self::ESTADO_VIGENTE, self::ESTADO_CUMPLIDO, self::ESTADO_INCUMPLIDO, self::ESTADO_VENCIDO, self::ESTADO_ANULADO]; }
    public function sede(): BelongsTo { return $this->belongsTo(Sede::class); }
    public function periodoLectivo(): BelongsTo { return $this->belongsTo(PeriodoLectivo::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function registradoPor(): BelongsTo { return $this->belongsTo(User::class, 'registrado_por'); }
    public function anuladoPor(): BelongsTo { return $this->belongsTo(User::class, 'anulado_por'); }
    public function estadoModificadoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'estado_modificado_por'
        );
    }
    public function evidencias(): HasMany { return $this->hasMany(EvidenciaAcuerdoPago::class); }
}
