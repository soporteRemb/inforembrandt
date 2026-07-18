<?php

namespace App\Services\Financiero\Pagos;

use App\Models\OperacionPago;
use Carbon\CarbonInterface;

class CrearOperacionPagoService
{
    /**
     * Crea la operación que agrupa toda la atención de caja.
     */
    public function crear(
        int $sedeId,
        int $periodoLectivoId,
        int $studentId,
        string $recibidoDe,
        float $subtotal,
        float $totalDescuentos,
        float $totalRecibido,
        int $registradoPor,
        CarbonInterface $registradoEn,
    ): OperacionPago {
        return OperacionPago::create([
            'sede_id' => $sedeId,
            'periodo_lectivo_id' => $periodoLectivoId,
            'student_id' => $studentId,
            'recibido_de' => trim($recibidoDe),
            'subtotal' => round($subtotal, 2),
            'total_descuentos' => round($totalDescuentos, 2),
            'total_recibido' => round($totalRecibido, 2),
            'estado' => OperacionPago::ESTADO_CONFIRMADA,
            'registrado_por' => $registradoPor,
            'registrado_en' => $registradoEn,
        ]);
    }
}