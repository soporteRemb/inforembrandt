<?php

namespace App\Services\Financiero\Pagos;

use App\Models\MovimientoCarteraEstudiante;
use App\Models\ReciboPago;
use App\Models\Student;
use Illuminate\Support\Str;

class SincronizacionCarteraEstudianteService
{
    public function sincronizarPension(
        Student $student,
        int $mesNumero,
        float $nuevoValor
    ): bool {
        $movimiento = MovimientoCarteraEstudiante::query()
            ->with('conceptoCobro')
            ->where('student_id', $student->id)
            ->where('sede_id', $student->sede_id)
            ->where('periodo_lectivo_id', $student->periodo_lectivo_id)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->where('mes_numero', $mesNumero)
            ->whereHas('conceptoCobro', function ($query) {
                $query->where(function ($subquery) {
                    $subquery
                        ->where('descripcion', 'like', '%Pensión%')
                        ->orWhere('descripcion', 'like', '%Pension%');
                });
            })
            ->first();

        return $this->actualizarSiEstaAbierta($movimiento, $nuevoValor);
    }

    public function sincronizarConcepto(
        Student $student,
        int $conceptoCobroId,
        float $nuevoValor
    ): bool {
        $movimiento = MovimientoCarteraEstudiante::query()
            ->where('student_id', $student->id)
            ->where('sede_id', $student->sede_id)
            ->where('periodo_lectivo_id', $student->periodo_lectivo_id)
            ->where('concepto_cobro_id', $conceptoCobroId)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->whereNull('mes_numero')
            ->first();

        return $this->actualizarSiEstaAbierta($movimiento, $nuevoValor);
    }

    public function sincronizarConceptoPorDescripcion(
        Student $student,
        string $descripcionBuscada,
        float $nuevoValor
    ): bool {
        $movimientos = MovimientoCarteraEstudiante::query()
            ->with('conceptoCobro')
            ->where('student_id', $student->id)
            ->where('sede_id', $student->sede_id)
            ->where('periodo_lectivo_id', $student->periodo_lectivo_id)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->whereNull('mes_numero')
            ->get();

        $descripcionBuscada = Str::upper(Str::ascii($descripcionBuscada));

        $movimiento = $movimientos->first(function ($movimiento) use ($descripcionBuscada) {
            $descripcion = Str::upper(
                Str::ascii(
                    $movimiento->conceptoCobro?->descripcion
                    ?? $movimiento->descripcion
                    ?? ''
                )
            );

            return str_contains($descripcion, $descripcionBuscada);
        });

        return $this->actualizarSiEstaAbierta($movimiento, $nuevoValor);
    }

    private function actualizarSiEstaAbierta(
        ?MovimientoCarteraEstudiante $movimiento,
        float $nuevoValor
    ): bool {
        if (! $movimiento) {
            return false;
        }

        $valorAplicado = (float) $movimiento->aplicacionesPago()
            ->whereHas('reciboPago', function ($query) {
                $query->where('estado', ReciboPago::ESTADO_CONFIRMADO);
            })
            ->sum('valor_aplicado');

        $valorActual = (float) $movimiento->valor;

        /*
         * Si fue pagada completamente con el valor que tenía
         * en ese momento, la obligación queda históricamente cerrada.
         */
        if ($valorAplicado >= $valorActual && $valorActual > 0) {
            return false;
        }

        /*
         * Una obligación parcialmente pagada no puede reducirse
         * automáticamente por debajo del valor ya aplicado.
         */
        if ($nuevoValor < $valorAplicado) {
            return false;
        }

        $movimiento->update([
            'valor_personalizado' => $nuevoValor,
            'valor' => $nuevoValor,
        ]);

        return true;
    }
}