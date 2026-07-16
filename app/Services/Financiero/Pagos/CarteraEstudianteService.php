<?php

namespace App\Services\Financiero\Pagos;

use App\Models\MovimientoCarteraEstudiante;
use App\Models\ReciboPago;
use App\Models\SaldoFavorEstudiante;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class CarteraEstudianteService
{
    /**
     * Busca estudiantes únicamente dentro de la sede
     * y periodo lectivo activos.
     */
    public function buscarEstudiantes(
        int $sedeId,
        int $periodoLectivoId,
        string $termino
    ): Collection {
        $termino = trim($termino);

        if (mb_strlen($termino) < 2) {
            return new Collection();
        }

        return Student::query()
            ->with('course')
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where(function ($query) use ($termino) {
                $query
                    ->where('codigo', 'like', "%{$termino}%")
                    ->orWhere('documento', 'like', "%{$termino}%")
                    ->orWhere('primer_nombre', 'like', "%{$termino}%")
                    ->orWhere('segundo_nombre', 'like', "%{$termino}%")
                    ->orWhere('primer_apellido', 'like', "%{$termino}%")
                    ->orWhere('segundo_apellido', 'like', "%{$termino}%")
                    ->orWhereRaw(
                        "CONCAT_WS(
                            ' ',
                            primer_nombre,
                            segundo_nombre,
                            primer_apellido,
                            segundo_apellido
                        ) LIKE ?",
                        ["%{$termino}%"]
                    );
            })
            ->orderBy('primer_apellido')
            ->orderBy('segundo_apellido')
            ->orderBy('primer_nombre')
            ->limit(12)
            ->get();
    }

    /**
     * Obtiene un estudiante validando estrictamente
     * que pertenezca al contexto activo.
     */
    public function obtenerEstudiante(
        int $studentId,
        int $sedeId,
        int $periodoLectivoId
    ): ?Student {
        return Student::query()
            ->with([
                'course',
                'guardians',
            ])
            ->whereKey($studentId)
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->first();
    }

    /**
     * Retorna las obligaciones activas del estudiante,
     * incluyendo pagos aplicados y saldo real pendiente.
     */
    public function obtenerObligaciones(
        int $studentId,
        int $sedeId,
        int $periodoLectivoId
    ): SupportCollection {
        $movimientos = MovimientoCarteraEstudiante::query()
            ->with([
                'conceptoCobro',
                'aplicacionesPago.reciboPago',
            ])
            ->where('student_id', $studentId)
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->orderBy('fecha_vencimiento')
            ->orderBy('mes_numero')
            ->orderBy('id')
            ->get();

        return $movimientos
            ->map(function (MovimientoCarteraEstudiante $movimiento) {
                $valorCausado = (float) $movimiento->valor;

                /*
                 * Solo descuentan deuda las aplicaciones relacionadas
                 * con recibos que continúan confirmados.
                 */
                $valorAplicado = $movimiento->aplicacionesPago
                    ->filter(function ($aplicacion) {
                        return $aplicacion->reciboPago
                            && $aplicacion->reciboPago->estado === ReciboPago::ESTADO_CONFIRMADO;
                    })
                    ->sum(fn ($aplicacion) => (float) $aplicacion->valor_aplicado);

                $saldoPendiente = max(
                    0,
                    round($valorCausado - $valorAplicado, 2)
                );

                return [
                    'id' => $movimiento->id,
                    'concepto_cobro_id' => $movimiento->concepto_cobro_id,
                    'codigo' => $movimiento->conceptoCobro?->codigo,
                    'concepto' => $movimiento->conceptoCobro?->descripcion
                        ?? $movimiento->descripcion
                        ?? 'Concepto sin nombre',
                    'tipo_concepto' => $movimiento->tipo_concepto,
                    'obligatorio' => (bool) ($movimiento->conceptoCobro?->obligatorio ?? false),
                    'mes' => $movimiento->mes,
                    'mes_numero' => $movimiento->mes_numero,
                    'fecha_movimiento' => $movimiento->fecha_movimiento,
                    'fecha_vencimiento' => $movimiento->fecha_vencimiento,
                    'valor_base' => (float) $movimiento->valor_base,
                    'valor_personalizado' => $movimiento->valor_personalizado !== null
                        ? (float) $movimiento->valor_personalizado
                        : null,
                    'valor_causado' => $valorCausado,
                    'valor_aplicado' => (float) $valorAplicado,
                    'saldo_pendiente' => $saldoPendiente,
                    'estado_cartera' => $this->determinarEstadoCartera(
                        valorCausado: $valorCausado,
                        valorAplicado: (float) $valorAplicado,
                        saldoPendiente: $saldoPendiente,
                    ),
                    'descripcion' => $movimiento->descripcion,
                    'referencia' => $movimiento->referencia,
                ];
            })
            ->filter(fn (array $obligacion) => $obligacion['saldo_pendiente'] > 0)
            ->values();
    }

    /**
     * Calcula los indicadores financieros superiores.
     */
    public function obtenerResumen(
        int $studentId,
        int $sedeId,
        int $periodoLectivoId
    ): array {
        $obligaciones = $this->obtenerObligaciones(
            studentId: $studentId,
            sedeId: $sedeId,
            periodoLectivoId: $periodoLectivoId,
        );

        $deudaObligatoria = $obligaciones
            ->where('obligatorio', true)
            ->sum('saldo_pendiente');

        $otrosConceptos = $obligaciones
            ->where('obligatorio', false)
            ->sum('saldo_pendiente');

        /*
         * Las penalizaciones aún no tienen una estructura separada
         * aplicada en la cartera actual. Se deja preparado el indicador.
         */
        $penalizaciones = 0;

        $totalPendiente = round(
            $deudaObligatoria + $otrosConceptos + $penalizaciones,
            2
        );

        $saldoFavor = (float) (
            SaldoFavorEstudiante::query()
                ->where('student_id', $studentId)
                ->where('sede_id', $sedeId)
                ->where('periodo_lectivo_id', $periodoLectivoId)
                ->value('saldo_disponible')
            ?? 0
        );

        $totalNeto = max(
            0,
            round($totalPendiente - $saldoFavor, 2)
        );

        return [
            'deuda_obligatoria' => (float) $deudaObligatoria,
            'penalizaciones' => (float) $penalizaciones,
            'otros_conceptos' => (float) $otrosConceptos,
            'total_pendiente' => (float) $totalPendiente,
            'saldo_favor' => $saldoFavor,
            'total_neto' => (float) $totalNeto,
            'cantidad_obligaciones' => $obligaciones->count(),
        ];
    }

    private function determinarEstadoCartera(
        float $valorCausado,
        float $valorAplicado,
        float $saldoPendiente
    ): string {
        if ($saldoPendiente <= 0) {
            return 'pagada';
        }

        if ($valorAplicado > 0 && $valorAplicado < $valorCausado) {
            return 'parcialmente_pagada';
        }

        return 'pendiente';
    }
}