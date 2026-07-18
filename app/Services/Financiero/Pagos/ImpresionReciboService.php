<?php

namespace App\Services\Financiero\Pagos;

use App\Models\ImpresionRecibo;
use App\Models\OperacionPago;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ImpresionReciboService
{
    /**
     * Registra la única impresión original del recibo.
     */
    public function registrarOriginal(
        int $operacionPagoId,
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
        int $generadoPor,
        string $medio = ImpresionRecibo::MEDIO_PDF,
        ?string $rutaPdf = null,
    ): ImpresionRecibo {
        return DB::transaction(function () use (
            $operacionPagoId,
            $studentId,
            $sedeId,
            $periodoLectivoId,
            $generadoPor,
            $medio,
            $rutaPdf,
        ) {
            $operacion = $this->obtenerOperacionBloqueada(
                operacionPagoId: $operacionPagoId,
                studentId: $studentId,
                sedeId: $sedeId,
                periodoLectivoId: $periodoLectivoId,
            );

            $originalExistente = ImpresionRecibo::query()
                ->where('operacion_pago_id', $operacion->id)
                ->where('numero_reimpresion', 0)
                ->lockForUpdate()
                ->first();

            if ($originalExistente) {
                throw ValidationException::withMessages([
                    'impresion' =>
                        'El recibo ya tiene registrada su impresión original.',
                ]);
            }

            return ImpresionRecibo::create([
                'operacion_pago_id' => $operacion->id,
                'recibo_pago_id' => null,
                'tipo' => ImpresionRecibo::TIPO_ORIGINAL,
                'numero_reimpresion' => 0,
                'medio' => $medio,
                'ruta_pdf' => $rutaPdf,
                'motivo' => null,
                'generado_por' => $generadoPor,
                'generado_en' => now(),
            ]);
        }, 3);
    }

    /**
     * Registra una nueva reimpresión R1, R2, R3...
     *
     * El motivo es opcional.
     */
    public function registrarReimpresion(
        int $operacionPagoId,
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
        int $generadoPor,
        ?string $motivo = null,
        string $medio = ImpresionRecibo::MEDIO_PDF,
        ?string $rutaPdf = null,
    ): ImpresionRecibo {
        return DB::transaction(function () use (
            $operacionPagoId,
            $studentId,
            $sedeId,
            $periodoLectivoId,
            $generadoPor,
            $motivo,
            $medio,
            $rutaPdf,
        ) {
            $operacion = $this->obtenerOperacionBloqueada(
                operacionPagoId: $operacionPagoId,
                studentId: $studentId,
                sedeId: $sedeId,
                periodoLectivoId: $periodoLectivoId,
            );

            $originalExiste = ImpresionRecibo::query()
                ->where('operacion_pago_id', $operacion->id)
                ->where('numero_reimpresion', 0)
                ->exists();

            if (! $originalExiste) {
                throw ValidationException::withMessages([
                    'impresion' =>
                        'Primero debe generarse la impresión original del recibo.',
                ]);
            }

            $ultimaImpresion = ImpresionRecibo::query()
                ->where('operacion_pago_id', $operacion->id)
                ->lockForUpdate()
                ->orderByDesc('numero_reimpresion')
                ->first();

            $numeroReimpresion = max(
                1,
                ((int) ($ultimaImpresion?->numero_reimpresion ?? 0)) + 1
            );

            return ImpresionRecibo::create([
                'operacion_pago_id' => $operacion->id,
                'recibo_pago_id' => null,
                'tipo' => ImpresionRecibo::TIPO_REIMPRESION,
                'numero_reimpresion' => $numeroReimpresion,
                'medio' => $medio,
                'ruta_pdf' => $rutaPdf,

                'motivo' => filled($motivo)
                    ? trim((string) $motivo)
                    : null,

                'generado_por' => $generadoPor,
                'generado_en' => now(),
            ]);
        }, 3);
    }

    /**
     * Devuelve el estado y resumen de impresión del recibo.
     */
    public function obtenerEstado(int $operacionPagoId): array
    {
        $impresiones = ImpresionRecibo::query()
            ->with('generadoPor')
            ->where('operacion_pago_id', $operacionPagoId)
            ->orderBy('numero_reimpresion')
            ->get();

        $original = $impresiones->firstWhere(
            'numero_reimpresion',
            0
        );

        $reimpresiones = $impresiones
            ->where('tipo', ImpresionRecibo::TIPO_REIMPRESION)
            ->values();

        $ultimaReimpresion = $reimpresiones->last();

        return [
            'ha_sido_impreso' => (bool) $original,

            'original' => $original
                ? $this->mapearImpresion($original)
                : null,

            'cantidad_reimpresiones' => $reimpresiones->count(),

            'ultima_reimpresion' => $ultimaReimpresion
                ? $this->mapearImpresion($ultimaReimpresion)
                : null,
        ];
    }

    /**
     * Identificador visual:
     * 257 para original
     * 257-R1, 257-R2... para reimpresiones.
     */
    public function identificadorVisual(
        int $numeroRecibo,
        ImpresionRecibo $impresion,
    ): string {
        if ($impresion->numero_reimpresion <= 0) {
            return (string) $numeroRecibo;
        }

        return sprintf(
            '%d-R%d',
            $numeroRecibo,
            $impresion->numero_reimpresion
        );
    }

    private function obtenerOperacionBloqueada(
        int $operacionPagoId,
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
    ): OperacionPago {
        $operacion = OperacionPago::query()
            ->whereKey($operacionPagoId)
            ->where('student_id', $studentId)
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->lockForUpdate()
            ->first();

        if (! $operacion) {
            throw ValidationException::withMessages([
                'impresion' =>
                    'No se encontró el recibo solicitado.',
            ]);
        }

        return $operacion;
    }

    private function mapearImpresion(
        ImpresionRecibo $impresion
    ): array {
        return [
            'id' => (int) $impresion->id,
            'tipo' => $impresion->tipo,
            'numero_reimpresion' =>
                (int) $impresion->numero_reimpresion,

            'usuario' => $impresion->generadoPor?->name
                ?? $impresion->generadoPor?->nombre
                ?? 'Usuario no disponible',

            'fecha' => $impresion->generado_en?->format(
                'd/m/Y h:i a'
            ),

            'motivo' => $impresion->motivo,
            'medio' => $impresion->medio,
            'ruta_pdf' => $impresion->ruta_pdf,
        ];
    }
}