<?php

namespace App\Services\Financiero\Pagos;

use App\Models\OperacionPago;
use App\Models\ReciboPago;

use App\Services\Financiero\Pagos\ImpresionReciboService;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class DetalleReciboService
{
    public function __construct(
        protected ImpresionReciboService $impresionReciboService,
    ) {
    }

    public function consultar(
        int $operacionPagoId,
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
    ): array {
        $operacion = OperacionPago::query()
            ->with([
                'student.course',
                'registradoPor',
                'recibos' => function ($query) {
                    $query
                        ->with([
                            'conceptoCobro',
                            'recibidoPor',
                            'formasPago.formaPago',
                            'aplicaciones.movimientoCarteraEstudiante',
                            'movimientosSaldoFavor',
                        ])
                        ->orderBy('id');
                },
            ])
            ->whereKey($operacionPagoId)
            ->where('student_id', $studentId)
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->first();

        if (! $operacion) {
            throw new ModelNotFoundException(
                'No se encontró el recibo solicitado.'
            );
        }

        $recibos = $operacion->recibos;

        $primerRecibo = $recibos->first();

        $numeroRecibo = $primerRecibo?->numero_recibo;

        $saldosFinalesPorMovimiento = $recibos
            ->flatMap(function (ReciboPago $recibo) {
                return $recibo->aplicaciones;
            })
            ->groupBy('movimiento_cartera_estudiante_id')
            ->map(function ($aplicaciones) {
                return (float) $aplicaciones
                    ->sortByDesc('id')
                    ->first()
                    ->saldo_posterior;
            });

        $ultimaAplicacionPorMovimiento = $recibos
            ->flatMap(function (ReciboPago $recibo) {
                return $recibo->aplicaciones;
            })
            ->groupBy('movimiento_cartera_estudiante_id')
            ->map(function ($aplicaciones) {
                return (int) $aplicaciones
                    ->sortByDesc('id')
                    ->first()
                    ->id;
            });    

        $lineas = $recibos
            ->map(function (ReciboPago $recibo) use (
                $saldosFinalesPorMovimiento,
                $ultimaAplicacionPorMovimiento
            ) {
                $formaPago = $recibo->formasPago->first();
                $aplicacion = $recibo->aplicaciones->first();
                $movimiento = $aplicacion?->movimientoCarteraEstudiante;
                $movimientoId = $aplicacion?->movimiento_cartera_estudiante_id;

                $esUltimaAplicacionDelConcepto =
                    $aplicacion
                    && $movimientoId
                    && (int) ($ultimaAplicacionPorMovimiento[$movimientoId] ?? 0)
                        === (int) $aplicacion->id;

                $saldoFinalConcepto = $movimientoId
                    ? (float) ($saldosFinalesPorMovimiento[$movimientoId] ?? 0)
                    : 0;

                $tipoConcepto = strtolower(
                    trim((string) ($movimiento?->tipo_concepto ?? ''))
                );

                return [
                    'id' => (int) $recibo->id,

                    'concepto' => $recibo->conceptoCobro?->descripcion
                        ?? $movimiento?->descripcion
                        ?? 'Concepto de pago',

                    'mes' => $recibo->mes ?: '',

                    'es_obligatorio' =>
                        $tipoConcepto === 'obligatorio',

                    'forma_pago' => $formaPago?->formaPago?->nombre
                        ?? 'Sin definir',

                    'numero_referencia' =>
                        $formaPago?->numero_referencia,

                    'fecha_consignacion' =>
                        $formaPago?->fecha_consignacion?->format('d/m/Y'),

                    'valor_recibido' =>
                        (float) $recibo->valor_recibido,

                    'descuento' =>
                        (float) $recibo->descuento,

                    'valor_aplicado' =>
                        (float) $recibo->valor_aplicado,

                    'saldo_favor_generado' =>
                        (float) $recibo->saldo_favor_generado,

                    'saldo_anterior' =>
                        (float) ($aplicacion?->saldo_anterior ?? 0),

                    'saldo_posterior' =>
                        $saldoFinalConcepto,

                    'mostrar_saldo_pendiente' =>
                        $esUltimaAplicacionDelConcepto
                        && $saldoFinalConcepto > 0,

                    'detalle' => $recibo->detalle,

                    'estado' => $recibo->estado,
                ];
            })
            ->values()
            ->toArray();

        $estadoImpresion = $this->impresionReciboService->obtenerEstado(
            operacionPagoId: (int) $operacion->id,
        );

        $student = $operacion->student;

        $nombreEstudiante = trim(implode(' ', array_filter([
            $student?->primer_nombre,
            $student?->segundo_nombre,
            $student?->primer_apellido,
            $student?->segundo_apellido,
        ])));

        return [
            'operacion_id' => (int) $operacion->id,
            'numero_recibo' => $numeroRecibo,
            'anio' => $primerRecibo?->anio,

            'fecha' => $operacion->registrado_en?->format(
                'd/m/Y h:i a'
            ),

            'estado' => $operacion->estado,

            'estado_texto' => match ($operacion->estado) {
                OperacionPago::ESTADO_CONFIRMADA => 'Confirmado',
                OperacionPago::ESTADO_ANULADA_PARCIALMENTE =>
                    'Anulado parcialmente',
                OperacionPago::ESTADO_ANULADA => 'Anulado',
                default => ucfirst((string) $operacion->estado),
            },

            'recibido_de' => $operacion->recibido_de,

            'registrado_por' =>
                $operacion->registradoPor?->name
                ?? $operacion->registradoPor?->nombre
                ?? 'Usuario no disponible',

            'estudiante' => [
                'id' => (int) $student?->id,
                'nombre' => $nombreEstudiante,
                'codigo' => $student?->codigo,
                'documento' => $student?->documento,
                'grado' => $student?->course?->grado,
                'curso' => $student?->course?->curso,
            ],

            'subtotal' => (float) $operacion->subtotal,
            'total_descuentos' =>
                (float) $operacion->total_descuentos,
            'total_recibido' =>
                (float) $operacion->total_recibido,

            'saldo_favor_generado' => round(
                (float) $recibos->sum(
                    fn (ReciboPago $recibo) =>
                        (float) $recibo->saldo_favor_generado
                ),
                2
            ),

            'lineas' => $lineas,

            'impresion' => $estadoImpresion,
        ];
    }
}