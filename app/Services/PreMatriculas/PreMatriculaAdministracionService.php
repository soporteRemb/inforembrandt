<?php

namespace App\Services\PreMatriculas;

use App\Models\PreMatricula;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PreMatriculaAdministracionService
{
    public function __construct(
        private readonly PreMatriculaHistorialService $historialService,
    ) {
    }

    /**
     * Obtiene una pre-matrícula perteneciente a la sede y
     * al período lectivo activos.
     */
    public function obtenerFormulario(
        int $preMatriculaId,
        int $sedeId,
        int $periodoLectivoId
    ): PreMatricula {
        return PreMatricula::query()
            ->with([
                'usuario',
                'sede',
                'periodoLectivo',
                'eps',
                'historial.usuario',

            ])
            ->whereKey($preMatriculaId)
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->firstOrFail();
    }

    /**
     * Prepara el registro con los nombres utilizados por el
     * modal administrativo.
     */
    public function prepararFormularioEdicion(
        PreMatricula $preMatricula
    ): array {
        $historial = $preMatricula->historial
            ->sortByDesc('created_at')
            ->map(function ($item): array {
                return [
                    'fecha' => optional($item->created_at)
                        ->format('d/m/Y h:i a'),

                    'usuario' => $item->usuario?->name
                        ?? 'Sistema',

                    'accion' => $item->accion,

                    'accion_texto' => match ($item->accion) {
                        'creacion' => 'Formulario habilitado',
                        'envio' => 'Formulario enviado',
                        'actualizacion' => 'Campo actualizado',
                        default => ucfirst(
                            str_replace('_', ' ', (string) $item->accion)
                        ),
                    },

                    'descripcion' => $item->descripcion,

                    'campo' => $item->campo,

                    'campo_texto' => filled($item->campo)
                        ? ucfirst(
                            str_replace('_', ' ', $item->campo)
                        )
                        : null,

                    'anterior' => $item->valor_anterior,

                    'nuevo' => $item->valor_nuevo,
                ];
            })
            ->values()
            ->toArray();

        return [
            'id' => $preMatricula->id,
            'numero_formulario' => $preMatricula->numero_formulario,
            'estado' => $preMatricula->estado,

            'fecha_envio' => $preMatricula->fecha_envio?->format(
                'd/m/Y h:i a'
            ),

            'historial' => $historial,

            /*
            |--------------------------------------------------------------------------
            | Estudiante
            |--------------------------------------------------------------------------
            */
            'nombres' => (string) ($preMatricula->nombres ?? ''),
            'apellidos' => (string) ($preMatricula->apellidos ?? ''),
            'estudiante' => trim(
                ($preMatricula->nombres ?? '')
                . ' '
                . ($preMatricula->apellidos ?? '')
            ),

            'tipo_documento' => (string) (
                $preMatricula->tipo_documento ?? ''
            ),

            'documento' => (string) (
                $preMatricula->documento ?? ''
            ),

            'ciudad_expedicion' => (string) (
                $preMatricula->ciudad_expedicion ?? ''
            ),

            'fecha_nacimiento' =>
                $preMatricula->fecha_nacimiento?->format('Y-m-d'),

            'edad' => $preMatricula->edad,

            'ciudad_nacimiento' => (string) (
                $preMatricula->ciudad_nacimiento ?? ''
            ),

            'genero' => (string) (
                $preMatricula->genero ?? ''
            ),

            'numero_hermanos' =>
                $preMatricula->numero_hermanos ?? 0,

            'telefono' => (string) (
                $preMatricula->telefono ?? ''
            ),

            'correo' => (string) (
                $preMatricula->correo ?? ''
            ),

            'direccion' => (string) (
                $preMatricula->direccion ?? ''
            ),

            'rh' => (string) (
                $preMatricula->rh ?? ''
            ),

            'eps_id' => filled($preMatricula->eps_id)
                ? (string) $preMatricula->eps_id
                : '',

            'eps' => (string) (
                $preMatricula->eps?->nombre ?? ''
            ),

            'telefono_emergencia' => (string) (
                $preMatricula->telefono_emergencia ?? ''
            ),

            'grado' => (string) (
                $preMatricula->grado_aspira ?? ''
            ),

            'grado_aspira' => (string) (
                $preMatricula->grado_aspira ?? ''
            ),

            'institucion_anterior' => (string) (
                $preMatricula->institucion_anterior ?? ''
            ),

            'condicion_ingreso' => (string) (
                $preMatricula->condicion_ingreso ?? ''
            ),

            /*
            |--------------------------------------------------------------------------
            | Padre
            |--------------------------------------------------------------------------
            */
            'padre_nombre' => (string) (
                $preMatricula->padre_nombre ?? ''
            ),

            'padre_telefono' => (string) (
                $preMatricula->padre_telefono ?? ''
            ),

            'padre_tipo_documento' => (string) (
                $preMatricula->padre_tipo_documento ?? ''
            ),

            'padre_documento' => (string) (
                $preMatricula->padre_documento ?? ''
            ),

            'padre_lugar_trabajo' => (string) (
                $preMatricula->padre_lugar_trabajo ?? ''
            ),

            'padre_correo' => (string) (
                $preMatricula->padre_correo ?? ''
            ),

            'padre_direccion' => (string) (
                $preMatricula->padre_direccion ?? ''
            ),

            /*
            |--------------------------------------------------------------------------
            | Madre
            |--------------------------------------------------------------------------
            */
            'madre_nombre' => (string) (
                $preMatricula->madre_nombre ?? ''
            ),

            'madre_telefono' => (string) (
                $preMatricula->madre_telefono ?? ''
            ),

            'madre_tipo_documento' => (string) (
                $preMatricula->madre_tipo_documento ?? ''
            ),

            'madre_documento' => (string) (
                $preMatricula->madre_documento ?? ''
            ),

            'madre_lugar_trabajo' => (string) (
                $preMatricula->madre_lugar_trabajo ?? ''
            ),

            'madre_correo' => (string) (
                $preMatricula->madre_correo ?? ''
            ),

            'madre_direccion' => (string) (
                $preMatricula->madre_direccion ?? ''
            ),

            /*
            |--------------------------------------------------------------------------
            | Acudiente
            |--------------------------------------------------------------------------
            */
            'acudiente_origen' => (string) (
                $preMatricula->acudiente_origen ?? 'otro'
            ),

            'acudiente_parentesco' => (string) (
                $preMatricula->acudiente_parentesco ?? ''
            ),

            'acudiente_nombre' => (string) (
                $preMatricula->acudiente_nombre ?? ''
            ),

            'acudiente' => (string) (
                $preMatricula->acudiente_nombre ?? ''
            ),

            'acudiente_telefono' => (string) (
                $preMatricula->acudiente_telefono ?? ''
            ),

            'acudiente_tipo_documento' => (string) (
                $preMatricula->acudiente_tipo_documento ?? ''
            ),

            'acudiente_documento' => (string) (
                $preMatricula->acudiente_documento ?? ''
            ),

            'acudiente_lugar_trabajo' => (string) (
                $preMatricula->acudiente_lugar_trabajo ?? ''
            ),

            'acudiente_correo' => (string) (
                $preMatricula->acudiente_correo ?? ''
            ),

            'acudiente_direccion' => (string) (
                $preMatricula->acudiente_direccion ?? ''
            ),
        ];
    }

    /**
     * Guarda las correcciones realizadas por un administrativo.
     */
    public function guardarCambios(
        PreMatricula $preMatricula,
        array $datos,
        User $administrador
    ): PreMatricula {
        $datosActualizables = Arr::only(
            $datos,
            $this->camposActualizables()
        );

        $datosActualizables['grado_aspira'] =
            $datos['grado'] ?? $datos['grado_aspira'] ?? null;

        unset($datosActualizables['grado']);

        $datosActualizables = $this->normalizarDatos(
            $datosActualizables
        );

        return DB::transaction(function () use (
            $preMatricula,
            $datosActualizables,
            $administrador
        ): PreMatricula {
            $valoresAnteriores = $preMatricula->only(
                array_keys($datosActualizables)
            );

            $preMatricula->fill($datosActualizables);

            $preMatricula->forceFill([
                'actualizado_por' => $administrador->id,
            ])->save();

            foreach ($datosActualizables as $campo => $valorNuevo) {
                $valorAnterior = $valoresAnteriores[$campo] ?? null;

                if (
                    $this->valoresEquivalentes(
                        $valorAnterior,
                        $valorNuevo
                    )
                ) {
                    continue;
                }

                $this->historialService->registrarCambio(
                    $preMatricula,
                    $campo,
                    $valorAnterior,
                    $valorNuevo,
                    $administrador
                );
            }

            return $preMatricula->fresh([
                'usuario',
                'sede',
                'periodoLectivo',
                'eps',
                'historial.usuario',
            ]);
        });
    }

    /**
     * Campos permitidos desde la edición administrativa.
     */
    private function camposActualizables(): array
    {
        return [
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
            'telefono_emergencia',
            'grado',
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
        ];
    }

    private function normalizarDatos(array $datos): array
    {
        $camposMayuscula = [
            'nombres',
            'apellidos',
            'ciudad_expedicion',
            'ciudad_nacimiento',
            'direccion',
            'grado_aspira',
            'institucion_anterior',

            'documento',
            'telefono',
            'telefono_emergencia',

            'padre_nombre',
            'padre_telefono',
            'padre_documento',
            'padre_lugar_trabajo',
            'padre_direccion',

            'madre_nombre',
            'madre_telefono',
            'madre_documento',
            'madre_lugar_trabajo',
            'madre_direccion',

            'acudiente_parentesco',
            'acudiente_nombre',
            'acudiente_telefono',
            'acudiente_documento',
            'acudiente_lugar_trabajo',
            'acudiente_direccion',
        ];

        foreach ($camposMayuscula as $campo) {
            if (! array_key_exists($campo, $datos)) {
                continue;
            }

            $datos[$campo] = filled($datos[$campo])
                ? mb_strtoupper(
                    trim((string) $datos[$campo]),
                    'UTF-8'
                )
                : null;
        }

        $camposCorreo = [
            'correo',
            'padre_correo',
            'madre_correo',
            'acudiente_correo',
        ];

        foreach ($camposCorreo as $campo) {
            if (! array_key_exists($campo, $datos)) {
                continue;
            }

            $datos[$campo] = filled($datos[$campo])
                ? mb_strtolower(
                    trim((string) $datos[$campo]),
                    'UTF-8'
                )
                : null;
        }

        foreach ($datos as $campo => $valor) {
            if ($valor === '') {
                $datos[$campo] = null;
            }
        }

        return $datos;
    }

    private function valoresEquivalentes(
        mixed $valorAnterior,
        mixed $valorNuevo
    ): bool {
        if ($valorAnterior instanceof \DateTimeInterface) {
            $valorAnterior = $valorAnterior->format('Y-m-d');
        }

        if ($valorNuevo instanceof \DateTimeInterface) {
            $valorNuevo = $valorNuevo->format('Y-m-d');
        }

        return (string) ($valorAnterior ?? '') ===
            (string) ($valorNuevo ?? '');
    }
}