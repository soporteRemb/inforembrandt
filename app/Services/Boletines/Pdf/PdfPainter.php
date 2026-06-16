<?php

namespace App\Services\Boletines\Pdf;

use setasign\Fpdi\Fpdi;

class PdfPainter
{
    public function pintarEncabezado(Fpdi $pdf, array $data): void
    {
        $estudiante = $data['estudiante'] ?? [];
        $curso = $data['curso'] ?? [];
        $periodo = $data['periodo'] ?? [];

        /******************************************************************
         * ENCABEZADO
         * Estos campos se pintan sobre los recuadros superiores del boletín.
         ******************************************************************/

        // Nombre estudiante
        $this->fuenteNegrita($pdf, 10);
        [$x, $y, $w] = PdfCoordinates::ENCABEZADO['estudiante_nombre'];
        $this->multiTexto($pdf, $x, $y, $w, 4, $estudiante['nombre'] ?? '');

        // Documento del estudiante
        $this->fuenteNormal($pdf, 9);
        [$x, $y, $w] = PdfCoordinates::ENCABEZADO['estudiante_documento'];
        $this->texto($pdf, $x, $y, $w, $estudiante['documento'] ?? '');

        // Grado + curso. Ejemplo: Octavo - 801
        $this->fuenteNegrita($pdf, 9);
        [$x, $y, $w] = PdfCoordinates::ENCABEZADO['curso_grado'];
        $this->textoCentrado($pdf, $x, $y, $w, $curso['grado_curso'] ?? '');

        // Jornada fija temporalmente
        $this->fuenteNormal($pdf, 10);
        [$x, $y, $w] = PdfCoordinates::ENCABEZADO['curso_jornada'];
        $this->textoCentrado($pdf, $x, $y, $w, 'Completa');

        // Periodo académico. Ejemplo:
        // PRIMERO
        // 2026
        $this->fuenteNegrita($pdf, 10);
        [$x, $y, $w] = PdfCoordinates::ENCABEZADO['periodo_academico'];
        $this->multiTextoCentrado(
            $pdf,
            $x,
            $y,
            $w,
            4,
            trim(($periodo['academico_corto'] ?? '') . "\n" . ($periodo['lectivo'] ?? ''))
        );

        // Director de curso: por ahora pendiente de conectar dato real
        $this->fuenteNegrita($pdf, 10);
        [$x, $y, $w] = PdfCoordinates::ENCABEZADO['director_curso'];
        $this->textoCentrado($pdf, $x, $y, $w, $curso['director_curso'] ?? '');
    }

    public function pintarTablaAcademica(Fpdi $pdf, array $asignaturas, array $data = []): void
    {
        /******************************************************************
         * COLUMNA IZQUIERDA DE ASIGNATURAS
         * Orden: viene desde Pensum Académico según el campo orden.
         ******************************************************************/

        $y = PdfCoordinates::TABLA_ACADEMICA['inicio_y'];
        $altoBloque = PdfCoordinates::TABLA_ACADEMICA['alto_bloque'];
        $desempenosPorPensum = collect($data['desempenos'] ?? [])
            ->keyBy('pensum_academico_id');

        foreach ($asignaturas as $item) {
            $nombreAsignatura = $item['nombre'] ?? $item['nombre_corto'] ?? '';
            $docente = $item['docente'] ?? '';
            $fallas = $item['fallas'] ?? 0;
            $pgc = $item['pgc'] ?? '';

            [$xAsignatura, $offsetYAsignatura, $wAsignatura] = PdfCoordinates::TABLA_ACADEMICA['asignatura'];
            [$xDocente, $offsetYDocente, $wDocente] = PdfCoordinates::TABLA_ACADEMICA['docente'];
            [$xFallas, $offsetYFallas, $wFallas] = PdfCoordinates::TABLA_ACADEMICA['fallas'];
            [$xPgc, $offsetYPgc, $wPgc] = PdfCoordinates::TABLA_ACADEMICA['pgc'];

            // Nombre de la asignatura
            $this->fuenteNegrita($pdf, 6);
            $this->texto($pdf, $xAsignatura, $y + $offsetYAsignatura, $wAsignatura, mb_strtoupper($nombreAsignatura, 'UTF-8'));

            // Docente que dicta la asignatura
            $this->fuenteNormal($pdf, 5.5);
            $this->texto($pdf, $xDocente, $y + $offsetYDocente, $wDocente, mb_strtoupper($docente, 'UTF-8'));

            // Fallas del periodo
            $this->fuenteNormal($pdf, 5.5);
            $this->texto($pdf, $xFallas, $y + $offsetYFallas, $wFallas, 'Fallas Per: ' . $fallas);

            // PGC del periodo
            $this->fuenteNormal($pdf, 5.5);
            $this->texto($pdf, $xPgc, $y + $offsetYPgc, $wPgc, 'PGC.: ' . $pgc);

            // Línea separadora entre asignaturas
            $pdf->SetDrawColor(120, 120, 120);
            $pdf->SetLineWidth(0.15);
            $pdf->Line(8, $y + 13.2, 207, $y + 13.2);

            // Intensidad horaria
            [$xIh, $offsetYIh, $wIh] = PdfCoordinates::TABLA_ACADEMICA['ih'];

            $this->fuenteNegrita($pdf, 8.5);
            $this->textoCentrado($pdf, $xIh, $y + $offsetYIh, $wIh, $item['ih'] ?? '');


            // Notas por periodo
            $notasPeriodos = $item['notas_periodos'] ?? [];
            $promedioFinal = $item['promedio_final'] ?? null;

            $this->fuenteNormal($pdf, 5.5);

            foreach ([1, 2, 3, 4] as $periodoNumero) {
                $notaPeriodo = $notasPeriodos[$periodoNumero] ?? null;

                if ($notaPeriodo === null || $notaPeriodo === '') {
                    continue;
                }

                [$xNota, $offsetYNota, $wNota] = PdfCoordinates::TABLA_ACADEMICA['periodo_' . $periodoNumero];

                $this->pintarNotaConDesempeno(
                    $pdf,
                    $xNota,
                    $y + $offsetYNota,
                    $wNota,
                    $notaPeriodo
                );
            }

            // Promedio final
            if ($promedioFinal !== null && $promedioFinal !== '') {
                [$xFinal, $offsetYFinal, $wFinal] = PdfCoordinates::TABLA_ACADEMICA['final'];

                $this->pintarNotaConDesempeno(
                    $pdf,
                    $xFinal,
                    $y + $offsetYFinal,
                    $wFinal,
                    $promedioFinal
                );
            }

            // Evidencia de aprendizaje
            [$xEvidencia, $offsetYEvidencia, $wEvidencia] = PdfCoordinates::TABLA_ACADEMICA['evidencia'];

            $evidencias = $this->evidenciasAsignatura($item, $desempenosPorPensum, $data);

            $this->fuenteNormal($pdf, 5.5);

            $yEvidencia = $y + $offsetYEvidencia;

            foreach ($evidencias as $evidencia) {
                $this->texto($pdf, $xEvidencia, $yEvidencia, $wEvidencia, $evidencia);
                $yEvidencia += 3;
            }


            $y += $altoBloque;
        }
    }

    public function pintarDesempenos(Fpdi $pdf, array $desempenos): void
    {
        $this->fuenteNormal($pdf, 6.5);

        $y = PdfCoordinates::DESEMPENOS['inicio_y'];
        $altoFila = PdfCoordinates::DESEMPENOS['alto_fila'];

        foreach ($desempenos as $grupo) {
            $asignatura = $grupo['asignatura'] ?? '';
            $items = $grupo['items'] ?? [];

            foreach ($items as $item) {
                [$xAsignatura, $wAsignatura] = PdfCoordinates::DESEMPENOS['asignatura'];
                [$xDescripcion, $wDescripcion] = PdfCoordinates::DESEMPENOS['descripcion'];

                $this->texto($pdf, $xAsignatura, $y, $wAsignatura, $asignatura);
                $this->multiTexto($pdf, $xDescripcion, $y - 3, $wDescripcion, 3.2, $item);

                $y += $altoFila;
            }
        }
    }

    public function pintarMejoramientos(Fpdi $pdf, array $mejoramientos): void
    {
        $this->fuenteNormal($pdf, 6.5);

        $y = PdfCoordinates::MEJORAMIENTOS['inicio_y'];
        $altoFila = PdfCoordinates::MEJORAMIENTOS['alto_fila'];

        foreach ($mejoramientos as $item) {
            [$xAsignatura, $wAsignatura] = PdfCoordinates::MEJORAMIENTOS['asignatura'];
            [$xCodigo, $wCodigo] = PdfCoordinates::MEJORAMIENTOS['codigo'];
            [$xDescripcion, $wDescripcion] = PdfCoordinates::MEJORAMIENTOS['descripcion'];

            $this->texto($pdf, $xAsignatura, $y, $wAsignatura, $item['asignatura'] ?? '');
            $this->textoCentrado($pdf, $xCodigo, $y, $wCodigo, $item['codigo'] ?? '');
            $this->multiTexto($pdf, $xDescripcion, $y - 3, $wDescripcion, 3.2, $item['descripcion'] ?? '');

            $y += $altoFila;
        }
    }

    public function pintarFinal(Fpdi $pdf, array $data): void
    {
        $boletin = $data['boletin'] ?? [];

        $this->fuenteNormal($pdf, 7);

        [$xObs, $yObs, $wObs] = PdfCoordinates::FINAL['observaciones'];
        $this->multiTexto($pdf, $xObs, $yObs, $wObs, 4, $boletin['observaciones'] ?? '');

        $this->pintarListaCodigos(
            $pdf,
            'Perfil Rembrandtino',
            $data['perfil'] ?? [],
            PdfCoordinates::FINAL['perfil_titulo'],
            PdfCoordinates::FINAL['perfil_items']
        );

        $this->pintarListaCodigos(
            $pdf,
            'Acompañamiento Familiar',
            $data['acompanamiento'] ?? [],
            PdfCoordinates::FINAL['acompanamiento_titulo'],
            PdfCoordinates::FINAL['acompanamiento_items']
        );

        $this->pintarConvenciones($pdf, $data);
    }

    protected function pintarListaCodigos(
        Fpdi $pdf,
        string $titulo,
        array $items,
        array $tituloCoords,
        array $itemCoords
    ): void {
        [$xTitulo, $yTitulo, $wTitulo] = $tituloCoords;
        [$xItem, $yItem, $wItem] = $itemCoords;

        $this->fuenteNegrita($pdf, 6.5);
        $this->texto($pdf, $xTitulo, $yTitulo, $wTitulo, $titulo);

        $this->fuenteNormal($pdf, 6);

        foreach ($items as $item) {
            $linea = trim(($item['codigo'] ?? '') . '. ' . ($item['descripcion'] ?? ''));

            $this->multiTexto($pdf, $xItem, $yItem, $wItem, 3, $linea);

            $yItem += 7;
        }
    }

   

    protected function fuenteNormal(Fpdi $pdf, float $size): void
    {
        $pdf->SetFont('Arial', '', $size);
        $pdf->SetTextColor(20, 20, 20);
    }

    protected function fuenteNegrita(Fpdi $pdf, float $size): void
    {
        $pdf->SetFont('Arial', 'B', $size);
        $pdf->SetTextColor(20, 20, 20);
    }

    protected function texto(Fpdi $pdf, float $x, float $y, float $w, mixed $texto): void
    {
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, 4, $this->limpiarTexto($texto), 0, 0, 'L');
    }

    protected function textoCentrado(Fpdi $pdf, float $x, float $y, float $w, mixed $texto): void
    {
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, 4, $this->limpiarTexto($texto), 0, 0, 'C');
    }

    protected function multiTexto(Fpdi $pdf, float $x, float $y, float $w, float $h, mixed $texto): void
    {
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($w, $h, $this->limpiarTexto($texto), 0, 'L');
    }

    protected function multiTextoCentrado(Fpdi $pdf, float $x, float $y, float $w, float $h, mixed $texto): void
    {
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($w, $h, $this->limpiarTexto($texto), 0, 'C');
    }

    protected function pintarNotaConDesempeno(
        Fpdi $pdf,
        float $x,
        float $y,
        float $w,
        mixed $nota
    ): void {
        if ($nota === null || $nota === '' || ! is_numeric($nota)) {
            return;
        }

        $nota = (float) $nota;

        $letra = match (true) {
            $nota >= 95 => 'S',
            $nota >= 80 => 'A',
            $nota >= 60 => 'BS',
            default => 'B',
        };

        $this->fuenteNormal($pdf, 5);
        $this->textoCentrado($pdf, $x, $y, $w, $letra);

        $this->fuenteNegrita($pdf, 8);
        $this->textoCentrado($pdf, $x, $y + 3.2, $w, number_format($nota, 0));
    }


    protected function evidenciasAsignatura(array $item, $desempenosPorPensum, array $data): array
    {
        $nombre = mb_strtolower($item['nombre'] ?? '', 'UTF-8');

        if (str_contains($nombre, 'perfil')) {
            return collect($data['perfil'] ?? [])
                ->pluck('descripcion')
                ->filter()
                ->values()
                ->toArray();
        }

        if (str_contains($nombre, 'acompañamiento') || str_contains($nombre, 'acompanamiento')) {
            return collect($data['acompanamiento'] ?? [])
                ->pluck('descripcion')
                ->filter()
                ->values()
                ->toArray();
        }

        return collect(
            $desempenosPorPensum->get($item['pensum_academico_id'])['items'] ?? []
        )
            ->filter(fn ($texto) => trim((string) $texto) !== '' && trim((string) $texto) !== '*')
            ->values()
            ->toArray();
    }

   

    public function pintarObservaciones(Fpdi $pdf, array $data): void
    {
        [$x, $y, $w] = PdfCoordinates::OBSERVACIONES;

        $this->fuenteNormal($pdf, 7.5);

        $this->multiTexto(
            $pdf,
            $x,
            $y,
            $w,
            4.5,
            $data['boletin']['observaciones'] ?? ''
        );
    }

    public function pintarConvenciones(Fpdi $pdf, array $data): void
    {
        $convenciones = array_values($data['convenciones'] ?? []);

        if (empty($convenciones)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Orden de impresión en el PDF
        |--------------------------------------------------------------------------
        | La consulta ya viene ordenada por el campo "orden":
        | 0 = Bajo
        | 1 = Básico
        | 2 = Alto
        | 3 = Superior
        |
        | En el PDF queremos mostrar:
        | Superior | Alto
        | Básico   | Bajo
        |--------------------------------------------------------------------------
        */
        if (count($convenciones) === 4) {
            $convenciones = [
                $convenciones[3],
                $convenciones[2],
                $convenciones[1],
                $convenciones[0],
            ];
        }

        $inicioX = PdfCoordinates::CONVENCIONES['inicio_x'];
        $inicioY = PdfCoordinates::CONVENCIONES['inicio_y'];

        $ancho = 92;
        $saltoY = 5.5;

        $this->fuenteNormal($pdf, 8);

        foreach ($convenciones as $i => $convencion) {
            $columna = $i % 2;
            $fila = intdiv($i, 2);

            $x = $inicioX + ($columna * $ancho);
            $y = $inicioY + ($fila * $saltoY);

            // Convención (roja)
            $pdf->SetTextColor(170, 20, 20);

            $this->texto(
                $pdf,
                $x,
                $y,
                10,
                ($convencion['convencion'] ?? '') . ':'
            );

            // Resto del texto (negro)
            $pdf->SetTextColor(0, 0, 0);

            $this->texto(
                $pdf,
                $x + 7,
                $y,
                $ancho - 7,
                ($convencion['nombre'] ?? '') . ': ' .
                ($convencion['descripcion_convencion'] ?? '')
            );
        }
    }

    protected function limpiarTexto(mixed $texto): string
    {
        return mb_convert_encoding((string) $texto, 'ISO-8859-1', 'UTF-8');
    }


}