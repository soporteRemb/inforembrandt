<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreMatriculaDocumento extends Model
{
    use HasFactory;

    protected $table = 'pre_matricula_documentos';

    protected $fillable = [
        'pre_matricula_id',
        'tipo_documento',
        'nombre_original',
        'ruta_archivo',
        'mime_type',
        'tamano',
        'origen',
        'subido_por',
    ];

    public function preMatricula(): BelongsTo
    {
        return $this->belongsTo(
            PreMatricula::class,
            'pre_matricula_id'
        );
    }

    public function usuarioCarga(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'subido_por'
        );
    }
}