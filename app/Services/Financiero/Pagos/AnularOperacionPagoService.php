<?php

namespace App\Services\Financiero\Pagos;

use App\Models\MovimientoSaldoFavor;
use App\Models\OperacionPago;
use App\Models\ReciboPago;
use App\Models\SaldoFavorEstudiante;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnularOperacionPagoService
{
    /**
     * Anula por completo una operación de caja.
     *
     * La anulación:
     * - no elimina ningún registro;
     * - marca la operación y sus recibos como anulados;
     * - hace que las aplicaciones de cartera dejen de contar;
     * - revierte los movimientos de saldo a favor;
     * - conserva impresiones, formas de pago y auditoría.
     */
    public function anular(
        int $operacionPagoId,
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
        int $anuladoPor,
        ?string $motivo = null,
    ): array {
        $this->validarDatosGenerales(
            operacionPagoId: $operacionPagoId,
            studentId: $studentId,
            sedeId: $sedeId,
            periodoLectivoId: $periodoLectivoId,
            anuladoPor: $anuladoPor,
        );

        return DB::transaction(function () use (
            $operacionPagoId,
            $studentId,
            $sedeId,
            $periodoLectivoId,
            $anuladoPor,
            $motivo,
        ) {
            /*
            |--------------------------------------------------------------------------
            | Bloquear la operación
            |--------------------------------------------------------------------------
            */
            $operacion = OperacionPago::query()
                ->whereKey($operacionPagoId)
                ->where('student_id', $studentId)
                ->where('sede_id', $sedeId)
                ->where('periodo_lectivo_id', $periodoLectivoId)
                ->lockForUpdate()
                ->first();

            if (! $operacion) {
                throw ValidationException::withMessages([
                    'operacion' =>
                        'No se encontró la operación de pago solicitada.',
                ]);
            }

            if (
                $operacion->estado ===
                OperacionPago::ESTADO_ANULADA
            ) {
                throw ValidationException::withMessages([
                    'operacion' =>
                        'Esta operación ya se encuentra anulada.',
                ]);
            }

            if (
                $operacion->estado !==
                OperacionPago::ESTADO_CONFIRMADA
            ) {
                throw ValidationException::withMessages([
                    'operacion' =>
                        'La operación no se encuentra confirmada '
                        . 'y no puede ser anulada.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Bloquear los recibos de la operación
            |--------------------------------------------------------------------------
            */
            $recibos = ReciboPago::query()
                ->where(
                    'operacion_pago_id',
                    $operacion->id
                )
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($recibos->isEmpty()) {
                throw ValidationException::withMessages([
                    'operacion' =>
                        'La operación no tiene recibos asociados.',
                ]);
            }

            $recibosYaAnulados = $recibos->contains(
                fn (ReciboPago $recibo) =>
                    $recibo->estado ===
                    ReciboPago::ESTADO_ANULADO
            );

            if ($recibosYaAnulados) {
                throw ValidationException::withMessages([
                    'operacion' =>
                        'La operación contiene recibos previamente anulados '
                        . 'y requiere revisión.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Validar dependencias del saldo a favor
            |--------------------------------------------------------------------------
            | Si esta operación generó saldo a favor y otra operación lo
            | consumió posteriormente, primero debe anularse la consumidora.
            */
            $dependencias =
                $this->buscarDependenciasSaldoFavor(
                    operacion: $operacion,
                    recibos: $recibos,
                );

            if ($dependencias->isNotEmpty()) {
                $detalleDependencias = $dependencias
                    ->map(function (array $dependencia) {
                        $texto = sprintf(
                            'Recibo %s',
                            $dependencia['identificador']
                        );

                        if ($dependencia['fecha']) {
                            $texto .= ' del '
                                . $dependencia['fecha'];
                        }

                        $texto .= ' por $ '
                            . number_format(
                                $dependencia['valor'],
                                0,
                                ',',
                                '.'
                            );

                        return $texto;
                    })
                    ->implode('; ');

                throw ValidationException::withMessages([
                    'operacion' =>
                        'La operación no puede anularse porque el saldo '
                        . 'a favor generado fue utilizado posteriormente. '
                        . 'Anule primero las siguientes operaciones: '
                        . $detalleDependencias
                        . '.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Revertir movimientos de saldo a favor
            |--------------------------------------------------------------------------
            */
            $reversionesSaldoFavor =
                $this->revertirMovimientosSaldoFavor(
                    recibos: $recibos,
                    registradoPor: $anuladoPor,
                );

            $fechaAnulacion = now();

            /*
            |--------------------------------------------------------------------------
            | Marcar todos los recibos como anulados
            |--------------------------------------------------------------------------
            | Las AplicacionPago se conservan como trazabilidad.
            | Al dejar de estar confirmado el recibo, ya no disminuyen cartera.
            */
            foreach ($recibos as $recibo) {
                $recibo->update([
                    'estado' =>
                        ReciboPago::ESTADO_ANULADO,

                    'anulado_por' =>
                        $anuladoPor,

                    'anulado_en' =>
                        $fechaAnulacion,

                    'motivo_anulacion' =>
                        $this->normalizarMotivo($motivo),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Marcar la operación como anulada
            |--------------------------------------------------------------------------
            */
            $operacion->update([
                'estado' =>
                    OperacionPago::ESTADO_ANULADA,

                'anulado_por' =>
                    $anuladoPor,

                'anulado_en' =>
                    $fechaAnulacion,

                'motivo_anulacion' =>
                    $this->normalizarMotivo($motivo),
            ]);

            $primerRecibo = $recibos->first();

            return [
                'operacion' => $operacion->fresh([
                    'recibos',
                    'anuladoPor',
                ]),

                'operacion_id' =>
                    (int) $operacion->id,

                'numero_recibo' =>
                    (int) ($primerRecibo?->numero_recibo ?? 0),

                'anio' =>
                    (int) ($primerRecibo?->anio ?? 0),

                'cantidad_recibos_anulados' =>
                    $recibos->count(),

                'cantidad_reversiones_saldo_favor' =>
                    $reversionesSaldoFavor->count(),

                'anulado_por' =>
                    $anuladoPor,

                'anulado_en' =>
                    $fechaAnulacion,

                'motivo_anulacion' =>
                    $this->normalizarMotivo($motivo),
            ];
        }, 3);
    }

    /**
     * Busca recibos posteriores que consumieron saldo generado
     * por la operación que se pretende anular.
     *
     * La reconstrucción se hace con criterio FIFO:
     * las aplicaciones consumen primero las generaciones más antiguas.
     */
    private function buscarDependenciasSaldoFavor(
        OperacionPago $operacion,
        Collection $recibos,
    ): Collection {
        $reciboIds = $recibos
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $generacionesObjetivo = MovimientoSaldoFavor::query()
            ->whereIn(
                'recibo_pago_id',
                $reciboIds
            )
            ->where(
                'tipo_movimiento',
                MovimientoSaldoFavor::TIPO_GENERACION
            )
            ->get();

        if ($generacionesObjetivo->isEmpty()) {
            return collect();
        }

        $generacionIdsObjetivo = $generacionesObjetivo
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        $saldoFavorIds = $generacionesObjetivo
            ->pluck('saldo_favor_estudiante_id')
            ->unique()
            ->values();

        $dependencias = collect();

        foreach ($saldoFavorIds as $saldoFavorId) {
            /*
             * Se consideran únicamente movimientos originales cuyos
             * recibos todavía estén confirmados. Los recibos anulados
             * ya fueron compensados y no deben intervenir.
             */
            $movimientos = MovimientoSaldoFavor::query()
                ->with([
                    'reciboPago',
                ])
                ->where(
                    'saldo_favor_estudiante_id',
                    $saldoFavorId
                )
                ->whereIn(
                    'tipo_movimiento',
                    [
                        MovimientoSaldoFavor::TIPO_GENERACION,
                        MovimientoSaldoFavor::TIPO_APLICACION,
                    ]
                )
                ->whereHas(
                    'reciboPago',
                    function ($query) {
                        $query->where(
                            'estado',
                            ReciboPago::ESTADO_CONFIRMADO
                        );
                    }
                )
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            /*
             * Cada generación se trata como un lote independiente.
             */
            $lotes = [];

            foreach ($movimientos as $movimiento) {
                $valorMovimiento = round(
                    (float) $movimiento->valor,
                    2
                );

                if ($valorMovimiento <= 0) {
                    continue;
                }

                if (
                    $movimiento->tipo_movimiento ===
                    MovimientoSaldoFavor::TIPO_GENERACION
                ) {
                    $lotes[] = [
                        'movimiento_id' =>
                            (int) $movimiento->id,

                        'restante' =>
                            $valorMovimiento,

                        'es_objetivo' =>
                            $generacionIdsObjetivo->has(
                                (int) $movimiento->id
                            ),
                    ];

                    continue;
                }

                if (
                    $movimiento->tipo_movimiento !==
                    MovimientoSaldoFavor::TIPO_APLICACION
                ) {
                    continue;
                }

                $pendienteConsumir = $valorMovimiento;

                foreach ($lotes as &$lote) {
                    if ($pendienteConsumir <= 0) {
                        break;
                    }

                    if ($lote['restante'] <= 0) {
                        continue;
                    }

                    $consumido = min(
                        $lote['restante'],
                        $pendienteConsumir
                    );

                    $lote['restante'] = round(
                        $lote['restante'] - $consumido,
                        2
                    );

                    $pendienteConsumir = round(
                        $pendienteConsumir - $consumido,
                        2
                    );

                    /*
                     * Solo bloqueamos por consumos realizados por
                     * otra operación. Los movimientos pertenecientes
                     * a la misma operación se revierten juntos.
                     */
                    $reciboConsumidor =
                        $movimiento->reciboPago;

                    $esOtraOperacion =
                        $reciboConsumidor
                        && (int) $reciboConsumidor
                            ->operacion_pago_id
                            !== (int) $operacion->id;

                    if (
                        $lote['es_objetivo']
                        && $esOtraOperacion
                        && $consumido > 0
                    ) {
                        $identificador =
                            (string) $reciboConsumidor
                                ->numero_recibo;

                        if (
                            filled(
                                $reciboConsumidor->anio
                            )
                        ) {
                            $identificador .= '/'
                                . $reciboConsumidor->anio;
                        }

                        $clave = (string)
                            $reciboConsumidor
                                ->operacion_pago_id;

                        $dependenciaActual =
                            $dependencias->get(
                                $clave,
                                [
                                    'operacion_id' =>
                                        (int)
                                        $reciboConsumidor
                                            ->operacion_pago_id,

                                    'identificador' =>
                                        $identificador,

                                    'fecha' =>
                                        $reciboConsumidor
                                            ->fecha_pago
                                            ?->format('d/m/Y'),

                                    'valor' => 0.0,
                                ]
                            );

                        $dependenciaActual['valor'] =
                            round(
                                $dependenciaActual['valor']
                                + $consumido,
                                2
                            );

                        $dependencias->put(
                            $clave,
                            $dependenciaActual
                        );
                    }
                }

                unset($lote);
            }
        }

        return $dependencias->values();
    }

    /**
     * Revierte todos los movimientos originales de saldo a favor
     * asociados con los recibos de la operación.
     */
    private function revertirMovimientosSaldoFavor(
        Collection $recibos,
        int $registradoPor,
    ): Collection {
        $reciboIds = $recibos
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        /*
         * Se procesan del más reciente al más antiguo.
         * Así, si una misma operación aplicó y generó saldo,
         * primero se devuelve la aplicación y luego se retira
         * la generación.
         */
        $movimientos = MovimientoSaldoFavor::query()
            ->whereIn(
                'recibo_pago_id',
                $reciboIds
            )
            ->whereIn(
                'tipo_movimiento',
                [
                    MovimientoSaldoFavor::TIPO_GENERACION,
                    MovimientoSaldoFavor::TIPO_APLICACION,
                ]
            )
            ->orderByDesc('id')
            ->lockForUpdate()
            ->get();

        $reversiones = collect();

        foreach ($movimientos as $movimiento) {
            $saldoFavor = SaldoFavorEstudiante::query()
                ->whereKey(
                    $movimiento
                        ->saldo_favor_estudiante_id
                )
                ->lockForUpdate()
                ->first();

            if (! $saldoFavor) {
                throw ValidationException::withMessages([
                    'saldoFavor' =>
                        'No se encontró el saldo a favor que debe revertirse.',
                ]);
            }

            $valor = round(
                (float) $movimiento->valor,
                2
            );

            if ($valor <= 0) {
                continue;
            }

            $saldoAnterior = round(
                (float) $saldoFavor->saldo_disponible,
                2
            );

            /*
             * Revertir una generación:
             * disminuye el saldo disponible.
             *
             * Revertir una aplicación:
             * devuelve el saldo utilizado.
             */
            if (
                $movimiento->tipo_movimiento ===
                MovimientoSaldoFavor::TIPO_GENERACION
            ) {
                $saldoPosterior = round(
                    $saldoAnterior - $valor,
                    2
                );

                if ($saldoPosterior < 0) {
                    throw ValidationException::withMessages([
                        'saldoFavor' =>
                            'No es posible anular la operación porque '
                            . 'el saldo a favor generado ya no está '
                            . 'completamente disponible.',
                    ]);
                }

                $detalle =
                    'Reversión de saldo a favor generado '
                    . 'por anulación del recibo.';
            } else {
                $saldoPosterior = round(
                    $saldoAnterior + $valor,
                    2
                );

                $detalle =
                    'Devolución de saldo a favor aplicado '
                    . 'por anulación del recibo.';
            }

            $saldoFavor->update([
                'saldo_disponible' =>
                    $saldoPosterior,
            ]);

            $reversiones->push(
                MovimientoSaldoFavor::create([
                    'saldo_favor_estudiante_id' =>
                        $saldoFavor->id,

                    'recibo_pago_id' =>
                        $movimiento->recibo_pago_id,

                    'aplicacion_pago_id' =>
                        $movimiento->aplicacion_pago_id,

                    'tipo_movimiento' =>
                        MovimientoSaldoFavor::TIPO_REVERSION,

                    'valor' =>
                        $valor,

                    'saldo_anterior' =>
                        $saldoAnterior,

                    'saldo_posterior' =>
                        $saldoPosterior,

                    'registrado_por' =>
                        $registradoPor,

                    'registrado_en' =>
                        now(),

                    'detalle' =>
                        $detalle
                        . ' Movimiento original #'
                        . $movimiento->id
                        . '.',
                ])
            );
        }

        return $reversiones;
    }

    private function validarDatosGenerales(
        int $operacionPagoId,
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
        int $anuladoPor,
    ): void {
        $errores = [];

        if ($operacionPagoId <= 0) {
            $errores['operacion'] =
                'No se encontró una operación válida.';
        }

        if ($studentId <= 0) {
            $errores['student_id'] =
                'No se encontró un estudiante válido.';
        }

        if ($sedeId <= 0) {
            $errores['sede_id'] =
                'No se encontró una sede válida.';
        }

        if ($periodoLectivoId <= 0) {
            $errores['periodo_lectivo_id'] =
                'No se encontró un periodo lectivo válido.';
        }

        if ($anuladoPor <= 0) {
            $errores['anulado_por'] =
                'No se encontró el usuario que realiza la anulación.';
        }

        if ($errores !== []) {
            throw ValidationException::withMessages(
                $errores
            );
        }
    }

    private function normalizarMotivo(
        ?string $motivo
    ): ?string {
        $motivo = trim((string) $motivo);

        return $motivo !== ''
            ? $motivo
            : null;
    }
}