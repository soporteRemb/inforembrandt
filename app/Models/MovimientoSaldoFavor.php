<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoSaldoFavor extends Model
{
    public const TIPO_GENERACION = 'generacion';
    public const TIPO_APLICACION = 'aplicacion';
    public const TIPO_REVERSION = 'reversion';
    public const TIPO_AJUSTE = 'ajuste';

    protected $table = 'movimientos_saldos_favor';
    protected $fillable = ['saldo_favor_estudiante_id', 'recibo_pago_id', 'aplicacion_pago_id', 'tipo_movimiento', 'valor', 'saldo_anterior', 'saldo_posterior', 'registrado_por', 'registrado_en', 'detalle'];
    protected $casts = ['valor' => 'decimal:2', 'saldo_anterior' => 'decimal:2', 'saldo_posterior' => 'decimal:2', 'registrado_en' => 'datetime'];

    public static function tipos(): array { return [self::TIPO_GENERACION, self::TIPO_APLICACION, self::TIPO_REVERSION, self::TIPO_AJUSTE]; }
    public function saldoFavorEstudiante(): BelongsTo { return $this->belongsTo(SaldoFavorEstudiante::class); }
    public function reciboPago(): BelongsTo { return $this->belongsTo(ReciboPago::class); }
    public function aplicacionPago(): BelongsTo { return $this->belongsTo(AplicacionPago::class); }
    public function registradoPor(): BelongsTo { return $this->belongsTo(User::class, 'registrado_por'); }
}
