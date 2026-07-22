<?php

namespace App\Services\Financiero\Pagos;

use App\Models\AcuerdoPagoEstudiante;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActualizarAcuerdoPagoService
{
    public function actualizar(
        int $acuerdoId,
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
        string $personaAcuerdo,
        ?string $parentesco,
        string $fechaCompromiso,
        string $textoAcuerdo,
    ): AcuerdoPagoEstudiante {
        return DB::transaction(function () use (
            $acuerdoId,
            $studentId,
            $sedeId,
            $periodoLectivoId,
            $personaAcuerdo,
            $parentesco,
            $fechaCompromiso,
            $textoAcuerdo,
        ) {
            $acuerdo = AcuerdoPagoEstudiante::query()
                ->whereKey($acuerdoId)
                ->where('student_id', $studentId)
                ->where('sede_id', $sedeId)
                ->where(
                    'periodo_lectivo_id',
                    $periodoLectivoId
                )
                ->lockForUpdate()
                ->first();

            if (! $acuerdo) {
                throw ValidationException::withMessages([
                    'acuerdo' =>
                        'No se encontró el acuerdo solicitado.',
                ]);
            }

            if (
                $acuerdo->estado !==
                AcuerdoPagoEstudiante::ESTADO_VIGENTE
            ) {
                throw ValidationException::withMessages([
                    'acuerdo' =>
                        'Solo los acuerdos vigentes pueden editarse.',
                ]);
            }

            $acuerdo->update([
                'persona_acuerdo' =>
                    trim($personaAcuerdo),

                'parentesco' =>
                    filled($parentesco)
                        ? trim((string) $parentesco)
                        : null,

                'fecha_compromiso' =>
                    $fechaCompromiso,

                'texto_acuerdo' =>
                    trim($textoAcuerdo),
            ]);

            return $acuerdo->fresh([
                'registradoPor',
                'estadoModificadoPor',
                'evidencias',
            ]);
        }, 3);
    }
}