<?php

namespace App\Services\Financiero\Pagos\Pdf;

use setasign\Fpdi\Fpdi;

class ExtractoPdfCanvas
{
    protected Fpdi $pdf;

    public function __construct()
    {
        $this->pdf = new Fpdi(
            'P',
            'mm',
            'Letter'
        );

        $this->pdf->SetAutoPageBreak(
            true,
            15
        );
        $this->pdf->SetMargins(0, 0, 0);
    }

    public function agregarPagina(): void
    {
        $this->pdf->AddPage(
            'P',
            'Letter'
        );
    }

    public function pdf(): Fpdi
    {
        return $this->pdf;
    }

    public function guardar(string $rutaAbsoluta): void
    {
        $this->pdf->Output('F', $rutaAbsoluta);
    }
}