<?php

namespace App\Services\Financiero\Pagos\Pdf;

use setasign\Fpdi\Fpdi;

class ExtractoPdfPainter
{
    private const ANCHO_PAGINA = 215.9;
    private const ALTO_PAGINA = 279.4;

    private const MARGEN_IZQUIERDO = 12.0;
    private const MARGEN_DERECHO = 12.0;
    private const MARGEN_SUPERIOR = 10.0;
    private const MARGEN_INFERIOR = 12.0;

    private const ANCHO_UTIL = self::ANCHO_PAGINA
        - self::MARGEN_IZQUIERDO
        - self::MARGEN_DERECHO;

    public function pintar(
        Fpdi $pdf,
        array $data,
    ): void {
        $obligaciones = collect(
            $data['obligaciones'] ?? []
        );

        $paginas = $obligaciones
            ->chunk(14)
            ->values();

        if ($paginas->isEmpty()) {
            $paginas = collect([
                collect(),
            ]);
        }

        foreach ($paginas as $indicePagina => $obligacionesPagina) {
            if ($indicePagina > 0) {
                $pdf->AddPage(
                    'P',
                    'Letter'
                );
            }

            $esUltimaPagina =
                $indicePagina === $paginas->count() - 1;

            $dataPagina = [
                ...$data,
                'obligaciones' =>
                    $obligacionesPagina
                        ->values()
                        ->toArray(),
            ];

            $this->pintarMarcoGeneral($pdf);

            $this->pintarEncabezado(
                pdf: $pdf,
                data: $dataPagina,
            );

            $this->pintarDatosEstudiante(
                pdf: $pdf,
                data: $dataPagina,
            );

            $yFinalTabla = $this->pintarTabla(
                pdf: $pdf,
                data: $dataPagina,
            );

            if ($esUltimaPagina) {
                $this->pintarTotales(
                    pdf: $pdf,
                    data: $data,
                    yInicial: $yFinalTabla,
                );

                $this->pintarPie(
                    pdf: $pdf,
                    data: $data,
                );
            } else {
                $this->pintarIndicadorContinuacion(
                    pdf: $pdf,
                    paginaActual: $indicePagina + 1,
                    totalPaginas: $paginas->count(),
                );
            }
        }
    }

    private function pintarMarcoGeneral(
        Fpdi $pdf
    ): void {
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.25);

        $pdf->Rect(
            self::MARGEN_IZQUIERDO,
            self::MARGEN_SUPERIOR,
            self::ANCHO_UTIL,
            self::ALTO_PAGINA
                - self::MARGEN_SUPERIOR
                - self::MARGEN_INFERIOR
        );
    }

    private function pintarEncabezado(
        Fpdi $pdf,
        array $data,
    ): void {
        $x = self::MARGEN_IZQUIERDO;
        $y = self::MARGEN_SUPERIOR;
        $ancho = self::ANCHO_UTIL;
        $alto = 26.0;

        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(203, 213, 225);

        $pdf->Rect(
            $x,
            $y,
            $ancho,
            $alto,
            'DF'
        );

        $logoRelativo = trim(
            (string) ($data['institucion']['logo'] ?? '')
        );

        $logoAbsoluto = $logoRelativo !== ''
            ? storage_path('app/public/' . $logoRelativo)
            : null;

        $hayLogo = $logoAbsoluto
            && is_file($logoAbsoluto);

        $logoX = $x + 4;
        $logoY = $y + 3;
        $logoAncho = 18.0;
        $logoAlto = 18.0;

        if ($hayLogo) {
            $pdf->Image(
                $logoAbsoluto,
                $logoX,
                $logoY,
                $logoAncho,
                $logoAlto
            );
        }

        $textoX = $hayLogo
            ? $x + 26
            : $x + 4;

        $textoAncho = 112.0;

        $nombreInstitucion = trim(
            (string) (
                $data['institucion']['nombre']
                ?? 'Institución educativa'
            )
        );

        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetXY($textoX, $y + 3);

        $pdf->Cell(
            $textoAncho,
            5,
            $this->textoPdf($nombreInstitucion),
            0,
            1,
            'L'
        );

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(71, 85, 105);

        $lineas = array_filter([
            filled($data['institucion']['nit'] ?? null)
                ? 'NIT ' . $data['institucion']['nit']
                : null,

            $data['institucion']['direccion'] ?? null,

            filled($data['institucion']['telefono'] ?? null)
                ? 'Tel. ' . $data['institucion']['telefono']
                : null,

            $data['institucion']['email'] ?? null,
        ]);

        $yTexto = $y + 9;

        foreach ($lineas as $linea) {
            $pdf->SetXY($textoX, $yTexto);

            $pdf->Cell(
                $textoAncho,
                3.2,
                $this->textoPdf((string) $linea),
                0,
                1,
                'L'
            );

            $yTexto += 3.2;
        }

        $anchoDocumento = 56.0;
        $xDocumento = $x + $ancho - $anchoDocumento;

        $pdf->Line(
            $xDocumento,
            $y,
            $xDocumento,
            $y + $alto
        );

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetXY(
            $xDocumento + 3,
            $y + 5
        );

        $pdf->MultiCell(
            $anchoDocumento - 6,
            5,
            $this->textoPdf('EXTRACTO DE CARTERA'),
            0,
            'C'
        );

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->SetXY(
            $xDocumento + 3,
            $y + 17
        );

        $pdf->Cell(
            $anchoDocumento - 6,
            4,
            $this->textoPdf(
                'Fecha de corte: '
                . ($data['fecha_corte'] ?? '—')
            ),
            0,
            0,
            'C'
        );
    }

    private function pintarDatosEstudiante(
        Fpdi $pdf,
        array $data,
    ): void {
        $x = self::MARGEN_IZQUIERDO;
        $y = 36.0;
        $alto = 14.0;

        $columnas = [
            [
                'ancho' => 83.0,
                'etiqueta' => 'ESTUDIANTE',
                'valor' =>
                    $data['estudiante']['nombre']
                    ?? '—',
            ],
            [
                'ancho' => 34.0,
                'etiqueta' => 'CÓDIGO',
                'valor' =>
                    $data['estudiante']['codigo']
                    ?? '—',
            ],
            [
                'ancho' => 42.0,
                'etiqueta' => 'DOCUMENTO',
                'valor' =>
                    $data['estudiante']['documento']
                    ?? '—',
            ],
            [
                'ancho' => self::ANCHO_UTIL - 159.0,
                'etiqueta' => 'CURSO',
                'valor' =>
                    $this->cursoCompleto($data),
            ],
        ];

        $xActual = $x;

        foreach ($columnas as $columna) {
            $this->campoCaja(
                pdf: $pdf,
                x: $xActual,
                y: $y,
                ancho: (float) $columna['ancho'],
                alto: $alto,
                etiqueta: $columna['etiqueta'],
                valor: $columna['valor'],
            );

            $xActual += (float) $columna['ancho'];
        }
    }

    private function pintarTabla(
        Fpdi $pdf,
        array $data,
    ): float {
        $x = self::MARGEN_IZQUIERDO;
        $y = 54.0;

        $columnas = [
            'concepto' => [
                'x' => $x,
                'ancho' => 74.0,
                'titulo' => 'CONCEPTO',
                'alineacion' => 'L',
            ],

            'mes' => [
                'x' => $x + 74.0,
                'ancho' => 28.0,
                'titulo' => 'MES',
                'alineacion' => 'L',
            ],

            'valor' => [
                'x' => $x + 102.0,
                'ancho' => 30.0,
                'titulo' => 'VALOR',
                'alineacion' => 'R',
            ],

            'pagado' => [
                'x' => $x + 132.0,
                'ancho' => 30.0,
                'titulo' => 'ABONO',
                'alineacion' => 'R',
            ],

            'pendiente' => [
                'x' => $x + 162.0,
                'ancho' => self::ANCHO_UTIL - 162.0,
                'titulo' => 'PENDIENTE',
                'alineacion' => 'R',
            ],
        ];

        $altoEncabezado = 8.0;

        $pdf->SetFillColor(241, 245, 249);
        $pdf->SetDrawColor(203, 213, 225);

        $pdf->Rect(
            $x,
            $y,
            self::ANCHO_UTIL,
            $altoEncabezado,
            'DF'
        );

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetTextColor(51, 65, 85);

        foreach ($columnas as $columna) {
            $pdf->SetXY(
                $columna['x'] + 1,
                $y + 2
            );

            $pdf->Cell(
                $columna['ancho'] - 2,
                4,
                $this->textoPdf($columna['titulo']),
                0,
                0,
                $columna['alineacion']
            );
        }

        $y += $altoEncabezado;

        $obligaciones = $data['obligaciones'] ?? [];

        if ($obligaciones === []) {
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->SetXY($x, $y + 5);

            $pdf->Cell(
                self::ANCHO_UTIL,
                6,
                $this->textoPdf(
                    'El estudiante no tiene obligaciones pendientes.'
                ),
                0,
                0,
                'C'
            );

            return $y + 16;
        }

        foreach ($obligaciones as $obligacion) {
            $concepto = trim(
                (string) (
                    $obligacion['concepto']
                    ?? 'Concepto sin nombre'
                )
            );

            $longitudConcepto = mb_strlen(
                $concepto,
                'UTF-8'
            );

            $altoFila = match (true) {
                $longitudConcepto > 75 => 13.0,
                $longitudConcepto > 45 => 11.0,
                default => 9.0,
            };

            $pdf->SetDrawColor(226, 232, 240);

            $pdf->Line(
                $x,
                $y + $altoFila,
                $x + self::ANCHO_UTIL,
                $y + $altoFila
            );

            foreach ([
                $x + 74.0,
                $x + 102.0,
                $x + 132.0,
                $x + 162.0,
            ] as $separadorX) {
                $pdf->Line(
                    $separadorX,
                    $y,
                    $separadorX,
                    $y + $altoFila
                );
            }

            

            $longitudConcepto = mb_strlen(
                $concepto,
                'UTF-8'
            );

            $tamanoConcepto = match (true) {
                $longitudConcepto > 75 => 5.2,
                $longitudConcepto > 60 => 5.7,
                $longitudConcepto > 45 => 6.2,
                $longitudConcepto > 32 => 6.7,
                default => 7.2,
            };

            $this->celdaTexto(
                pdf: $pdf,
                x: $columnas['concepto']['x'],
                y: $y,
                ancho: $columnas['concepto']['ancho'],
                alto: $altoFila,
                texto: $concepto,
                negrita: true,
                tamano: $tamanoConcepto,
            );

            $this->celdaTexto(
                pdf: $pdf,
                x: $columnas['mes']['x'],
                y: $y,
                ancho: $columnas['mes']['ancho'],
                alto: $altoFila,
                texto: $obligacion['mes'] ?? '—',
            );

            $this->celdaMoneda(
                pdf: $pdf,
                x: $columnas['valor']['x'],
                y: $y,
                ancho: $columnas['valor']['ancho'],
                alto: $altoFila,
                valor: (float) ($obligacion['valor'] ?? 0),
            );

            $this->celdaMoneda(
                pdf: $pdf,
                x: $columnas['pagado']['x'],
                y: $y,
                ancho: $columnas['pagado']['ancho'],
                alto: $altoFila,
                valor: (float) ($obligacion['pagado'] ?? 0),
            );

            $this->celdaMoneda(
                pdf: $pdf,
                x: $columnas['pendiente']['x'],
                y: $y,
                ancho: $columnas['pendiente']['ancho'],
                alto: $altoFila,
                valor: (float) ($obligacion['pendiente'] ?? 0),
                negrita: true,
            );

            $y += $altoFila;
        }

        return $y;
    }

    private function pintarTotales(
        Fpdi $pdf,
        array $data,
        float $yInicial,
    ): void {
        $x = self::MARGEN_IZQUIERDO;
        $y = $yInicial + 8;

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->SetXY($x, $y);

        $pdf->Cell(
            90,
            5,
            $this->textoPdf(
                'Obligaciones pendientes: '
                . (
                    $data['cantidad_obligaciones']
                    ?? 0
                )
            ),
            0,
            1,
            'L'
        );

        $saldoFavor = (float) (
            $data['saldo_favor']
            ?? 0
        );

        if ($saldoFavor > 0) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetTextColor(0, 140, 69);
            $pdf->SetXY($x, $y + 7);

            $pdf->Cell(
                90,
                5,
                $this->textoPdf('Saldo a favor'),
                0,
                0,
                'L'
            );

            $pdf->SetXY($x + 90, $y + 7);

            $pdf->Cell(
                self::ANCHO_UTIL - 90,
                5,
                '$ ' . number_format(
                    $saldoFavor,
                    0,
                    ',',
                    '.'
                ),
                0,
                0,
                'R'
            );

            $y += 8;
        }

        $yTotal = $y + 9;

        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(203, 213, 225);

        $pdf->Rect(
            $x,
            $yTotal,
            self::ANCHO_UTIL,
            18,
            'DF'
        );

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->SetXY(
            $x + 4,
            $yTotal + 4
        );

        $pdf->Cell(
            90,
            5,
            $this->textoPdf('TOTAL PENDIENTE'),
            0,
            0,
            'L'
        );

        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetTextColor(0, 140, 69);
        $pdf->SetXY(
            $x + 95,
            $yTotal + 2
        );

        $pdf->Cell(
            self::ANCHO_UTIL - 100,
            9,
            '$ ' . number_format(
                $data['total_pendiente'] ?? 0,
                0,
                ',',
                '.'
            ),
            0,
            0,
            'R'
        );
    }

    private function pintarPie(
        Fpdi $pdf,
        array $data,
    ): void {
        $x = self::MARGEN_IZQUIERDO;
        $y = 244.0;

        $pdf->SetDrawColor(226, 232, 240);
        $pdf->Line(
            $x,
            $y,
            $x + self::ANCHO_UTIL,
            $y
        );

        $texto = 'Este documento refleja únicamente las obligaciones '
            . 'pendientes registradas a la fecha de generación. '
            . 'Los valores pueden variar posteriormente por nuevos cargos, '
            . 'penalizaciones, intereses, pagos, descuentos u otros '
            . 'movimientos de cartera.';

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->SetXY($x, $y + 4);

        $pdf->MultiCell(
            self::ANCHO_UTIL,
            3.7,
            $this->textoPdf($texto),
            0,
            'L'
        );

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->SetXY($x, $y + 16);

        $pdf->Cell(
            95,
            4,
            $this->textoPdf(
                'Generado por: '
                . ($data['generado_por'] ?? '—')
            ),
            0,
            0,
            'L'
        );

        $pdf->Cell(
            self::ANCHO_UTIL - 95,
            4,
            $this->textoPdf(
                'Fecha: '
                . ($data['generado_en'] ?? '—')
            ),
            0,
            0,
            'R'
        );
    }

    private function campoCaja(
        Fpdi $pdf,
        float $x,
        float $y,
        float $ancho,
        float $alto,
        string $etiqueta,
        mixed $valor,
    ): void {
        $pdf->SetDrawColor(203, 213, 225);

        $pdf->Rect(
            $x,
            $y,
            $ancho,
            $alto
        );

        $pdf->SetFont('Arial', '', 5.8);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->SetXY(
            $x + 1.5,
            $y + 1.5
        );

        $pdf->Cell(
            $ancho - 3,
            2.5,
            $this->textoPdf($etiqueta),
            0,
            1,
            'L'
        );

        $textoValor = trim((string) $valor);

        if ($textoValor === '') {
            $textoValor = '—';
        }

        $longitud = mb_strlen(
            $textoValor,
            'UTF-8'
        );

        $tamano = match (true) {
            $longitud > 55 => 5.3,
            $longitud > 45 => 5.8,
            $longitud > 35 => 6.3,
            $longitud > 28 => 6.8,
            default => 7.4,
        };

        $pdf->SetFont('Arial', 'B', $tamano);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetXY(
            $x + 1.5,
            $y + 5
        );

        $pdf->MultiCell(
            $ancho - 3,
            3.2,
            $this->textoPdf($textoValor),
            0,
            'L'
        );
    }

    private function celdaTexto(
        Fpdi $pdf,
        float $x,
        float $y,
        float $ancho,
        float $alto,
        string $texto,
        string $alineacion = 'L',
        bool $negrita = false,
        float $tamano = 7.0,
    ): void {
        $pdf->SetFont(
            'Arial',
            $negrita ? 'B' : '',
            $tamano
        );

        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetXY(
            $x + 1.5,
            $y + 2
        );

        $pdf->MultiCell(
            $ancho - 3,
            3.2,
            $this->textoPdf($texto),
            0,
            $alineacion
        );
    }

    private function celdaMoneda(
        Fpdi $pdf,
        float $x,
        float $y,
        float $ancho,
        float $alto,
        float $valor,
        bool $negrita = false,
    ): void {
        $pdf->SetFont(
            'Arial',
            $negrita ? 'B' : '',
            $negrita ? 7.8 : 7.2
        );

        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetXY(
            $x + 1,
            $y + 2
        );

        $pdf->Cell(
            $ancho - 2,
            4,
            '$ ' . number_format(
                $valor,
                0,
                ',',
                '.'
            ),
            0,
            0,
            'R'
        );
    }

    private function cursoCompleto(
        array $data
    ): string {
        $grado = trim(
            (string) (
                $data['estudiante']['grado']
                ?? ''
            )
        );

        $curso = trim(
            (string) (
                $data['estudiante']['curso']
                ?? ''
            )
        );

        $texto = trim(
            implode(
                ' - ',
                array_filter([
                    $grado,
                    $curso,
                ])
            )
        );

        return $texto !== ''
            ? $texto
            : '—';
    }

    private function textoPdf(
        string $texto
    ): string {
        return iconv(
            'UTF-8',
            'windows-1252//TRANSLIT',
            $texto
        ) ?: $texto;
    }



    private function pintarIndicadorContinuacion(
        Fpdi $pdf,
        int $paginaActual,
        int $totalPaginas,
    ): void {
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetTextColor(100, 116, 139);

        $pdf->SetXY(
            self::MARGEN_IZQUIERDO,
            258
        );

        $pdf->Cell(
            self::ANCHO_UTIL,
            4,
            $this->textoPdf(
                sprintf(
                    'Continúa en la siguiente página · Página %d de %d',
                    $paginaActual,
                    $totalPaginas
                )
            ),
            0,
            0,
            'R'
        );
    }
}