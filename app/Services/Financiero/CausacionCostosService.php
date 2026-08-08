<?php

namespace App\Services\Financiero;

use App\Models\AsignacionConcepto;
use App\Models\FichaCostoEstudiante;
use App\Models\OtroCostoEstudiante;
use App\Models\PensionEstudiante;
use App\Models\Student;

class CausacionCostosService
{
    public function calcularResumen(
        ?int $sedeId,
        ?int $periodoLectivoId,
        ?string $grado,
        ?int $conceptoCobroId,
        ?int $mesNumero = null,
        ?int $courseId = null,
        ?array $studentIds = null,
        ?float $valorBaseManual = null,
    ): array {
        $resumen = [
            'estudiantes' => 0,
            'tarifa_base' => 0,
            'tarifa_variable' => $grado === 'todos',
            'valor_base_total' => 0,
            'personalizados' => 0,
            'diferencia_personalizados' => 0,
            'total_causar' => 0,
        ];

        if (! $sedeId || ! $periodoLectivoId || ! $grado || ! $conceptoCobroId) {
            return $resumen;
        }

        $grados = $grado === 'todos'
            ? Student::query()
                ->where('sede_id', $sedeId)
                ->where(
                    'periodo_lectivo_id',
                    $periodoLectivoId
                )
                ->whereHas('course')
                ->with('course:id,grado')
                ->get()
                ->pluck('course.grado')
                ->filter()
                ->unique()
                ->values()
                ->toArray()
            : [$grado];

        foreach ($grados as $gradoActual) {
            $asignacion = AsignacionConcepto::query()
                ->where('sede_id', $sedeId)
                ->where(
                    'periodo_lectivo_id',
                    $periodoLectivoId
                )
                ->where(
                    'concepto_cobro_id',
                    $conceptoCobroId
                )
                ->where('activo', true)
                ->whereIn('grado', [
                    $gradoActual,
                    'todos',
                ])
                ->orderByRaw(
                    "CASE WHEN grado = ? THEN 0 ELSE 1 END",
                    [$gradoActual]
                )
                ->first();

            if (! $asignacion) {
                continue;
            }

            $valorBaseGrado =
                $valorBaseManual !== null
                    ? round($valorBaseManual, 2)
                    : round(
                        (float) (
                            $asignacion->tarifa_ordinaria
                            ?? 0
                        ),
                        2
                    );

            if ($grado !== 'todos') {
                $resumen['tarifa_base'] = $valorBaseGrado;
            }

            $estudiantes = Student::query()
                ->where('sede_id', $sedeId)
                ->where(
                    'periodo_lectivo_id',
                    $periodoLectivoId
                )
                ->whereHas(
                    'course',
                    function ($query) use (
                        $gradoActual,
                        $courseId
                    ) {
                        $query->where(
                            'grado',
                            $gradoActual
                        );

                        if ($courseId) {
                            $query->whereKey($courseId);
                        }
                    }
                )
                ->when(
                    is_array($studentIds),
                    function ($query) use ($studentIds) {
                        $query->whereIn(
                            'id',
                            array_map('intval', $studentIds)
                        );
                    }
                )
                ->get();

            foreach ($estudiantes as $student) {
                $valorPersonalizado = null;

                $ficha = FichaCostoEstudiante::query()
                    ->where('student_id', $student->id)
                    ->where('periodo_lectivo_id', $periodoLectivoId)
                    ->first();

                if ($ficha) {
                    if ($mesNumero) {
                        $pension = PensionEstudiante::query()
                            ->where('ficha_costo_estudiante_id', $ficha->id)
                            ->where('mes_numero', $mesNumero)
                            ->first();

                        if ($pension && (float) $pension->valor_personalizado > 0) {
                            $valorPersonalizado = (float) $pension->valor_personalizado;
                        }
                    } else {
                        $otroCosto = OtroCostoEstudiante::query()
                            ->where('ficha_costo_estudiante_id', $ficha->id)
                            ->where('concepto_cobro_id', $conceptoCobroId)
                            ->first();

                        if ($otroCosto && (float) $otroCosto->valor_personalizado > 0) {
                            $valorPersonalizado = (float) $otroCosto->valor_personalizado;
                        }
                    }
                }

                $valorFinal = $valorPersonalizado ?? $valorBaseGrado;

                $resumen['estudiantes']++;
                $resumen['valor_base_total'] += $valorBaseGrado;
                $resumen['total_causar'] += $valorFinal;

                if ($valorPersonalizado !== null && $valorPersonalizado != $valorBaseGrado) {
                    $resumen['personalizados']++;
                    $resumen['diferencia_personalizados'] += ($valorPersonalizado - $valorBaseGrado);
                }
            }
        }

        return $resumen;
    }
}