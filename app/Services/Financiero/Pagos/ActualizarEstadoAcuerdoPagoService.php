<?php

namespace App\Services\Financiero\Pagos;

use App\Models\AcuerdoPagoEstudiante;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActualizarEstadoAcuerdoPagoService
{
    public function actualizar(
        int $acuerdoId,
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
        string $estado,
        int $modificadoPor,
    ): AcuerdoPagoEstudiante {
        if (! in_array(
            $estado,
            AcuerdoPagoEstudiante::estados(),
            true
        )) {
            throw ValidationException::withMessages([
                'estado' => 'Seleccione un estado válido.',
            ]);
        }

        return DB::transaction(function () use (
            $acuerdoId,
            $studentId,
            $sedeId,
            $periodoLectivoId,
            $estado,
            $modificadoPor,
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

            if ($acuerdo->estado === $estado) {
                return $acuerdo;
            }

            $fechaCambio = now();

            $datos = [
                'estado' => $estado,
                'estado_modificado_por' => $modificadoPor,
                'estado_modificado_en' => $fechaCambio,
            ];

            if (
                $estado ===
                AcuerdoPagoEstudiante::ESTADO_ANULADO
            ) {
                $datos['anulado_por'] = $modificadoPor;
                $datos['anulado_en'] = $fechaCambio;
            } else {
                /*
                 * Si un acuerdo previamente anulado vuelve a otro
                 * estado, limpiamos su auditoría de anulación.
                 */
                $datos['anulado_por'] = null;
                $datos['anulado_en'] = null;
                $datos['motivo_anulacion'] = null;
            }

            $acuerdo->update($datos);

            return $acuerdo->fresh([
                'registradoPor',
                'estadoModificadoPor',
                'evidencias',
            ]);
        }, 3);
    }
}