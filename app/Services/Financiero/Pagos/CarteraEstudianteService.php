<?php

namespace App\Services\Financiero\Pagos;

use App\Models\MovimientoCarteraEstudiante;
use App\Models\ReciboPago;
use App\Models\SaldoFavorEstudiante;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

use App\Services\Financiero\Pagos\CalcularValorVigenteObligacionService;

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
            ->where(
                'periodo_lectivo_id',
                $periodoLectivoId
            )
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->orderBy('fecha_vencimiento')
            ->orderBy('mes_numero')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Calculador central del valor vigente
        |--------------------------------------------------------------------------
        |
        | La causación conserva el valor original.
        | Este servicio determina el valor que corresponde cobrar hoy según:
        |
        | - valor causado o personalizado;
        | - tarifas extemporáneas configuradas;
        | - fecha actual;
        | - pagos que continúan confirmados.
        */
        $calculador = app(
            CalcularValorVigenteObligacionService::class
        );

        return $movimientos
            ->map(function (
                MovimientoCarteraEstudiante $movimiento
            ) use ($calculador) {
                $calculo = $calculador->calcular(
                    movimiento: $movimiento,
                    fechaCorte: now(),
                );

                $valorOriginal = (float) (
                    $calculo['valor_original']
                    ?? $movimiento->valor
                );

                $valorVigente = (float) (
                    $calculo['valor_vigente']
                    ?? $valorOriginal
                );

                $valorAplicado = (float) (
                    $calculo['valor_aplicado']
                    ?? 0
                );

                $saldoPendiente = (float) (
                    $calculo['saldo_pendiente']
                    ?? 0
                );

                

                return [
                    'id' =>
                        (int) $movimiento->id,

                    'concepto_cobro_id' =>
                        (int) $movimiento->concepto_cobro_id,

                    'codigo' =>
                        $movimiento->conceptoCobro?->codigo,

                    'concepto' =>
                        $movimiento->conceptoCobro?->descripcion
                        ?? $movimiento->descripcion
                        ?? 'Concepto sin nombre',

                    'tipo_concepto' =>
                        $movimiento->tipo_concepto,

                    'obligatorio' =>
                        (bool) (
                            $movimiento
                                ->conceptoCobro
                                ?->obligatorio
                            ?? false
                        ),

                    'mes' =>
                        $movimiento->mes,

                    'mes_numero' =>
                        $movimiento->mes_numero,

                    'fecha_movimiento' =>
                        $movimiento->fecha_movimiento,

                    'fecha_vencimiento' =>
                        $movimiento->fecha_vencimiento,

                    /*
                    |--------------------------------------------------------------------------
                    | Valores históricos de la causación
                    |--------------------------------------------------------------------------
                    */
                    'valor_base' =>
                        (float) $movimiento->valor_base,

                    'valor_personalizado' =>
                        $movimiento->valor_personalizado !== null
                            ? (float) $movimiento->valor_personalizado
                            : null,

                    'valor_original' =>
                        $valorOriginal,

                    /*
                    |--------------------------------------------------------------------------
                    | Valores financieros vigentes
                    |--------------------------------------------------------------------------
                    |
                    | Conservamos valor_causado porque las pantallas actuales
                    | ya consumen esa clave. Desde ahora representa el importe
                    | vigente que corresponde cobrar.
                    */
                    'valor_causado' =>
                        $valorVigente,

                    'valor_vigente' =>
                        $valorVigente,

                    'valor_aplicado' =>
                        $valorAplicado,

                    'saldo_pendiente' =>
                        $saldoPendiente,

                    

                    'aumento_extemporaneo' =>
                        (float) (
                            $calculo['aumento_extemporaneo']
                            ?? 0
                        ),

                    /*
                    |--------------------------------------------------------------------------
                    | Información de la tarifa aplicada
                    |--------------------------------------------------------------------------
                    */
                    'tiene_tarifa_extemporanea' =>
                        (bool) (
                            $calculo[
                                'tiene_tarifa_extemporanea'
                            ]
                            ?? false
                        ),

                    'tipo_limite_extemporaneo_id' =>
                        $calculo[
                            'tipo_limite_extemporaneo_id'
                        ]
                        ?? null,

                    'tipo_limite_texto' =>
                        $calculo[
                            'tipo_limite_texto'
                        ]
                        ?? null,

                    'fecha_vencimiento_aplicada' =>
                        $calculo[
                            'fecha_vencimiento_aplicada'
                        ]
                        ?? null,

                    'estado_cartera' =>
                        $this->determinarEstadoCartera(
                            valorCausado:
                                $valorVigente,

                            valorAplicado:
                                $valorAplicado,

                            saldoPendiente:
                                $saldoPendiente,
                        ),

                    'descripcion' =>
                        $movimiento->descripcion,

                    'referencia' =>
                        $movimiento->referencia,
                ];
            })
            ->filter(
                fn (array $obligacion) =>
                    $obligacion['saldo_pendiente'] > 0
            )
            ->sortBy(function (array $obligacion) {
                return [
                    /*
                    * Primero se muestran las obligatorias.
                    */
                    $obligacion['obligatorio']
                        ? 0
                        : 1,

                    /*
                    * Dentro de cada grupo se conserva el orden
                    * cronológico.
                    */
                    $obligacion['fecha_vencimiento']
                        ? \Carbon\Carbon::parse(
                            $obligacion[
                                'fecha_vencimiento'
                            ]
                        )->format('Y-m-d')
                        : '9999-12-31',

                    (int) (
                        $obligacion['mes_numero']
                        ?? 0
                    ),

                    (int) (
                        $obligacion['id']
                        ?? 0
                    ),
                ];
            })
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

        /*
        |--------------------------------------------------------------------------
        | Totales vigentes
        |--------------------------------------------------------------------------
        |
        | El saldo pendiente ya incluye los aumentos por tarifa
        | extemporánea aplicables a la fecha actual.
        |
        | Las penalizaciones son independientes y todavía no están
        | conectadas, por lo que permanecen en cero.
        */
        $deudaObligatoria = round(
            (float) $obligaciones
                ->where('obligatorio', true)
                ->sum('saldo_pendiente'),
            2
        );

        $otrosConceptos = round(
            (float) $obligaciones
                ->where('obligatorio', false)
                ->sum('saldo_pendiente'),
            2
        );

        $penalizaciones = 0.0;

        $totalPendiente = round(
            $deudaObligatoria
                + $otrosConceptos
                + $penalizaciones,
            2
        );

        $saldoFavor = (float) (
            SaldoFavorEstudiante::query()
                ->where('student_id', $studentId)
                ->where('sede_id', $sedeId)
                ->where(
                    'periodo_lectivo_id',
                    $periodoLectivoId
                )
                ->value('saldo_disponible')
            ?? 0
        );

        $totalNeto = max(
            0,
            round(
                $totalPendiente - $saldoFavor,
                2
            )
        );

        return [
            'deuda_obligatoria' =>
                (float) $deudaObligatoria,

            'penalizaciones' =>
                (float) $penalizaciones,

            'otros_conceptos' =>
                (float) $otrosConceptos,

            'total_pendiente' =>
                (float) $totalPendiente,

            'saldo_favor' =>
                $saldoFavor,

            'total_neto' =>
                (float) $totalNeto,

            'cantidad_obligaciones' =>
                $obligaciones->count(),
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