<?php

namespace App\Services\Financiero\Pagos\Pdf;

use App\Services\Financiero\Pagos\Extracto\ExtractoDataService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExtractoPdfService
{
    public function __construct(
        protected ExtractoDataService $dataService,
        protected ExtractoPdfPainter $painter,
    ) {
    }

    public function generar(
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
        int $generadoPor,
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Obtener los datos financieros
        |--------------------------------------------------------------------------
        */
        $data = $this->dataService->consultar(
            studentId: $studentId,
            sedeId: $sedeId,
            periodoLectivoId: $periodoLectivoId,
            generadoPor: $generadoPor,
        );

        /*
        |--------------------------------------------------------------------------
        | Crear y pintar el PDF
        |--------------------------------------------------------------------------
        */
        $canvas = new ExtractoPdfCanvas();

        $canvas->agregarPagina();

        $this->painter->pintar(
            pdf: $canvas->pdf(),
            data: $data,
        );

        /*
        |--------------------------------------------------------------------------
        | Definir ubicación del archivo
        |--------------------------------------------------------------------------
        */
        $rutaRelativa = $this->rutaArchivo(
            data: $data,
        );

        Storage::disk('public')->makeDirectory(
            dirname($rutaRelativa)
        );

        $rutaAbsoluta = Storage::disk('public')->path(
            $rutaRelativa
        );

        $canvas->guardar(
            $rutaAbsoluta
        );

        return [
            'ruta_pdf' => $rutaRelativa,

            'url' => Storage::disk('public')->url(
                $rutaRelativa
            ),

            'data' => $data,
        ];
    }

    private function rutaArchivo(
        array $data
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

        $fechaArchivo = now()->format(
            'Ymd-His'
        );

        $nombreArchivo = sprintf(
            'extracto-%s-%d-%s.pdf',
            $nombreEstudiante,
            $studentId,
            $fechaArchivo
        );

        return sprintf(
            'extractos/%s/%s',
            now()->format('Y'),
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

        $texto = trim(
            (string) $texto,
            '-'
        );

        return $texto !== ''
            ? Str::lower($texto)
            : 'archivo';
    }
}