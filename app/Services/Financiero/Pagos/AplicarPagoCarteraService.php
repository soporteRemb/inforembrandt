<?php

namespace App\Services\Financiero\Pagos;

use App\Models\AplicacionPago;
use App\Models\MovimientoCarteraEstudiante;
use App\Models\ReciboPago;
use Illuminate\Validation\ValidationException;

class AplicarPagoCarteraService
{
    /**
     * Aplica un valor a una obligación individual del estudiante.
     *
     * El valor aplicado puede incluir:
     * - dinero recibido;
     * - descuento concedido.
     *
     * No modifica el valor histórico de la causación. La deuda pendiente
     * siempre se calcula a partir de las aplicaciones confirmadas.
     */
    public function aplicar(
        ReciboPago $recibo,
        int $movimientoId,
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
        float $valorSolicitado,
    ): AplicacionPago {
        /*
        |--------------------------------------------------------------------------
        | Bloquear la obligación
        |--------------------------------------------------------------------------
        | Evita que dos cajeros apliquen simultáneamente pagos sobre el mismo
        | saldo y terminen cubriendo más de lo realmente adeudado.
        */
        $movimiento = MovimientoCarteraEstudiante::query()
            ->whereKey($movimientoId)
            ->where('student_id', $studentId)
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->lockForUpdate()
            ->first();

        if (! $movimiento) {
            throw ValidationException::withMessages([
                'colaPagos' => 'Una de las obligaciones seleccionadas ya no está disponible.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Pagos confirmados ya aplicados
        |--------------------------------------------------------------------------
        | Los recibos anulados no disminuyen la obligación.
        */
        $valorAplicadoAnterior = (float) AplicacionPago::query()
            ->where(
                'movimiento_cartera_estudiante_id',
                $movimiento->id
            )
            ->whereHas('reciboPago', function ($query) {
                $query->where(
                    'estado',
                    ReciboPago::ESTADO_CONFIRMADO
                );
            })
            ->sum('valor_aplicado');

        $valorObligacion = (float) $movimiento->valor;

        $saldoAnterior = max(
            0,
            round($valorObligacion - $valorAplicadoAnterior, 2)
        );

        if ($saldoAnterior <= 0) {
            throw ValidationException::withMessages([
                'colaPagos' => sprintf(
                    'La obligación "%s" ya se encuentra completamente cubierta.',
                    $movimiento->descripcion
                        ?? $movimiento->conceptoCobro?->descripcion
                        ?? 'seleccionada'
                ),
            ]);
        }

        $valorSolicitado = max(
            0,
            round($valorSolicitado, 2)
        );

        /*
         * Nunca se aplica a la obligación más de su saldo real.
         * Cualquier dinero excedente se gestionará en SaldoFavorService.
         */
        $valorAplicado = min(
            $saldoAnterior,
            $valorSolicitado
        );

        if ($valorAplicado <= 0) {
            throw ValidationException::withMessages([
                'colaPagos' => 'El valor aplicado a la obligación debe ser mayor que cero.',
            ]);
        }

        $saldoPosterior = max(
            0,
            round($saldoAnterior - $valorAplicado, 2)
        );

        return AplicacionPago::create([
            'recibo_pago_id' => $recibo->id,
            'movimiento_cartera_estudiante_id' => $movimiento->id,
            'valor_aplicado' => $valorAplicado,
            'saldo_anterior' => $saldoAnterior,
            'saldo_posterior' => $saldoPosterior,
        ]);
    }
}