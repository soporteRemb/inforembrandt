<?php

namespace App\Services\Boletines\Pdf;

use setasign\Fpdi\Fpdi;

class PdfCanvas
{
    protected Fpdi $pdf;

    public function __construct()
    {
        $this->pdf = new Fpdi('P', 'mm', 'Letter');
        $this->pdf->SetAutoPageBreak(false);
    }

    public function pdf(): Fpdi
    {
        return $this->pdf;
    }

    public function agregarPaginaConPlantilla(string $templatePath): void
    {
        $this->pdf->AddPage();

        $this->pdf->setSourceFile($templatePath);

        $templateId = $this->pdf->importPage(1);

        $this->pdf->useTemplate(
            $templateId,
            0,
            0,
            PdfCoordinates::PAGE_WIDTH,
            PdfCoordinates::PAGE_HEIGHT
        );
    }

    public function guardar(string $rutaAbsoluta): void
    {
        $this->pdf->Output('F', $rutaAbsoluta);
    }
}