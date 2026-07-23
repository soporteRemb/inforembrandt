<?php

namespace App\Services\Financiero\Pagos;

use App\Models\AsignacionConcepto;
use App\Models\MovimientoCarteraEstudiante;
use App\Models\ReciboPago;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class CalcularValorVigenteObligacionService
{
    public function calcular(
        MovimientoCarteraEstudiante $movimiento,
        CarbonInterface|string|null $fechaCorte = null,
    ): array {
        $fechaConsulta = $fechaCorte
            ? Carbon::parse($fechaCorte)->endOfDay()
            : now()->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Valor original de la causación
        |--------------------------------------------------------------------------
        |
        | movimiento->valor ya contiene el valor definitivo inicial:
        | - tarifa ordinaria, o
        | - valor personalizado del estudiante.
        |
        | Nunca modificamos este valor histórico.
        */
        $valorOriginal = round(
            (float) $movimiento->valor,
            2
        );

        $valorVigente = $valorOriginal;
        $vencimientoAplicado = null;

        /*
        |--------------------------------------------------------------------------
        | Pagos que siguen siendo válidos
        |--------------------------------------------------------------------------
        |
        | Una aplicación cuyo recibo fue anulado deja de descontar cartera.
        */
        $aplicacionesConfirmadas = $movimiento
            ->aplicacionesPago
            ->filter(function ($aplicacion) use ($fechaConsulta) {
                $recibo = $aplicacion->reciboPago;

                if (
                    ! $recibo
                    || $recibo->estado
                        !== ReciboPago::ESTADO_CONFIRMADO
                ) {
                    return false;
                }

                $fechaPago = $recibo->fecha_pago
                    ?? $aplicacion->created_at;

                if (! $fechaPago) {
                    return false;
                }

                /*
                * Para una consulta histórica solo cuentan los pagos
                * realizados hasta la fecha de corte.
                */
                return Carbon::parse($fechaPago)
                    ->lte($fechaConsulta);
            })
            ->sortBy(function ($aplicacion) {
                return $aplicacion->reciboPago?->fecha_pago
                    ?? $aplicacion->created_at;
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Buscar la asignación correspondiente
        |--------------------------------------------------------------------------
        |
        | El movimiento no conserva asignacion_concepto_id, por lo que se
        | localiza usando el contexto académico que sí quedó almacenado:
        |
        | sede + periodo lectivo + grado + concepto.
        */
        $asignacion = AsignacionConcepto::query()
            ->with([
                'vencimientos.tipoLimiteExtemporaneo',
            ])
            ->where('sede_id', $movimiento->sede_id)
            ->where(
                'periodo_lectivo_id',
                $movimiento->periodo_lectivo_id
            )
            ->where('grado', $movimiento->grado)
            ->where(
                'concepto_cobro_id',
                $movimiento->concepto_cobro_id
            )
            ->where('activo', true)
            ->first();


        $tarifaOrdinaria = round(
            (float) (
                $asignacion?->tarifa_ordinaria
                ?? $movimiento->valor_base
                ?? $valorOriginal
            ),
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Aplicar tarifas configuradas por fecha
        |--------------------------------------------------------------------------
        |
        | No dependemos de nombres como:
        | - 30 días
        | - 60 días
        | - 90 días
        |
        | Tampoco asumimos una cantidad fija de límites.
        |
        | La tarifa empieza a aplicar después de la fecha de vencimiento.
        | Ejemplo: vencimiento 28/02 → nuevo valor desde el 01/03.
        */
        if ($asignacion) {
            $vencimientos = $asignacion
                ->vencimientos
                ->filter(function ($vencimiento) use ($fechaConsulta) {
                    return $vencimiento->fecha_vencimiento
                        && (float) $vencimiento->valor > 0
                        && $fechaConsulta->isAfter(
                            $vencimiento->fecha_vencimiento->endOfDay()
                        );
                })
                ->sortBy([
                    ['fecha_vencimiento', 'asc'],
                    ['id', 'asc'],
                ])
                ->values();

            foreach ($vencimientos as $vencimiento) {
                /*
                 * Revisamos cuánto se había pagado, con recibos que todavía
                 * siguen confirmados, hasta la fecha límite inclusive.
                 */
                $pagadoHastaElLimite = round(
                    (float) $aplicacionesConfirmadas
                        ->filter(function ($aplicacion) use ($vencimiento) {
                            $fechaPago = $aplicacion
                                ->reciboPago
                                ?->fecha_pago;

                            return $fechaPago
                                && $fechaPago->lte(
                                    $vencimiento
                                        ->fecha_vencimiento
                                        ->endOfDay()
                                );
                        })
                        ->sum(
                            fn ($aplicacion) =>
                                (float) $aplicacion->valor_aplicado
                        ),
                    2
                );

                /*
                 * Si antes o durante la fecha límite la obligación ya estaba
                 * totalmente cubierta con pagos aún confirmados, no se aplica
                 * este aumento ni los siguientes.
                 */
                if ($pagadoHastaElLimite >= $valorVigente) {
                    break;
                }

                $valorLimite = round(
                    (float) $vencimiento->valor,
                    2
                );

                /*
                * El valor registrado en el límite representa cuánto
                * valdría la tarifa ordinaria en esa fecha.
                *
                * Calculamos solamente el aumento configurado:
                *
                * tarifa límite - tarifa ordinaria.
                *
                * Después sumamos ese aumento al valor real causado,
                * que puede ser ordinario o personalizado.
                */
                $aumentoExtemporaneo = max(
                    0,
                    round(
                        $valorLimite - $tarifaOrdinaria,
                        2
                    )
                );

                $valorLimite = round(
                    (float) $vencimiento->valor,
                    2
                );

                $aumentoExtemporaneo = max(
                    0,
                    round(
                        $valorLimite - $tarifaOrdinaria,
                        2
                    )
                );

                $valorVigente = round(
                    $valorOriginal + $aumentoExtemporaneo,
                    2
                );

                $vencimientoAplicado = $vencimiento;

                
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Total actualmente aplicado
        |--------------------------------------------------------------------------
        */
        $valorAplicado = round(
            (float) $aplicacionesConfirmadas->sum(
                fn ($aplicacion) =>
                    (float) $aplicacion->valor_aplicado
            ),
            2
        );

        $saldoPendiente = max(
            0,
            round(
                $valorVigente - $valorAplicado,
                2
            )
        );

        return [
            'valor_original' =>
                $valorOriginal,

            'valor_vigente' =>
                $valorVigente,

            'valor_aplicado' =>
                $valorAplicado,

            'saldo_pendiente' =>
                $saldoPendiente,

            

            'tiene_tarifa_extemporanea' =>
                $vencimientoAplicado !== null,

            'asignacion_concepto_id' =>
                $asignacion?->id,

            'vencimiento_aplicado_id' =>
                $vencimientoAplicado?->id,

            'tipo_limite_extemporaneo_id' =>
                $vencimientoAplicado
                    ?->tipo_limite_extemporaneo_id,

            'tipo_limite_texto' =>
                $vencimientoAplicado
                    ?->tipoLimiteExtemporaneo
                    ?->nombre
                ?? null,

            'fecha_vencimiento_aplicada' =>
                $vencimientoAplicado
                    ?->fecha_vencimiento
                    ?->format('Y-m-d'),

            'fecha_corte' =>
                $fechaConsulta->format('Y-m-d'),

            'aumento_extemporaneo' =>
            max(
                0,
                round(
                    $valorVigente - $valorOriginal,
                    2
                )
            ),


        ];
    }
}