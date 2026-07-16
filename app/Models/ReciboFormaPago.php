<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReciboFormaPago extends Model
{
    protected $table = 'recibos_formas_pago';
    protected $fillable = ['recibo_pago_id', 'forma_pago_id', 'valor', 'numero_referencia', 'fecha_consignacion'];
    protected $casts = ['valor' => 'decimal:2', 'fecha_consignacion' => 'date'];

    public function reciboPago(): BelongsTo { return $this->belongsTo(ReciboPago::class); }
    public function formaPago(): BelongsTo { return $this->belongsTo(FormaPago::class); }
}
