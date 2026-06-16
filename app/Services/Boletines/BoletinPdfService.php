<?php

namespace App\Services\Boletines;

use App\Models\BoletinGenerado;



use App\Services\Boletines\Pdf\PdfCanvas;
use App\Services\Boletines\Pdf\PdfPainter;
use App\Services\Boletines\Pdf\PdfCalibrationPainter;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class BoletinPdfService
{
    public function __construct(
        protected BoletinTemplateService $templateService,
        protected BoletinPaginatorService $paginatorService,
        protected PdfPainter $painter,
    ) {
    }

    public function generar(array $data): string
    {
        $canvas = new PdfCanvas();

        $paginas = $this->paginatorService->paginar($data);

        foreach ($paginas as $index => $pagina) {
            $templatePath = ($pagina['es_final'] ?? false)
                ? $this->templateService->hojaFinal()
                : $this->templateService->base();

            $canvas->agregarPaginaConPlantilla($templatePath);

            $pdf = $canvas->pdf();

            $this->painter->pintarEncabezado($pdf, $data);
            $this->painter->pintarTablaAcademica($pdf, $pagina['asignaturas'] ?? [], $data);
            

            if ($pagina['es_final'] ?? false) {
                $this->painter->pintarObservaciones($pdf, $data);
            }

            if ($index === 0 && ! ($pagina['es_final'] ?? false)) {
                $this->painter->pintarConvenciones($pdf, $data);
            }

          
        }

        $rutaRelativa = $this->guardarPdf($canvas, $data);

        $this->actualizarBoletinGenerado($data, $rutaRelativa);

        return $rutaRelativa;
    }

    protected function guardarPdf(PdfCanvas $canvas, array $data): string
    {
        $curso = $this->limpiarNombreArchivo($data['curso']['codigo'] ?? 'curso');

        $estudiante = $this->limpiarNombreArchivo(
            $data['estudiante']['nombre'] ?? 'estudiante'
        );

        $periodo = $this->limpiarNombreArchivo(
            mb_strtolower($data['periodo']['academico_corto'] ?? $data['periodo']['academico'] ?? 'periodo', 'UTF-8')
        );

        $anio = $this->limpiarNombreArchivo(
            $data['periodo']['lectivo'] ?? now()->format('Y')
        );

        $nombreArchivo = "{$curso}-{$estudiante}-{$periodo}{$anio}.pdf";

        $rutaRelativa = 'boletines/' . now()->format('Y') . '/' . $nombreArchivo;

        Storage::disk('public')->makeDirectory(dirname($rutaRelativa));

        $rutaAbsoluta = Storage::disk('public')->path($rutaRelativa);

        $canvas->guardar($rutaAbsoluta);

        return $rutaRelativa;
    }


    protected function limpiarNombreArchivo(string $texto): string
    {
        $texto = trim($texto);

        $texto = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'],
            ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'],
            $texto
        );

        $texto = preg_replace('/[^A-Za-z0-9]+/', '', $texto);

        return $texto ?: 'archivo';
    }

    protected function actualizarBoletinGenerado(array $data, string $rutaRelativa): void
    {
        $studentId = $data['estudiante']['id'] ?? null;
        $periodoAcademicoId = $data['periodo']['academico_id'] ?? null;

        if (! $studentId || ! $periodoAcademicoId) {
            return;
        }

        BoletinGenerado::query()
            ->where('student_id', $studentId)
            ->where('periodo_academico_id', $periodoAcademicoId)
            ->latest('id')
            ->first()
            ?->update([
                'estado' => 'pdf_generado',
                'generated_by' => Auth::id(),
                'generado_en' => now(),
                'pdf_path' => $rutaRelativa,
            ]);
    }

    public function generarCalibracion(bool $usarHojaFinal = true): string
    {
        $canvas = new PdfCanvas();

        $templatePath = $usarHojaFinal
            ? $this->templateService->hojaFinal()
            : $this->templateService->base();

        $canvas->agregarPaginaConPlantilla($templatePath);

        app(PdfCalibrationPainter::class)->pintar($canvas->pdf());

        $nombreArchivo = $usarHojaFinal
            ? 'calibracion-boletin-hojafinal.pdf'
            : 'calibracion-boletin-hojabase.pdf';

        $rutaRelativa = 'boletines/calibracion/' . $nombreArchivo;

        Storage::disk('public')->makeDirectory(dirname($rutaRelativa));

        $rutaAbsoluta = Storage::disk('public')->path($rutaRelativa);

        $canvas->guardar($rutaAbsoluta);

        return $rutaRelativa;
    }

    
}