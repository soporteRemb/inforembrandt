<?php

namespace App\Services\Financiero\Pagos;

use App\Models\ReciboPago;

class BuscarReciboGlobalService
{
    public function buscar(
        string $numeroRecibo,
        int $sedeId,
        int $periodoLectivoId,
    ): ?array {
        $numeroRecibo = trim($numeroRecibo);

        if (
            $numeroRecibo === ''
            || $sedeId <= 0
            || $periodoLectivoId <= 0
        ) {
            return null;
        }

        $recibo = ReciboPago::query()
            ->with([
                'operacionPago.student.course',
            ])
            ->where('numero_recibo', $numeroRecibo)
            ->whereHas(
                'operacionPago',
                function ($query) use (
                    $sedeId,
                    $periodoLectivoId
                ) {
                    $query
                        ->where('sede_id', $sedeId)
                        ->where(
                            'periodo_lectivo_id',
                            $periodoLectivoId
                        );
                }
            )
            ->latest('id')
            ->first();

        $operacion = $recibo?->operacionPago;

        if (! $operacion) {
            return null;
        }

        return [
            'operacion_pago_id' =>
                (int) $operacion->id,

            'student_id' =>
                (int) $operacion->student_id,

            'sede_id' =>
                (int) $operacion->sede_id,

            'periodo_lectivo_id' =>
                (int) $operacion->periodo_lectivo_id,

            'numero_recibo' =>
                (string) $recibo->numero_recibo,
        ];
    }
}