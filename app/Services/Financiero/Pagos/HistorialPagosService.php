<?php

namespace App\Services\Financiero\Pagos;

use App\Models\ReciboPago;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HistorialPagosService
{
    /**
     * Consulta el historial financiero de un estudiante.
     *
     * Cada elemento representa una línea del recibo:
     * concepto + mes + forma de pago + valor.
     */
    public function consultar(
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
        ?string $numeroRecibo = null,
        ?string $concepto = null,
        ?string $fechaDesde = null,
        ?string $fechaHasta = null,
        ?string $estado = null,
        
        int $limite = 100,
    ): array {
        $query = ReciboPago::query()
            ->with([
                'conceptoCobro',
                'recibidoPor',
                'formasPago.formaPago',
                'aplicaciones.movimientoCarteraEstudiante',
            ])
            ->where('student_id', $studentId)
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodoLectivoId);

        $this->aplicarFiltros(
            query: $query,
            numeroRecibo: $numeroRecibo,
            concepto: $concepto,
            fechaDesde: $fechaDesde,
            fechaHasta: $fechaHasta,
            estado: $estado,
        );

        $recibos = $query
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->limit($limite)
            ->get();

        return [
            'filas' => $recibos
                ->map(fn (ReciboPago $recibo) => $this->mapearFila($recibo))
                ->values()
                ->toArray(),

            'resumen' => $this->construirResumen($recibos),
        ];
    }

    private function aplicarFiltros(
        Builder $query,
        ?string $numeroRecibo,
        ?string $concepto,
        ?string $fechaDesde,
        ?string $fechaHasta,
        ?string $estado,
    ): void {
        $numeroRecibo = trim((string) $numeroRecibo);

        if ($numeroRecibo !== '') {
            $query->where(
                'numero_recibo',
                (int) preg_replace('/\D+/', '', $numeroRecibo)
            );
        }

        $concepto = trim((string) $concepto);

        if ($concepto !== '') {
            $query->whereHas(
                'conceptoCobro',
                function (Builder $conceptoQuery) use ($concepto) {
                    $conceptoQuery->where(
                        'descripcion',
                        'like',
                        '%' . $concepto . '%'
                    );
                }
            );
        }

        if ($fechaDesde) {
            $query->whereDate('fecha_pago', '>=', $fechaDesde);
        }

        if ($fechaHasta) {
            $query->whereDate('fecha_pago', '<=', $fechaHasta);
        }

        if (
            $estado
            && in_array($estado, ReciboPago::estados(), true)
        ) {
            $query->where('estado', $estado);
        }
    }

    private function mapearFila(ReciboPago $recibo): array
    {
        $formaPago = $recibo->formasPago->first();

        $aplicacion = $recibo->aplicaciones->first();

        $movimiento = $aplicacion?->movimientoCarteraEstudiante;

        $tipoConcepto = strtolower(
            trim((string) ($movimiento?->tipo_concepto ?? ''))
        );

        $esObligatorio = $tipoConcepto === 'obligatorio';

        return [
            'id' => (int) $recibo->id,
            'operacion_pago_id' => (int) $recibo->operacion_pago_id,

            'grupo_recibo' => $recibo->operacion_pago_id
                . '-' . $recibo->numero_recibo,

            'numero_recibo' => (int) $recibo->numero_recibo,
            'anio' => (int) $recibo->anio,

            'fecha_pago' => $recibo->fecha_pago?->format(
                'd/m/Y h:i a'
            ),

            'concepto' => $recibo->conceptoCobro?->descripcion
                ?? $movimiento?->descripcion
                ?? 'Concepto de pago',

            'tipo_concepto' => $tipoConcepto,

            'es_obligatorio' => $esObligatorio,

            'tipo_concepto_texto' => $esObligatorio
                ? 'Obligatorio'
                : 'No obligatorio',

            /*
            * Solo muestra mes cuando existe.
            * Matrícula y otros conceptos sin mes quedan vacíos.
            */
            'mes' => $recibo->mes ?: '',

            'forma_pago' => $formaPago?->formaPago?->nombre
                ?? 'Sin definir',

            'forma_pago_id' => $formaPago?->forma_pago_id,

            'numero_referencia' => $formaPago?->numero_referencia,

            'fecha_consignacion' => $formaPago?->fecha_consignacion
                ?->format('d/m/Y'),

            'valor_pagado' => (float) $recibo->valor_recibido,
            'valor_aplicado' => (float) $recibo->valor_aplicado,
            'descuento' => (float) $recibo->descuento,
            'saldo_favor_generado' => (float) $recibo->saldo_favor_generado,

            'recibido_de' => $recibo->recibido_de,

            'recibido_por' => $recibo->recibidoPor?->name
                ?? $recibo->recibidoPor?->nombre
                ?? 'Usuario no disponible',

            'detalle' => $recibo->detalle,

            'estado' => $recibo->estado,

            'estado_texto' => match ($recibo->estado) {
                ReciboPago::ESTADO_CONFIRMADO => 'Confirmado',
                ReciboPago::ESTADO_ANULADO => 'Anulado',
                default => ucfirst((string) $recibo->estado),
            },
        ];
    }

    private function construirResumen(Collection $recibos): array
    {
        /*
         * Los recibos anulados permanecen visibles en auditoría,
         * pero no cuentan como recaudo válido.
         */
        $confirmados = $recibos->where(
            'estado',
            ReciboPago::ESTADO_CONFIRMADO
        );

        $pagos = round(
            (float) $confirmados->sum(
                fn (ReciboPago $recibo) =>
                    (float) $recibo->valor_recibido
            ),
            2
        );

        $descuentos = round(
            (float) $confirmados->sum(
                fn (ReciboPago $recibo) =>
                    (float) $recibo->descuento
            ),
            2
        );

        return [
            'pagos' => $pagos,
            'descuentos' => $descuentos,

            /*
             * En el diseño actual "Total pagado" representa dinero
             * realmente recibido. El descuento se informa aparte.
             */
            'total_pagado' => $pagos,

            'cantidad_filas' => $recibos->count(),
        ];
    }
}