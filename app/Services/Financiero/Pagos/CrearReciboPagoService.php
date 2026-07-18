<?php

namespace App\Services\Financiero\Pagos;

use App\Models\OperacionPago;
use App\Models\ReciboFormaPago;
use App\Models\ReciboPago;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class CrearReciboPagoService
{
    public function __construct(
        protected AplicarPagoCarteraService $aplicarPagoCarteraService,
        protected SaldoFavorService $saldoFavorService,
    ) {
    }

    /**
     * Crea una línea financiera de la cola.
     *
     * Todas las líneas de una misma operación comparten:
     * - operacion_pago_id;
     * - numero_recibo;
     * - sede;
     * - periodo lectivo;
     * - estudiante.
     */
    public function crear(
        OperacionPago $operacion,
        array $fila,
        int $numeroRecibo,
        int $anio,
        int $registradoPor,
        CarbonInterface $fechaPago,
    ): ReciboPago {
        $movimientoId = (int) ($fila['movimiento_id'] ?? 0);
        $formaPagoId = (int) ($fila['forma_pago_id'] ?? 0);

        $valorRecibido = round(
            (float) ($fila['valor_recibido'] ?? 0),
            2
        );

        $descuento = round(
            (float) ($fila['descuento'] ?? 0),
            2
        );

        if ($movimientoId <= 0) {
            throw ValidationException::withMessages([
                'colaPagos' => 'Una fila de la cola no tiene una obligación válida.',
            ]);
        }

        if ($formaPagoId <= 0) {
            throw ValidationException::withMessages([
                'colaPagos' => 'Una fila de la cola no tiene una forma de pago válida.',
            ]);
        }

        if ($valorRecibido <= 0) {
            throw ValidationException::withMessages([
                'colaPagos' => 'El valor recibido debe ser mayor que cero.',
            ]);
        }

        if ($descuento < 0) {
            throw ValidationException::withMessages([
                'colaPagos' => 'El descuento no puede ser negativo.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Crear la línea del recibo
        |--------------------------------------------------------------------------
        | Se crea primero porque AplicacionPago y ReciboFormaPago necesitan
        | recibo_pago_id. Los valores exactos de aplicación se actualizan
        | después de aplicar la fila contra la cartera bloqueada.
        */
        $recibo = ReciboPago::create([
            'operacion_pago_id' => $operacion->id,
            'sede_id' => $operacion->sede_id,
            'periodo_lectivo_id' => $operacion->periodo_lectivo_id,
            'student_id' => $operacion->student_id,

            'concepto_cobro_id' => $fila['concepto_cobro_id'] ?? null,
            'numero_recibo' => $numeroRecibo,
            'anio' => $anio,
            'tipo_registro' => ReciboPago::TIPO_OBLIGACION,

            'mes' => filled($fila['mes'] ?? null)
                ? $fila['mes']
                : null,

            'mes_numero' => $fila['mes_numero'] ?? null,

            /*
             * Son valores provisionales. Luego se reemplazan con los saldos
             * reales obtenidos al bloquear y aplicar la obligación.
             */
            'valor_ordinario' => round(
                (float) ($fila['saldo_anterior'] ?? 0),
                2
            ),

            'tipo_limite_extemporaneo_id' => null,

            'valor_vigente' => round(
                (float) ($fila['saldo_anterior'] ?? 0),
                2
            ),

            'penalizacion' => 0,
            'descuento' => $descuento,
            'valor_recibido' => $valorRecibido,
            'valor_aplicado' => 0,
            'saldo_favor_generado' => 0,

            'recibido_de' => trim(
                (string) ($fila['recibido_de'] ?? $operacion->recibido_de)
            ),

            'detalle' => filled($fila['detalle'] ?? null)
                ? trim((string) $fila['detalle'])
                : null,

            'estado' => ReciboPago::ESTADO_CONFIRMADO,
            'recibido_por' => $registradoPor,
            'fecha_pago' => $fechaPago,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Registrar la forma de pago de esta fila
        |--------------------------------------------------------------------------
        | La referencia y la fecha siguen siendo opcionales en la versión 1.
        */
        ReciboFormaPago::create([
            'recibo_pago_id' => $recibo->id,
            'forma_pago_id' => $formaPagoId,
            'valor' => $valorRecibido,

            'numero_referencia' => filled($fila['numero_referencia'] ?? null)
                ? trim((string) $fila['numero_referencia'])
                : null,

            'fecha_consignacion' => filled($fila['fecha_consignacion'] ?? null)
                ? $fila['fecha_consignacion']
                : null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Aplicar dinero + descuento a la obligación
        |--------------------------------------------------------------------------
        | El descuento cubre cartera, aunque no representa dinero recibido.
        */
        $valorSolicitadoAplicar = round(
            $valorRecibido + $descuento,
            2
        );

        $aplicacion = $this->aplicarPagoCarteraService->aplicar(
            recibo: $recibo,
            movimientoId: $movimientoId,
            studentId: (int) $operacion->student_id,
            sedeId: (int) $operacion->sede_id,
            periodoLectivoId: (int) $operacion->periodo_lectivo_id,
            valorSolicitado: $valorSolicitadoAplicar,
        );

        /*
        |--------------------------------------------------------------------------
        | Validar el descuento contra el saldo real bloqueado
        |--------------------------------------------------------------------------
        */
        $saldoAnteriorReal = (float) $aplicacion->saldo_anterior;
        $valorAplicadoReal = (float) $aplicacion->valor_aplicado;

        if ($descuento > $saldoAnteriorReal) {
            throw ValidationException::withMessages([
                'colaPagos' => sprintf(
                    'El descuento de la fila "%s" supera el saldo real de la obligación.',
                    $fila['concepto'] ?? 'seleccionada'
                ),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Calcular excedente real
        |--------------------------------------------------------------------------
        | Parte del valor aplicado puede estar cubierta mediante descuento.
        | Solo el dinero que exceda lo necesario genera saldo a favor.
        */
        $dineroNecesarioParaAplicacion = max(
            0,
            round($valorAplicadoReal - $descuento, 2)
        );

        $saldoFavorGenerado = max(
            0,
            round(
                $valorRecibido - $dineroNecesarioParaAplicacion,
                2
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Completar la fotografía financiera del recibo
        |--------------------------------------------------------------------------
        */
        $recibo->update([
            'valor_ordinario' => $saldoAnteriorReal,
            'valor_vigente' => $saldoAnteriorReal,
            'valor_aplicado' => $valorAplicadoReal,
            'saldo_favor_generado' => $saldoFavorGenerado,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generar saldo a favor general
        |--------------------------------------------------------------------------
        */
        if ($saldoFavorGenerado > 0) {
            $this->saldoFavorService->generar(
                recibo: $recibo,
                sedeId: (int) $operacion->sede_id,
                periodoLectivoId: (int) $operacion->periodo_lectivo_id,
                studentId: (int) $operacion->student_id,
                valor: $saldoFavorGenerado,
                registradoPor: $registradoPor,
                detalle: sprintf(
                    'Excedente generado en el recibo N.º %d por el concepto %s.',
                    $numeroRecibo,
                    $fila['concepto'] ?? 'obligación'
                ),
            );
        }

        return $recibo->fresh([
            'formasPago',
            'aplicaciones',
            'movimientosSaldoFavor',
        ]);
    }
}