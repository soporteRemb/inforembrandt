<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ExtractoEstudiante extends Model
{
    protected $table = 'extractos_estudiantes';
    protected $fillable = ['uuid', 'sede_id', 'periodo_lectivo_id', 'student_id', 'fecha_corte', 'ruta_pdf', 'generado_por', 'generado_en'];
    protected $casts = ['fecha_corte' => 'datetime', 'generado_en' => 'datetime'];

    protected static function booted(): void { static::creating(fn (self $modelo) => $modelo->uuid ??= (string) Str::uuid()); }
    public function sede(): BelongsTo { return $this->belongsTo(Sede::class); }
    public function periodoLectivo(): BelongsTo { return $this->belongsTo(PeriodoLectivo::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function generadoPor(): BelongsTo { return $this->belongsTo(User::class, 'generado_por'); }
}
