<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AplicacionPago extends Model
{
    protected $table = 'aplicaciones_pago';
    protected $fillable = ['recibo_pago_id', 'movimiento_cartera_estudiante_id', 'valor_aplicado', 'saldo_anterior', 'saldo_posterior'];
    protected $casts = ['valor_aplicado' => 'decimal:2', 'saldo_anterior' => 'decimal:2', 'saldo_posterior' => 'decimal:2'];

    public function reciboPago(): BelongsTo { return $this->belongsTo(ReciboPago::class); }
    public function movimientoCarteraEstudiante(): BelongsTo { return $this->belongsTo(MovimientoCarteraEstudiante::class); }
    public function movimientosSaldoFavor(): HasMany { return $this->hasMany(MovimientoSaldoFavor::class); }
}
