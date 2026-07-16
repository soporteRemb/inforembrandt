<?php

namespace App\Services\Financiero;

use App\Models\MovimientoCarteraEstudiante;
use App\Models\Student;
use Illuminate\Support\Str;

class ResumenCuentaEstudianteService
{
    public function calcular(Student $student): array
    {
        $movimientos = MovimientoCarteraEstudiante::query()
            ->with('conceptoCobro')
            ->where('student_id', $student->id)
            ->where('sede_id', $student->sede_id)
            ->where('periodo_lectivo_id', $student->periodo_lectivo_id)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->get();

        $matricula = 0;
        $pensiones = 0;
        $costosAcademicos = 0;
        $otrasDeudas = 0;

        foreach ($movimientos as $movimiento) {
            $descripcion = Str::upper(Str::ascii($movimiento->conceptoCobro?->descripcion ?? ''));

            if (str_contains($descripcion, 'MATRICULA')) {
                $matricula += (float) $movimiento->valor;
                continue;
            }

            if (str_contains($descripcion, 'PENSION')) {
                $pensiones += (float) $movimiento->valor;
                continue;
            }

            if (str_contains($descripcion, 'COSTOS ACADEMICOS')) {
                $costosAcademicos += (float) $movimiento->valor;
                continue;
            }

            $otrasDeudas += (float) $movimiento->valor;
        }

        return [
            'matricula' => $matricula,
            'deudas' => $pensiones,
            'costos_academicos' => $costosAcademicos,
            'otras_deudas' => $otrasDeudas,
            'total_causado' => $movimientos->sum('valor'),
        ];
    }

    public function obtenerUltimoMesCausado(Student $student): ?string
    {
        $meses = [
            2  => 'FEBRERO',
            3  => 'MARZO',
            4  => 'ABRIL',
            5  => 'MAYO',
            6  => 'JUNIO',
            7  => 'JULIO',
            8  => 'AGOSTO',
            9  => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
        ];

        $ultimoMes = MovimientoCarteraEstudiante::query()
            ->where('student_id', $student->id)
            ->where('sede_id', $student->sede_id)
            ->where('periodo_lectivo_id', $student->periodo_lectivo_id)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->max('mes_numero');

        return $ultimoMes
            ? ($meses[$ultimoMes] ?? null)
            : null;
    }
}