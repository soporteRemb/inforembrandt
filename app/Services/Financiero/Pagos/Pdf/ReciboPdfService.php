<?php

namespace App\Services\Financiero\Pagos\Pdf;

use App\Models\ImpresionRecibo;
use App\Services\Financiero\Pagos\DetalleReciboService;
use App\Services\Financiero\Pagos\ImpresionReciboService;


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReciboPdfService
{
    public function __construct(
        protected DetalleReciboService $detalleReciboService,
        protected ImpresionReciboService $impresionReciboService,
        protected ReciboPdfPainter $painter,
        
    ) {
    }

    /**
     * Genera la única impresión original.
     */
    public function generarOriginal(
        int $operacionPagoId,
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
        int $generadoPor,
    ): array {
        $data = $this->detalleReciboService->consultar(
            operacionPagoId: $operacionPagoId,
            studentId: $studentId,
            sedeId: $sedeId,
            periodoLectivoId: $periodoLectivoId,
        );

        if (
            $data['impresion']['ha_sido_impreso']
            ?? false
        ) {
            throw ValidationException::withMessages([
                'impresion' =>
                    'Este recibo ya tiene una impresión original registrada.',
            ]);
        }

        $numeroRecibo = (int) ($data['numero_recibo'] ?? 0);

        if ($numeroRecibo <= 0) {
            throw ValidationException::withMessages([
                'impresion' =>
                    'No se encontró un número de recibo válido.',
            ]);
        }

        $identificador = (string) $numeroRecibo;

        $rutaRelativa = $this->generarArchivo(
            data: $data,
            identificador: $identificador,
            esReimpresion: false,
        );

        try {
            $impresion = $this->impresionReciboService->registrarOriginal(
                operacionPagoId: $operacionPagoId,
                studentId: $studentId,
                sedeId: $sedeId,
                periodoLectivoId: $periodoLectivoId,
                generadoPor: $generadoPor,
                medio: ImpresionRecibo::MEDIO_PDF,
                rutaPdf: $rutaRelativa,
            );
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($rutaRelativa);

            throw $exception;
        }

        return [
            'impresion' => $impresion,
            'identificador' => $identificador,
            'ruta_pdf' => $rutaRelativa,
            'url' => Storage::disk('public')->url($rutaRelativa),
        ];
    }

    /**
     * Genera R1, R2, R3...
     *
     * El motivo es opcional.
     */
    public function generarReimpresion(
        int $operacionPagoId,
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
        int $generadoPor,
        ?string $motivo = null,
    ): array {
        $data = $this->detalleReciboService->consultar(
            operacionPagoId: $operacionPagoId,
            studentId: $studentId,
            sedeId: $sedeId,
            periodoLectivoId: $periodoLectivoId,
        );

        if (
            ! ($data['impresion']['ha_sido_impreso'] ?? false)
        ) {
            throw ValidationException::withMessages([
                'impresion' =>
                    'Primero debe generarse la impresión original.',
            ]);
        }

        $numeroRecibo = (int) ($data['numero_recibo'] ?? 0);

        if ($numeroRecibo <= 0) {
            throw ValidationException::withMessages([
                'impresion' =>
                    'No se encontró un número de recibo válido.',
            ]);
        }

        /*
         * Vista previa del próximo número. El servicio de auditoría
         * confirma después el consecutivo real bajo bloqueo.
         */
        $siguienteNumero = (
            (int) (
                $data['impresion']['cantidad_reimpresiones']
                ?? 0
            )
        ) + 1;

        $identificador = sprintf(
            '%d-R%d',
            $numeroRecibo,
            $siguienteNumero
        );

        $rutaRelativa = $this->generarArchivo(
            data: $data,
            identificador: $identificador,
            esReimpresion: true,
        );

        try {
            $impresion =
                $this->impresionReciboService->registrarReimpresion(
                    operacionPagoId: $operacionPagoId,
                    studentId: $studentId,
                    sedeId: $sedeId,
                    periodoLectivoId: $periodoLectivoId,
                    generadoPor: $generadoPor,
                    motivo: $motivo,
                    medio: ImpresionRecibo::MEDIO_PDF,
                    rutaPdf: $rutaRelativa,
                );
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($rutaRelativa);

            throw $exception;
        }

        /*
         * Por seguridad, recalculamos el identificador usando el número
         * real registrado por el servicio transaccional.
         */
        $identificadorReal =
            $this->impresionReciboService->identificadorVisual(
                numeroRecibo: $numeroRecibo,
                impresion: $impresion,
            );

        /*
         * Si dos usuarios generaron simultáneamente y el número real
         * cambió, renombramos el archivo para mantener coherencia.
         */
        if ($identificadorReal !== $identificador) {
            $rutaNueva = $this->rutaArchivo(
                data: $data,
                identificador: $identificadorReal,
            );

            Storage::disk('public')->makeDirectory(
                dirname($rutaNueva)
            );

            Storage::disk('public')->move(
                $rutaRelativa,
                $rutaNueva
            );

            $impresion->update([
                'ruta_pdf' => $rutaNueva,
            ]);

            $rutaRelativa = $rutaNueva;
            $identificador = $identificadorReal;
        }

        return [
            'impresion' => $impresion,
            'identificador' => $identificador,
            'ruta_pdf' => $rutaRelativa,
            'url' => Storage::disk('public')->url($rutaRelativa),
        ];
    }

    private function generarArchivo(
        array $data,
        string $identificador,
        bool $esReimpresion,
    ): string {
        $canvas = new ReciboPdfCanvas();

        $canvas->agregarPagina();

        $this->painter->pintar(
            pdf: $canvas->pdf(),
            data: $data,
            identificador: $identificador,
            esReimpresion: $esReimpresion,
        );

        $rutaRelativa = $this->rutaArchivo(
            data: $data,
            identificador: $identificador,
        );

        Storage::disk('public')->makeDirectory(
            dirname($rutaRelativa)
        );

        $rutaAbsoluta = Storage::disk('public')->path(
            $rutaRelativa
        );

        $canvas->guardar($rutaAbsoluta);

        return $rutaRelativa;
    }

    private function rutaArchivo(
        array $data,
        string $identificador,
    ): string {
        $studentId = (int) (
            $data['estudiante']['id']
            ?? 0
        );

        $nombreEstudiante = $this->limpiarNombreArchivo(
            (string) (
                $data['estudiante']['nombre']
                ?? 'estudiante'
            )
        );

        $identificadorArchivo = $this->limpiarNombreArchivo(
            $identificador
        );

        $anio = (string) (
            $data['anio']
            ?? now()->format('Y')
        );

        $nombreArchivo = sprintf(
            'recibo-%s-%s-%d.pdf',
            $identificadorArchivo,
            $nombreEstudiante,
            $studentId
        );

        return sprintf(
            'recibos/%s/%s',
            $anio,
            $nombreArchivo
        );
    }

    private function limpiarNombreArchivo(
        string $texto
    ): string {
        $texto = trim($texto);

        $texto = str_replace(
            [
                'á', 'é', 'í', 'ó', 'ú',
                'Á', 'É', 'Í', 'Ó', 'Ú',
                'ñ', 'Ñ',
            ],
            [
                'a', 'e', 'i', 'o', 'u',
                'A', 'E', 'I', 'O', 'U',
                'n', 'N',
            ],
            $texto
        );

        $texto = preg_replace(
            '/[^A-Za-z0-9\-]+/',
            '-',
            $texto
        );

        $texto = trim((string) $texto, '-');

        return $texto !== ''
            ? Str::lower($texto)
            : 'archivo';
    }

   
}