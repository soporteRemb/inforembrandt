<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenPago extends Model
{
    use HasFactory;

    protected $table = 'ordenes_pago';

    protected $fillable = [
        'uuid',
        'anio',
        'consecutivo',
        'referencia',

        'user_id',

        'metodo_pago',
        'estado',

        'subtotal',
        'total',

        'proveedor',
        'referencia_proveedor',
        'transaccion_proveedor',
        'respuesta_proveedor',

        'iniciada_en',
        'confirmada_en',
        'rechazada_en',
        'expira_en',
    ];

    protected $casts = [
        'anio' => 'integer',
        'consecutivo' => 'integer',

        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',

        'respuesta_proveedor' => 'array',

        'iniciada_en' => 'datetime',
        'confirmada_en' => 'datetime',
        'rechazada_en' => 'datetime',
        'expira_en' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function detalles(): HasMany
    {
        return $this->hasMany(
            OrdenPagoDetalle::class,
            'orden_pago_id'
        );
    }
}