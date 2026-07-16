<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormaPago extends Model
{
    protected $table = 'formas_pago';

    protected $fillable = [
        'nombre',
        'requiere_referencia',
        'requiere_fecha_consignacion',
        'orden',
        'activo',
    ];

    protected $casts = [
        'requiere_referencia' => 'boolean',
        'requiere_fecha_consignacion' => 'boolean',
        'orden' => 'integer',
        'activo' => 'boolean',
    ];

    public function recibosFormasPago(): HasMany
    {
        return $this->hasMany(ReciboFormaPago::class);
    }
}
