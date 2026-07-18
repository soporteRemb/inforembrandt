<?php

namespace App\Services\Financiero\Pagos;

use App\Models\ConsecutivoRecibo;
use Illuminate\Support\Facades\DB;

class GenerarConsecutivoReciboService
{
    /**
     * Genera el siguiente número de recibo por sede y año.
     *
     * Este método debe ejecutarse dentro de una transacción de base de datos.
     */
    public function generar(int $sedeId, int $anio): int
    {
        /*
        |--------------------------------------------------------------------------
        | Buscar y bloquear el consecutivo
        |--------------------------------------------------------------------------
        | lockForUpdate evita que dos cajeros obtengan el mismo número
        | cuando confirman pagos al mismo tiempo.
        */
        $consecutivo = ConsecutivoRecibo::query()
            ->where('sede_id', $sedeId)
            ->where('anio', $anio)
            ->lockForUpdate()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Primera numeración de la sede en ese año
        |--------------------------------------------------------------------------
        */
        if (! $consecutivo) {
            $consecutivo = ConsecutivoRecibo::create([
                'sede_id' => $sedeId,
                'anio' => $anio,
                'ultimo_numero' => 0,
            ]);

            /*
             * Bloqueamos nuevamente el registro recién creado para mantener
             * el mismo comportamiento dentro de la transacción.
             */
            $consecutivo = ConsecutivoRecibo::query()
                ->whereKey($consecutivo->id)
                ->lockForUpdate()
                ->firstOrFail();
        }

        $siguienteNumero = (int) $consecutivo->ultimo_numero + 1;

        $consecutivo->update([
            'ultimo_numero' => $siguienteNumero,
        ]);

        return $siguienteNumero;
    }
}