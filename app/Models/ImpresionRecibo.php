<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImpresionRecibo extends Model
{
    public const TIPO_ORIGINAL = 'original';
    public const TIPO_REIMPRESION = 'reimpresion';

    public const MEDIO_PDF = 'pdf';
    public const MEDIO_IMPRESORA = 'impresora';

    protected $table = 'impresiones_recibos';

    protected $fillable = [
        'operacion_pago_id',
        'recibo_pago_id',
        'tipo',
        'numero_reimpresion',
        'medio',
        'ruta_pdf',
        'motivo',
        'generado_por',
        'generado_en',
    ];

    protected $casts = [
        'numero_reimpresion' => 'integer',
        'generado_en' => 'datetime',
    ];

    public static function tipos(): array
    {
        return [
            self::TIPO_ORIGINAL,
            self::TIPO_REIMPRESION,
        ];
    }

    public static function medios(): array
    {
        return [
            self::MEDIO_PDF,
            self::MEDIO_IMPRESORA,
        ];
    }

    public function operacionPago(): BelongsTo
    {
        return $this->belongsTo(OperacionPago::class);
    }

    /**
     * Relación histórica.
     *
     * Los nuevos eventos de impresión se asociarán principalmente
     * mediante operacion_pago_id.
     */
    public function reciboPago(): BelongsTo
    {
        return $this->belongsTo(ReciboPago::class);
    }

    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generado_por');
    }
}