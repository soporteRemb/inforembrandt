<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\PreMatriculaHistorial;


class PreMatricula extends Model
{
    use HasFactory;

    protected $table = 'pre_matriculas';

    protected $fillable = [
        'numero_formulario',
        'user_id',
        'sede_id',
        'periodo_lectivo_id',
        'estado',
        'fecha_habilitacion',
        'fecha_limite',
        'fecha_envio',
        'creado_por',
        'actualizado_por',

        'nombres',
        'apellidos',
        'tipo_documento',
        'documento',
        'ciudad_expedicion',
        'fecha_nacimiento',
        'edad',
        'ciudad_nacimiento',
        'genero',
        'numero_hermanos',
        'telefono',
        'correo',
        'direccion',
        'rh',
        'eps_id',
        'eps_otro',
        'telefono_emergencia',
        'grado_aspira',
        'institucion_anterior',
        'condicion_ingreso',

        'padre_nombre',
        'padre_telefono',
        'padre_tipo_documento',
        'padre_documento',
        'padre_lugar_trabajo',
        'padre_correo',
        'padre_direccion',

        'madre_nombre',
        'madre_telefono',
        'madre_tipo_documento',
        'madre_documento',
        'madre_lugar_trabajo',
        'madre_correo',
        'madre_direccion',

        'acudiente_origen',
        'acudiente_parentesco',
        'acudiente_nombre',
        'acudiente_telefono',
        'acudiente_tipo_documento',
        'acudiente_documento',
        'acudiente_lugar_trabajo',
        'acudiente_correo',
        'acudiente_direccion',

        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_habilitacion' => 'datetime',
            'fecha_limite' => 'datetime',
            'fecha_envio' => 'datetime',
            'fecha_nacimiento' => 'date',
            'edad' => 'integer',
            'numero_hermanos' => 'integer',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function periodoLectivo(): BelongsTo
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }

    public function eps(): BelongsTo
    {
        return $this->belongsTo(Eps::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    public function historiales(): HasMany
    {
        return $this->hasMany(
            PreMatriculaHistorial::class,
            'pre_matricula_id'
        )->latest('created_at');
    }

    public function estaPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function estaCompletada(): bool
    {
        return $this->estado === 'completado';
    }

    public function estaVencida(): bool
    {
        return $this->estado === 'vencido';
    }

    public function plazoVencido(): bool
    {
        return $this->fecha_limite !== null
            && now()->greaterThan($this->fecha_limite);
    }


    public function historial(): HasMany
    {
        return $this->hasMany(
            PreMatriculaHistorial::class,
            'pre_matricula_id'
        )->latest();
    }
}