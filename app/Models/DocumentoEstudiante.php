<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoEstudiante extends Model
{
    protected $table = 'documentos_estudiante';

    protected $fillable = [
        'student_id',
        'tipo_documento',
        'nombre_original',
        'ruta_archivo',
        'mime_type',
        'tamano',
        'origen',
        'subido_por',
        'pre_matricula_documento_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function usuarioCarga(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'subido_por'
        );
    }

    public function documentoPreMatricula(): BelongsTo
    {
        return $this->belongsTo(
            PreMatriculaDocumento::class,
            'pre_matricula_documento_id'
        );
    }
}