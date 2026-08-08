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
        return DB::transaction(function () use (
            $usuarioTemporal,
            $sedeId,
            $periodoLectivoId,
            $creadoPor
        ): PreMatricula {
            $preMatricula = PreMatricula::create([
                'numero_formulario' => 'PRE-' . $usuarioTemporal->email,
                'user_id' => $usuarioTemporal->id,
                'sede_id' => $sedeId,
                'periodo_lectivo_id' => $periodoLectivoId,
                'estado' => 'pendiente',
                'fecha_habilitacion' => now(),
                'fecha_limite' => $usuarioTemporal->expires_at,
                'documento' => $usuarioTemporal->email,
                'creado_por' => $creadoPor->id,
                'actualizado_por' => $creadoPor->id,
            ]);

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
}