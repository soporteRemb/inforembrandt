<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenciaAcuerdoPago extends Model
{
    protected $table = 'evidencias_acuerdos_pago';
    protected $fillable = ['acuerdo_pago_estudiante_id', 'nombre_original', 'ruta', 'tipo_archivo', 'tamano', 'cargado_por', 'cargado_en'];
    protected $casts = ['tamano' => 'integer', 'cargado_en' => 'datetime'];

    public function acuerdoPagoEstudiante(): BelongsTo { return $this->belongsTo(AcuerdoPagoEstudiante::class); }
    public function cargadoPor(): BelongsTo { return $this->belongsTo(User::class, 'cargado_por'); }
}
