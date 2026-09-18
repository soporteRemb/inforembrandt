<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirmaElectronicaEstudiante extends Model
{
    protected $table =
        'firma_electronica_estudiantes';

    protected $fillable = [
        'student_id',
        'iniciada_por',
        'proveedor',
        'estado',
        'request_id',
        'contract_id',
        'signature_id',
        'config_id',
        'nombre_archivo_original',
        'nombre_archivo_firmado',
        'ultimo_error',
        'respuesta_inicio',
        'respuesta_estado',
        'iniciada_en',
        'firmada_en',
        'rechazada_en',
        'expirada_en',
    ];

    protected $casts = [
        'respuesta_inicio' =>
            'array',

        'respuesta_estado' =>
            'array',

        'iniciada_en' =>
            'datetime',

        'firmada_en' =>
            'datetime',

        'rechazada_en' =>
            'datetime',

        'expirada_en' =>
            'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(
            Student::class
        );
    }

    public function usuarioInicio(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'iniciada_por'
        );
    }
}