<?php

namespace App\Services\Financiero\Pagos;

use App\Models\MovimientoSaldoFavor;
use App\Models\ReciboPago;
use App\Models\SaldoFavorEstudiante;
use Illuminate\Validation\ValidationException;

class SaldoFavorService
{
    /**
     * Genera saldo a favor general para el estudiante.
     *
     * El saldo no queda vinculado al concepto que originó el excedente.
     * Puede utilizarse posteriormente en cualquier obligación del mismo
     * estudiante, sede y periodo lectivo.
     */
    public function generar(
        ReciboPago $recibo,
        int $sedeId,
        int $periodoLectivoId,
        int $studentId,
        float $valor,
        int $registradoPor,
        ?string $detalle = null,
    ): ?MovimientoSaldoFavor {
        $valor = round($valor, 2);

        if ($valor <= 0) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Obtener o crear el saldo general del estudiante
        |--------------------------------------------------------------------------
        */
        $saldoFavor = SaldoFavorEstudiante::query()
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('student_id', $studentId)
            ->lockForUpdate()
            ->first();

        if (! $saldoFavor) {
            $saldoFavor = SaldoFavorEstudiante::create([
                'sede_id' => $sedeId,
                'periodo_lectivo_id' => $periodoLectivoId,
                'student_id' => $studentId,
                'saldo_disponible' => 0,
            ]);

            $saldoFavor = SaldoFavorEstudiante::query()
                ->whereKey($saldoFavor->id)
                ->lockForUpdate()
                ->firstOrFail();
        }

        $saldoAnterior = (float) $saldoFavor->saldo_disponible;

        $saldoPosterior = round(
            $saldoAnterior + $valor,
            2
        );

        $saldoFavor->update([
            'saldo_disponible' => $saldoPosterior,
        ]);

        return MovimientoSaldoFavor::create([
            'saldo_favor_estudiante_id' => $saldoFavor->id,
            'recibo_pago_id' => $recibo->id,
            'aplicacion_pago_id' => null,
            'tipo_movimiento' => MovimientoSaldoFavor::TIPO_GENERACION,
            'valor' => $valor,
            'saldo_anterior' => $saldoAnterior,
            'saldo_posterior' => $saldoPosterior,
            'registrado_por' => $registradoPor,
            'registrado_en' => now(),
            'detalle' => $detalle
                ?: 'Saldo a favor generado por excedente en el pago.',
        ]);
    }

    /**
     * Consulta el saldo general disponible.
     */
    public function obtenerDisponible(
        int $sedeId,
        int $periodoLectivoId,
        int $studentId,
    ): float {
        return (float) SaldoFavorEstudiante::query()
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('student_id', $studentId)
            ->value('saldo_disponible');
    }

    /**
     * Aplica automáticamente lo necesario del saldo a favor.
     *
     * La interfaz solo tendrá la opción:
     *
     * [ ] Usar saldo a favor
     *
     * No se permitirá escribir manualmente cuánto utilizar.
     */
    public function aplicar(
        ReciboPago $recibo,
        int $sedeId,
        int $periodoLectivoId,
        int $studentId,
        float $valorNecesario,
        int $registradoPor,
        ?int $aplicacionPagoId = null,
        ?string $detalle = null,
    ): float {
        $valorNecesario = round($valorNecesario, 2);

        if ($valorNecesario <= 0) {
            return 0;
        }

        $saldoFavor = SaldoFavorEstudiante::query()
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('student_id', $studentId)
            ->lockForUpdate()
            ->first();

        if (! $saldoFavor) {
            return 0;
        }

        $saldoAnterior = (float) $saldoFavor->saldo_disponible;

        if ($saldoAnterior <= 0) {
            return 0;
        }

        /*
         * Siempre usa automáticamente el menor valor entre:
         * - saldo disponible;
         * - valor necesario para completar la obligación.
         */
        $valorAplicado = min(
            $saldoAnterior,
            $valorNecesario
        );

        if ($valorAplicado <= 0) {
            return 0;
        }

        $saldoPosterior = round(
            $saldoAnterior - $valorAplicado,
            2
        );

        if ($saldoPosterior < 0) {
            throw ValidationException::withMessages([
                'saldoFavor' => 'No fue posible aplicar correctamente el saldo a favor.',
            ]);
        }

        $saldoFavor->update([
            'saldo_disponible' => $saldoPosterior,
        ]);

        MovimientoSaldoFavor::create([
            'saldo_favor_estudiante_id' => $saldoFavor->id,
            'recibo_pago_id' => $recibo->id,
            'aplicacion_pago_id' => $aplicacionPagoId,
            'tipo_movimiento' => MovimientoSaldoFavor::TIPO_APLICACION,
            'valor' => $valorAplicado,
            'saldo_anterior' => $saldoAnterior,
            'saldo_posterior' => $saldoPosterior,
            'registrado_por' => $registradoPor,
            'registrado_en' => now(),
            'detalle' => $detalle
                ?: 'Saldo a favor aplicado al pago de una obligación.',
        ]);

        return $valorAplicado;
    }
}