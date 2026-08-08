<?php

namespace App\Services\Financiero;

use App\Models\AsignacionConcepto;
use App\Models\FichaCostoEstudiante;
use App\Models\MovimientoCarteraEstudiante;
use App\Models\OtroCostoEstudiante;
use App\Models\PensionEstudiante;
use App\Models\Student;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CausacionMasivaService
{
    public function causar(
        int $sedeId,
        int $periodoLectivoId,
        string $grado,
        int $conceptoCobroId,
        ?int $mesNumero,
        ?int $userId,
        ?array $studentIds = null,
        ?float $valorBaseManual = null,
    ): array {
        return DB::transaction(function () use (
            $sedeId,
            $periodoLectivoId,
            $grado,
            $conceptoCobroId,
            $mesNumero,
            $userId,
            $studentIds,
            $valorBaseManual,
        ) {

            $referenciaLote = sprintf(
                'CAU-%s-%s-%s',
                now()->format('Ymd'),
                $conceptoCobroId,
                strtoupper(\Illuminate\Support\Str::random(4))
            );

            $grados = $grado === 'todos'
                ? Student::query()
                    ->where('sede_id', $sedeId)
                    ->where(
                        'periodo_lectivo_id',
                        $periodoLectivoId
                    )
                    ->when(
                        is_array($studentIds),
                        function ($query) use ($studentIds) {
                            $query->whereIn(
                                'id',
                                collect($studentIds)
                                    ->map(fn ($id) => (int) $id)
                                    ->filter(fn ($id) => $id > 0)
                                    ->unique()
                                    ->values()
                                    ->toArray()
                            );
                        }
                    )
                    ->with('course:id,grado')
                    ->get()
                    ->pluck('course.grado')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray()
                : [$grado];

            $creados = 0;
            $omitidos = 0;
            $totalCausado = 0;
            $totalEstudiantes = 0;

            foreach ($grados as $gradoActual) {
                $asignacion = AsignacionConcepto::query()
                    ->with('conceptoCobro')
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
                        'CASE WHEN grado = ? THEN 0 ELSE 1 END',
                        [$gradoActual]
                    )
                    ->first();

                if (! $asignacion) {
                    continue;
                }

                $estudiantes = Student::query()
                    ->where('sede_id', $sedeId)
                    ->where(
                        'periodo_lectivo_id',
                        $periodoLectivoId
                    )
                    ->whereHas(
                        'course',
                        fn ($query) =>
                            $query->where('grado', $gradoActual)
                    )
                    ->when(
                        is_array($studentIds),
                        function ($query) use ($studentIds) {
                            $idsValidos = collect($studentIds)
                                ->map(fn ($id) => (int) $id)
                                ->filter(fn ($id) => $id > 0)
                                ->unique()
                                ->values()
                                ->toArray();

                            $query->whereIn('id', $idsValidos);
                        }
                    )
                    ->with('course')
                    ->get();

                $totalEstudiantes += $estudiantes->count();

                foreach ($estudiantes as $student) {
                    $yaExiste = MovimientoCarteraEstudiante::query()
                        ->where('student_id', $student->id)
                        ->where('sede_id', $sedeId)
                        ->where('periodo_lectivo_id', $periodoLectivoId)
                        ->where('concepto_cobro_id', $conceptoCobroId)
                        ->where('mes_numero', $mesNumero)
                        ->where('tipo_movimiento', 'causacion')
                        ->where('estado', 'activo')
                        ->exists();

                    if ($yaExiste) {
                        $omitidos++;
                        continue;
                    }

                    $valorBase = $valorBaseManual !== null
                        ? round($valorBaseManual, 2)
                        : round(
                            (float) (
                                $asignacion->tarifa_ordinaria
                                ?? 0
                            ),
                            2
                        );
                    $valorPersonalizado = 0;
                    $valorFinal = $valorBase;

                    $ficha = FichaCostoEstudiante::query()
                        ->where('student_id', $student->id)
                        ->where('periodo_lectivo_id', $periodoLectivoId)
                        ->first();

                    if ($ficha) {
                        $descripcionConcepto = Str::upper(Str::ascii($asignacion->conceptoCobro?->descripcion ?? ''));

                        if (str_contains($descripcionConcepto, 'MATRICULA') && (float) $ficha->matricula > 0) {
                            if ((float) $ficha->matricula != $valorBase) {
                                $valorPersonalizado = (float) $ficha->matricula;
                                $valorFinal = $valorPersonalizado;
                            }
                        }
                        if ($mesNumero) {
                            $pension = PensionEstudiante::query()
                                ->where('ficha_costo_estudiante_id', $ficha->id)
                                ->where('mes_numero', $mesNumero)
                                ->first();

                            if ($pension && (float) $pension->valor_personalizado > 0) {
                                $valorPersonalizado = (float) $pension->valor_personalizado;
                                $valorFinal = $valorPersonalizado;
                            }
                        } else {
                            $otroCosto = OtroCostoEstudiante::query()
                                ->where('ficha_costo_estudiante_id', $ficha->id)
                                ->where('concepto_cobro_id', $conceptoCobroId)
                                ->first();

                            if ($otroCosto && (float) $otroCosto->valor_personalizado > 0) {
                                $valorPersonalizado = (float) $otroCosto->valor_personalizado;
                                $valorFinal = $valorPersonalizado;
                            }
                        }
                    }

                    MovimientoCarteraEstudiante::create([
                        'student_id' => $student->id,
                        'sede_id' => $sedeId,
                        'periodo_lectivo_id' => $periodoLectivoId,
                        'course_id' => $student->course_id,
                        'concepto_cobro_id' => $conceptoCobroId,
                        'grado' => $gradoActual,
                        'tipo_movimiento' => 'causacion',
                        'tipo_concepto' => $asignacion->conceptoCobro?->obligatorio ? 'obligatorio' : 'no_obligatorio',
                        'mes' => $mesNumero ? $this->nombreMes($mesNumero) : null,
                        'mes_numero' => $mesNumero,
                        'valor_base' => $valorBase,
                        'valor_personalizado' => $valorPersonalizado,
                        'valor' => $valorFinal,
                        'estado' => 'activo',
                        'descripcion' => $asignacion->conceptoCobro?->descripcion,
                        'referencia' => $referenciaLote,
                        'causado_por' => $userId,
                        'causado_en' => now(),
                        'fecha_movimiento' => now()->toDateString(),
                    ]);

                    $creados++;
                    $totalCausado += $valorFinal;
                }
            }

            return [
                'creados' => $creados,
                'omitidos' => $omitidos,
                'estudiantes' => $totalEstudiantes,
                'total_causado' => $totalCausado,
            ];
        });
    }

    private function nombreMes(?int $mesNumero): ?string
    {
        return [
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
        ][$mesNumero] ?? null;
    }

    private function referencia(int $conceptoCobroId, string $grado, ?int $mesNumero): string
    {
        return 'CAU-' . $conceptoCobroId . '-' . $grado . '-' . ($mesNumero ?: 'GENERAL');
    }
}