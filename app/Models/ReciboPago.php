<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ReciboPago extends Model
{
    public const TIPO_OBLIGACION = 'obligacion';
    public const TIPO_ANTICIPO_CONCEPTO = 'anticipo_concepto';
    public const TIPO_SALDO_FAVOR = 'saldo_favor';

    public const ESTADO_CONFIRMADO = 'confirmado';
    public const ESTADO_ANULADO = 'anulado';

    protected $table = 'recibos_pago';

    protected $fillable = [
        'uuid', 'operacion_pago_id', 'sede_id', 'periodo_lectivo_id', 'student_id',
        'concepto_cobro_id', 'numero_recibo', 'anio', 'tipo_registro', 'mes', 'mes_numero',
        'valor_ordinario', 'tipo_limite_extemporaneo_id', 'valor_vigente', 'penalizacion',
        'descuento', 'valor_recibido', 'valor_aplicado', 'saldo_favor_generado',
        'recibido_de', 'detalle', 'estado', 'recibido_por', 'fecha_pago',
        'anulado_por', 'anulado_en', 'motivo_anulacion',
    ];

    protected $casts = [
        'numero_recibo' => 'integer', 'anio' => 'integer', 'mes_numero' => 'integer',
        'valor_ordinario' => 'decimal:2', 'valor_vigente' => 'decimal:2',
        'penalizacion' => 'decimal:2', 'descuento' => 'decimal:2',
        'valor_recibido' => 'decimal:2', 'valor_aplicado' => 'decimal:2',
        'saldo_favor_generado' => 'decimal:2', 'fecha_pago' => 'datetime', 'anulado_en' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $modelo) => $modelo->uuid ??= (string) Str::uuid());
    }

    public static function tiposRegistro(): array
    {
        return [self::TIPO_OBLIGACION, self::TIPO_ANTICIPO_CONCEPTO, self::TIPO_SALDO_FAVOR];
    }

    public static function estados(): array
    {
        return [self::ESTADO_CONFIRMADO, self::ESTADO_ANULADO];
    }

    public function operacionPago(): BelongsTo { return $this->belongsTo(OperacionPago::class); }
    public function sede(): BelongsTo { return $this->belongsTo(Sede::class); }
    public function periodoLectivo(): BelongsTo { return $this->belongsTo(PeriodoLectivo::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function conceptoCobro(): BelongsTo { return $this->belongsTo(ConceptoCobro::class); }
    public function tipoLimiteExtemporaneo(): BelongsTo { return $this->belongsTo(TipoLimiteExtemporaneo::class); }
    public function recibidoPor(): BelongsTo { return $this->belongsTo(User::class, 'recibido_por'); }
    public function anuladoPor(): BelongsTo { return $this->belongsTo(User::class, 'anulado_por'); }
    public function formasPago(): HasMany { return $this->hasMany(ReciboFormaPago::class); }
    public function aplicaciones(): HasMany { return $this->hasMany(AplicacionPago::class); }
    public function movimientosSaldoFavor(): HasMany { return $this->hasMany(MovimientoSaldoFavor::class); }
    public function impresiones(): HasMany { return $this->hasMany(ImpresionRecibo::class); }
}
