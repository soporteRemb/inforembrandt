<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;


class BoletinGenerado extends Model
{
    protected $table = 'boletines_generados';

    protected $fillable = [
        'periodo_lectivo_id',
        'periodo_academico_id',
        'course_id',
        'student_id',
        'created_by',
        'updated_by',
        'generated_by',
        'published_by',
        'codigos_perfil',
        'codigos_acompanamiento',
        'observaciones',
        'estado',
        'pdf_path',
        'generado_en',
        'publicado_en',
        'uuid',
    ];

    protected $casts = [
        'codigos_perfil' => 'array',
        'codigos_acompanamiento' => 'array',
        'generado_en' => 'datetime',
        'publicado_en' => 'datetime',
    ];

    public function periodoLectivo(): BelongsTo
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function booted(): void
    {
        static::creating(function ($boletin) {

            if (empty($boletin->uuid)) {
                $boletin->uuid = (string) Str::uuid();
            }

        });
    }
}