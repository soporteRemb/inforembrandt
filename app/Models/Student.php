<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Matricula;
use App\Models\StudentDocumento;
use App\Models\FichaCostoEstudiante;

class Student extends Model
{
    protected $fillable = [
        'sede_id',
        'periodo_lectivo_id',
        'course_id',
        'codigo',
        'folio',
        'foto',
        'apellidos',
        'nombres',
        'primer_apellido',
        'segundo_apellido',
        'primer_nombre',
        'segundo_nombre',
        'tipo_documento',
        'documento',
        'ciudad_expedicion',
        'sexo',
        'fecha_nacimiento',
        'edad',
        'ciudad_nacimiento',
        'numero_hermanos',
        'correo',
        'correo_familiar',
        'telefono_emergencia',
        'eps',
        'rh',
        'estado',
        'documentos_fisicos',
        'fecha_matricula',
        'control',
        'referido',
        'parentesco_matricula',
        'ultimo_grado',
        'anio_ultimo_grado',
        'institucion_anterior',
        'observaciones',
    ];

    protected $appends = ['apellidos', 'nombres'];

    protected $casts = [
        'documentos_fisicos' => 'array',
        'fecha_nacimiento'   => 'date',
    ];

    // ── Apellidos virtuales (combina primer + segundo apellido) ──────────────
    public function getApellidosAttribute(): string
    {
        return trim(($this->attributes['primer_apellido'] ?? '') . ' ' . ($this->attributes['segundo_apellido'] ?? ''));
    }

    public function setApellidosAttribute(string $value): void
    {
        $parts = explode(' ', trim($value), 2);
        $this->attributes['primer_apellido'] = trim($parts[0] ?? '');
        $this->attributes['segundo_apellido'] = trim($parts[1] ?? '');
    }

    // ── Nombres virtuales (combina primer + segundo nombre) ──────────────────
    public function getNombresAttribute(): string
    {
        return trim(($this->attributes['primer_nombre'] ?? '') . ' ' . ($this->attributes['segundo_nombre'] ?? ''));
    }

    public function setNombresAttribute(string $value): void
    {
        $parts = explode(' ', trim($value), 2);
        $this->attributes['primer_nombre'] = trim($parts[0] ?? '');
        $this->attributes['segundo_nombre'] = trim($parts[1] ?? '');
    }
    protected static function booted(): void
    {
        static::creating(function (Student $student) {

            if (! empty($student->codigo)) {
                return;
            }

            $anio = date('Y');

            // max() compara como texto y falla con 10+; usamos orden numérico
            $ultimo = Student::whereYear('created_at', $anio)
                ->orderByRaw('CAST(codigo AS UNSIGNED) DESC')
                ->value('codigo');

            $numero = $ultimo ? (intval(substr($ultimo, 4)) + 1) : 1;

            $student->codigo = $anio . $numero;
        });
    }
    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function periodoLectivo()
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(
            Guardian::class,
            'guardian_student'
        )
        ->withPivot('tipo', 'estado')
        ->withTimestamps();
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class);
    }

    public function documentos()
    {
        return $this->hasMany(StudentDocumento::class, 'student_codigo', 'codigo');
    }

    public function documentoGenerado(string $tipo): bool
    {
        return $this->documentos()->where('tipo', $tipo)->exists();
    }

    public function getFullNameAttribute(): string
    {
        return $this->primer_nombre . ' ' . $this->primer_apellido;
    }
    public function fichaCosto()
    {
        return $this->hasOne(FichaCostoEstudiante::class, 'student_id', 'id');
    }


    public function notas()
    {
        return $this->hasMany(NotaEstudiante::class);
    }

}