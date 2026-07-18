<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OperacionPago extends Model
{
    public const ESTADO_CONFIRMADA = 'confirmada';
    public const ESTADO_ANULADA_PARCIALMENTE = 'anulada_parcialmente';
    public const ESTADO_ANULADA = 'anulada';

    protected $table = 'operaciones_pago';

    protected $fillable = [
        'uuid', 'sede_id', 'periodo_lectivo_id', 'student_id', 'recibido_de',
        'subtotal', 'total_descuentos', 'total_recibido', 'estado',
        'registrado_por', 'registrado_en', 'anulado_por', 'anulado_en', 'motivo_anulacion',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total_descuentos' => 'decimal:2',
        'total_recibido' => 'decimal:2',
        'registrado_en' => 'datetime',
        'anulado_en' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $modelo) => $modelo->uuid ??= (string) Str::uuid());
    }

    public static function estados(): array
    {
        return [self::ESTADO_CONFIRMADA, self::ESTADO_ANULADA_PARCIALMENTE, self::ESTADO_ANULADA];
    }

    public function sede(): BelongsTo { return $this->belongsTo(Sede::class); }
    public function periodoLectivo(): BelongsTo { return $this->belongsTo(PeriodoLectivo::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function registradoPor(): BelongsTo { return $this->belongsTo(User::class, 'registrado_por'); }
    public function anuladoPor(): BelongsTo { return $this->belongsTo(User::class, 'anulado_por'); }
    public function recibos(): HasMany { return $this->hasMany(ReciboPago::class); }

    public function impresiones(): HasMany
    {
        return $this->hasMany(ImpresionRecibo::class);
    }
}
