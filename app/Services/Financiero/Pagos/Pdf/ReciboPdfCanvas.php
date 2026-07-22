<?php

namespace App\Services\Financiero\Pagos\Pdf;

use App\Services\Financiero\Pagos\Pdf\Templates\ReciboTemplateService;
use setasign\Fpdi\Fpdi;

class ReciboPdfCanvas
{
    protected Fpdi $pdf;

    public function __construct()
    {
        $this->pdf = new Fpdi(
            'L',
            'mm',
            [139.7, 215.9]
        );

        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->SetMargins(0, 0, 0);
    }

    public function agregarPagina(): void
    {
        $this->pdf->AddPage(
            'L',
            [139.7, 215.9]
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