<?php

namespace App\Services\PreMatriculas;

use App\Models\PreMatricula;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PreMatriculaService
{
    public function __construct(
        private readonly PreMatriculaHistorialService $historialService,
        private readonly PreMatriculaValidacionService $validacionService,
    ) {
    }

    public function crearFormulario(
        User $usuarioTemporal,
        int $sedeId,
        int $periodoLectivoId,
        User $creadoPor
    ): PreMatricula {
        $periodoOrigen = \App\Models\PeriodoLectivo::query()
            ->where('id', $periodoLectivoId)
            ->where('sede_id', $sedeId)
            ->first();

        if (! $periodoOrigen) {
            throw ValidationException::withMessages([
                'data.sedes' => 'El período lectivo de origen no pertenece a la sede seleccionada.',
            ]);
        }

        $anioOrigen = trim((string) $periodoOrigen->nombre);

        if (! preg_match('/^\d{4}$/', $anioOrigen)) {
            throw ValidationException::withMessages([
                'data.sedes' => 'El período lectivo de origen debe corresponder a un año válido.',
            ]);
        }

        $anioDestino = (string) ((int) $anioOrigen + 1);

        $periodoDestino = \App\Models\PeriodoLectivo::query()
            ->where('sede_id', $sedeId)
            ->where('nombre', $anioDestino)
            ->first();

        if (! $periodoDestino) {
            throw ValidationException::withMessages([
                'data.sedes' => "La sede seleccionada no tiene configurado el período lectivo {$anioDestino}.",
            ]);
        }
        return DB::transaction(function () use (
            $usuarioTemporal,
            $sedeId,
            $periodoLectivoId,
            $periodoDestino,
            $creadoPor
        ): PreMatricula {
            $preMatricula = PreMatricula::create([

                'numero_formulario' => null,

                'user_id' => $usuarioTemporal->id,

                'sede_id' => $sedeId,

                'periodo_lectivo_id' => $periodoLectivoId,

                'periodo_destino_id' => $periodoDestino->id,

                'estado' => 'pendiente',

                'fecha_habilitacion' => now(),

                'fecha_limite' => $usuarioTemporal->expires_at,

                'documento' => $usuarioTemporal->email,

                'creado_por' => $creadoPor->id,

                'actualizado_por' => $creadoPor->id,

            ]);

            $numero = (int) $preMatricula->id;

            do {
                $numeroFormulario = 'PRE-' . $numero;

                $existe = PreMatricula::query()
                    ->where('numero_formulario', $numeroFormulario)
                    ->where('id', '!=', $preMatricula->id)
                    ->exists();

                if ($existe) {
                    $numero++;
                }
            } while ($existe);

            $preMatricula->forceFill([
                'numero_formulario' => $numeroFormulario,
            ])->save();

            $this->historialService->registrarEvento(
                $preMatricula,
                'creacion',
                'Formulario de pre-matrícula habilitado.',
                $creadoPor
            );

            return $preMatricula;
        });
    }

    public function completarFormulario(
        PreMatricula $preMatricula,
        array $datos,
        User $usuarioTemporal
    ): PreMatricula {
        if (
            ! $this->validacionService->puedeDiligenciar(
                $usuarioTemporal,
                $preMatricula
            )
        ) {
            throw ValidationException::withMessages([
                'formulario' => 'El formulario no se encuentra disponible.',
            ]);
        }

        if (! $this->validacionService->tieneResponsableCompleto($datos)) {
            throw ValidationException::withMessages([
                'responsable' => 'Debe registrar al menos un responsable del estudiante: padre, madre o acudiente.',
            ]);
        }

        return DB::transaction(function () use (
            $preMatricula,
            $datos,
            $usuarioTemporal
        ): PreMatricula {
            $preMatricula->fill($datos);

            $preMatricula->forceFill([
                'estado' => 'completado',
                'fecha_envio' => now(),
                'actualizado_por' => $usuarioTemporal->id,
            ])->save();

            /*
            |--------------------------------------------------------------------------
            | Desactivar la cuenta temporal
            |--------------------------------------------------------------------------
            |
            | Después del envío, el usuario ya no podrá ingresar nuevamente.
            |
            */
            $usuarioTemporal->forceFill([
                'is_active' => false,
            ])->save();

            $this->historialService->registrarEvento(
                $preMatricula,
                'envio',
                'Formulario enviado por el usuario temporal.',
                $usuarioTemporal
            );

            return $preMatricula->fresh();
        });
    }

    public function marcarComoVencida(
        PreMatricula $preMatricula
    ): PreMatricula {
        if (! $preMatricula->estaPendiente()) {
            return $preMatricula;
        }

        return DB::transaction(function () use (
            $preMatricula
        ): PreMatricula {
            $preMatricula->forceFill([
                'estado' => 'vencido',
            ])->save();

            if ($preMatricula->usuario) {
                $preMatricula->usuario->forceFill([
                    'is_active' => false,
                ])->save();
            }

            $this->historialService->registrarEvento(
                $preMatricula,
                'vencimiento',
                'El plazo del formulario finalizó.'
            );

            return $preMatricula->fresh();
        });
    }

    public function reactivarFormularioVencido(
        PreMatricula $preMatricula,
        User $actualizadoPor,
        \DateTimeInterface|string|null $fechaLimite
    ): PreMatricula {
        if (! $preMatricula->estaVencida()) {
            return $preMatricula;
        }

        if (blank($fechaLimite)) {
            throw ValidationException::withMessages([
                'data.expires_at' =>
                    'Debe establecer una nueva fecha de vencimiento para reactivar el formulario.',
            ]);
        }

        return DB::transaction(function () use (
            $preMatricula,
            $actualizadoPor,
            $fechaLimite
        ): PreMatricula {
            $preMatricula->forceFill([
                'estado' => 'pendiente',
                'fecha_limite' => $fechaLimite,
                'actualizado_por' => $actualizadoPor->id,
            ])->save();

            $this->historialService->registrarEvento(
                $preMatricula,
                'reactivacion',
                'Formulario vencido habilitado nuevamente.',
                $actualizadoPor
            );

            return $preMatricula->fresh();
        });
    }

    public function reactivarFormularioCompletado(
        PreMatricula $preMatricula,
        User $actualizadoPor,
        \DateTimeInterface|string|null $fechaLimite
    ): PreMatricula {
        if (! $preMatricula->estaCompletada()) {
            throw ValidationException::withMessages([
                'data.is_active' =>
                    'Solo se puede utilizar esta operación con formularios completados.',
            ]);
        }

        if (
            blank($fechaLimite)
            || \Carbon\Carbon::parse($fechaLimite)->startOfDay()->lte(today())
        ) {
            throw ValidationException::withMessages([
                'data.expires_at' =>
                    'Debe establecer una fecha futura para reactivar el formulario.',
            ]);
        }

        return DB::transaction(function () use (
            $preMatricula,
            $actualizadoPor,
            $fechaLimite
        ): PreMatricula {
            $preMatricula->forceFill([
                'estado' => 'pendiente',
                'fecha_limite' => $fechaLimite,
                'actualizado_por' => $actualizadoPor->id,
            ])->save();

            $this->historialService->registrarEvento(
                $preMatricula,
                'reactivacion',
                'Formulario completado habilitado nuevamente para correcciones y documentos adicionales.',
                $actualizadoPor
            );

            return $preMatricula->fresh();
        });
    }
}