<?php

namespace App\Services\Boletines\Pdf;

use setasign\Fpdi\Fpdi;

class PdfCalibrationPainter
{
    public function pintar(Fpdi $pdf): void
    {
        $this->pintarCuadriculaMilimetrica($pdf);
        $this->pintarEjesPrincipales($pdf);
        $this->pintarRotulo($pdf);
    }

    private function pintarCuadriculaMilimetrica(Fpdi $pdf): void
    {
        $pdf->SetDrawColor(255, 205, 205);
        $pdf->SetLineWidth(0.05);

        for ($x = 0; $x <= PdfCoordinates::PAGE_WIDTH; $x += 1) {
            $pdf->Line($x, 0, $x, PdfCoordinates::PAGE_HEIGHT);
        }

        for ($y = 0; $y <= PdfCoordinates::PAGE_HEIGHT; $y += 1) {
            $pdf->Line(0, $y, PdfCoordinates::PAGE_WIDTH, $y);
        }
    }

    private function pintarEjesPrincipales(Fpdi $pdf): void
    {
        $pdf->SetFont('Arial', '', 4.5);
        $pdf->SetTextColor(180, 0, 0);
        $pdf->SetDrawColor(210, 0, 0);
        $pdf->SetLineWidth(0.15);

        for ($x = 0; $x <= PdfCoordinates::PAGE_WIDTH; $x += 10) {
            $pdf->Line($x, 0, $x, PdfCoordinates::PAGE_HEIGHT);

            $pdf->SetXY($x + 0.7, 2);
            $pdf->Cell(8, 2, (string) $x);

            $pdf->SetXY($x + 0.7, PdfCoordinates::PAGE_HEIGHT - 5);
            $pdf->Cell(8, 2, (string) $x);
        }

        for ($y = 0; $y <= PdfCoordinates::PAGE_HEIGHT; $y += 10) {
            $pdf->Line(0, $y, PdfCoordinates::PAGE_WIDTH, $y);

            $pdf->SetXY(1, $y + 0.7);
            $pdf->Cell(8, 2, (string) $y);

            $pdf->SetXY(PdfCoordinates::PAGE_WIDTH - 10, $y + 0.7);
            $pdf->Cell(8, 2, (string) $y);
        }

        $this->pintarMarcasCadaCinco($pdf);
    }

    private function pintarMarcasCadaCinco(Fpdi $pdf): void
    {
        $pdf->SetFont('Arial', '', 3.5);
        $pdf->SetTextColor(150, 0, 0);
        $pdf->SetDrawColor(230, 70, 70);
        $pdf->SetLineWidth(0.08);

        for ($x = 5; $x <= PdfCoordinates::PAGE_WIDTH; $x += 10) {
            $pdf->Line($x, 0, $x, PdfCoordinates::PAGE_HEIGHT);

            $pdf->SetXY($x + 0.5, 5);
            $pdf->Cell(6, 2, (string) $x);
        }

        for ($y = 5; $y <= PdfCoordinates::PAGE_HEIGHT; $y += 10) {
            $pdf->Line(0, $y, PdfCoordinates::PAGE_WIDTH, $y);

            $pdf->SetXY(5, $y + 0.5);
            $pdf->Cell(6, 2, (string) $y);
        }
    }

    private function pintarRotulo(Fpdi $pdf): void
    {
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetTextColor(180, 0, 0);

        $pdf->SetXY(70, 5);
        $pdf->Cell(80, 4, 'MODO CALIBRACION PDF - Coordenadas en milimetros', 0, 0, 'C');
    }
}