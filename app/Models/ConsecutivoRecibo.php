<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsecutivoRecibo extends Model
{
    protected $table = 'consecutivos_recibos';

    protected $fillable = ['sede_id', 'anio', 'ultimo_numero'];

    protected $casts = [
        'anio' => 'integer',
        'ultimo_numero' => 'integer',
    ];

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }
}
