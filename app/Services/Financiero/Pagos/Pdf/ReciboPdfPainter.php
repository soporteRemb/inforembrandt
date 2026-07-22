<?php

namespace App\Services\Financiero\Pagos\Pdf;

use setasign\Fpdi\Fpdi;

class ReciboPdfPainter
{
    private const ANCHO_PAGINA = 215.9;
    private const ALTO_PAGINA = 139.7;

    private const MARGEN_IZQUIERDO = 10.0;
    private const MARGEN_DERECHO = 10.0;
    private const MARGEN_SUPERIOR = 7.0;
    private const MARGEN_INFERIOR = 7.0;

    private const ANCHO_UTIL = self::ANCHO_PAGINA
        - self::MARGEN_IZQUIERDO
        - self::MARGEN_DERECHO;

    /**
     * Altura máxima recomendada para el detalle.
     *
     * Si posteriormente un recibo supera este espacio, agregaremos
     * paginación específica sin modificar el diseño de la primera hoja.
     */
    private const LIMITE_DETALLE_Y = 100.0;

    public function pintar(
        Fpdi $pdf,
        array $data,
        
        string $identificador,
        bool $esReimpresion,
    ): void {
        $this->pintarMarcoGeneral($pdf);

        $this->pintarEncabezado(
            pdf: $pdf,
            data: $data,
            identificador: $identificador,
            esReimpresion: $esReimpresion,
        );

        $this->pintarInformacionGeneral(
            pdf: $pdf,
            data: $data,
        );

        $yFinalDetalle = $this->pintarDetalle(
            pdf: $pdf,
            data: $data,
        );

        $this->pintarZonaInferior(
            pdf: $pdf,
            data: $data,
            yInicial: $yFinalDetalle,
        );

        $this->pintarPie(
            pdf: $pdf,
            data: $data,
            esReimpresion: $esReimpresion,
        );
    }

    private function pintarMarcoGeneral(Fpdi $pdf): void
    {
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
        string $identificador,
        bool $esReimpresion,
    ): void {
        $x = self::MARGEN_IZQUIERDO;
        $y = self::MARGEN_SUPERIOR;
        $ancho = self::ANCHO_UTIL;
        $alto = 22.0;

        /*
        |--------------------------------------------------------------------------
        | Distribución
        |--------------------------------------------------------------------------
        | Institución: 132 mm
        | Documento: ancho restante
        */
        $anchoInstitucion = 132.0;
        $xDocumento = $x + $anchoInstitucion;
        $anchoDocumento = $ancho - $anchoInstitucion;

        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(203, 213, 225);

        $pdf->Rect(
            $x,
            $y,
            $ancho,
            $alto,
            'DF'
        );

        /*
        |--------------------------------------------------------------------------
        | Logo institucional
        |--------------------------------------------------------------------------
        */
        $logoRelativo = trim(
            (string) ($data['institucion']['logo'] ?? '')
        );

        $logoAbsoluto = $logoRelativo !== ''
            ? storage_path('app/public/' . $logoRelativo)
            : null;

        $hayLogo = $logoAbsoluto
            && is_file($logoAbsoluto);

        $logoX = $x + 4;
        $logoY = $y + 2.5;
        $logoAncho = 16.0;
        $logoAlto = 16.0;

        if ($hayLogo) {
            $pdf->Image(
                $logoAbsoluto,
                $logoX,
                $logoY,
                $logoAncho,
                $logoAlto
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Posición inicial de los textos institucionales
        |--------------------------------------------------------------------------
        */
        $textoX = $hayLogo
            ? $x + 23
            : $x + 4;

        $textoAncho = $xDocumento - $textoX - 4;

        /*
        |--------------------------------------------------------------------------
        | Nombre de la sede
        |--------------------------------------------------------------------------
        */
        $nombreInstitucion = trim(
            (string) ($data['institucion']['nombre'] ?? '')
        );

        if ($nombreInstitucion === '') {
            $nombreInstitucion = 'Institución educativa';
        }

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetXY($textoX, $y + 2.5);

        $pdf->Cell(
            $textoAncho,
            4.5,
            $this->textoPdf($nombreInstitucion),
            0,
            1,
            'L'
        );

        /*
        |--------------------------------------------------------------------------
        | Datos institucionales
        |--------------------------------------------------------------------------
        */
        $pdf->SetFont('Arial', '', 6.6);
        $pdf->SetTextColor(71, 85, 105);

        /*
        * La posición avanza solamente cuando existe información,
        * evitando espacios vacíos.
        */
        $yTexto = $y + 7.0;

        /*
        |--------------------------------------------------------------------------
        | NIT
        |--------------------------------------------------------------------------
        */
        $nit = trim(
            (string) ($data['institucion']['nit'] ?? '')
        );

        if ($nit !== '') {
            $pdf->SetXY($textoX, $yTexto);

            $pdf->Cell(
                $textoAncho,
                3,
                $this->textoPdf('NIT ' . $nit),
                0,
                1,
                'L'
            );

            $yTexto += 3.1;
        }

        /*
        |--------------------------------------------------------------------------
        | Dirección
        |--------------------------------------------------------------------------
        */
        $direccion = trim(
            (string) ($data['institucion']['direccion'] ?? '')
        );

        if ($direccion !== '') {
            $pdf->SetXY($textoX, $yTexto);

            $pdf->Cell(
                $textoAncho,
                3,
                $this->textoPdf($direccion),
                0,
                1,
                'L'
            );

            $yTexto += 3.1;
        }

        /*
        |--------------------------------------------------------------------------
        | Teléfono y correo
        |--------------------------------------------------------------------------
        */
        $contacto = array_filter([
            filled($data['institucion']['telefono'] ?? null)
                ? 'Tel. ' . $data['institucion']['telefono']
                : null,

            filled($data['institucion']['email'] ?? null)
                ? $data['institucion']['email']
                : null,
        ]);

        if ($contacto !== []) {
            $pdf->SetXY($textoX, $yTexto);

            $pdf->Cell(
                $textoAncho,
                3,
                $this->textoPdf(
                    implode('  ·  ', $contacto)
                ),
                0,
                1,
                'L'
            );

            $yTexto += 3.1;
        }

        /*
        |--------------------------------------------------------------------------
        | Tipo de documento
        |--------------------------------------------------------------------------
        */
        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetXY($textoX, $yTexto);

        $pdf->Cell(
            $textoAncho,
            3.2,
            $this->textoPdf('RECIBO DE CAJA'),
            0,
            0,
            'L'
        );

        /*
        |--------------------------------------------------------------------------
        | Separador del bloque documental
        |--------------------------------------------------------------------------
        */
        $pdf->SetDrawColor(203, 213, 225);

        $pdf->Line(
            $xDocumento,
            $y,
            $xDocumento,
            $y + $alto
        );

        

        /*
        |--------------------------------------------------------------------------
        | Estado anulado
        |--------------------------------------------------------------------------
        */
        $estaAnulado =
            (bool) ($data['anulacion']['esta_anulado'] ?? false);

        if ($estaAnulado) {
            $pdf->SetFont('Arial', 'B', 12.5);
            $pdf->SetTextColor(185, 28, 28);

            $pdf->SetXY(
                $xDocumento + 4,
                $y + 4
            );

            $pdf->Cell(
                ($anchoDocumento - 8) / 2,
                5,
                $this->textoPdf('ANULADO'),
                0,
                0,
                'L'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Número del recibo
        |--------------------------------------------------------------------------
        */
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(15, 23, 42);

        $pdf->SetXY(
            $xDocumento + 4,
            $y + 3
        );

        $pdf->Cell(
            $anchoDocumento - 8,
            6,
            $this->textoPdf($identificador),
            0,
            1,
            'R'
        );

        /*
        |--------------------------------------------------------------------------
        | Fecha original del recibo
        |--------------------------------------------------------------------------
        */

        $pdf->SetFont('Arial', '', 6.5);
        $pdf->SetTextColor(100, 116, 139);

        $pdf->SetXY(
            $xDocumento + 4,
            $y + 11
        );

        $pdf->Cell(
            $anchoDocumento - 8,
            3.5,
            $this->textoPdf(
                'Fecha: ' . ($data['fecha'] ?? '—')
            ),
            0,
            0,
            'R'
        );

        /*
        |--------------------------------------------------------------------------
        | Fecha de generación de la reimpresión
        |--------------------------------------------------------------------------
        */
        if ($esReimpresion) {
            $pdf->SetXY(
                $xDocumento + 4,
                $y + 15
            );

            $pdf->Cell(
                $anchoDocumento - 8,
                3.5,
                $this->textoPdf(
                    'Reimpresión: ' . now()->format('d/m/Y h:i a')
                ),
                0,
                0,
                'R'
            );
        }
    }

    private function pintarInformacionGeneral(
        Fpdi $pdf,
        array $data,
    ): void {
        $x = self::MARGEN_IZQUIERDO;
        $y = 31.0;
        $ancho = self::ANCHO_UTIL;
        $alto = 13.0;

        $columnas = [
            [
                'ancho' => 76.0,
                'etiqueta' => 'ESTUDIANTE',
                'valor' => $data['estudiante']['nombre'] ?? '—',
            ],
            [
                'ancho' => 29.0,
                'etiqueta' => 'CÓDIGO',
                'valor' => $data['estudiante']['codigo'] ?? '—',
            ],
            [
                'ancho' => 39.0,
                'etiqueta' => 'DOCUMENTO',
                'valor' => $data['estudiante']['documento'] ?? '—',
            ],
            [
                'ancho' => 24.0,
                'etiqueta' => 'CURSO',
                'valor' => $this->cursoCompleto($data),
            ],
            [
                'ancho' => 0,
                'etiqueta' => 'RECIBIDO DE',
                'valor' => $data['recibido_de'] ?? '—',
            ],
        ];

        $anchoUsado = collect($columnas)
            ->sum(
                fn (array $columna) =>
                    (float) $columna['ancho']
            );

        $columnas[array_key_last($columnas)]['ancho'] =
            $ancho - $anchoUsado;

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

    /**
     * Pinta las líneas del recibo y devuelve la coordenada Y final.
     */
    private function pintarDetalle(
        Fpdi $pdf,
        array $data,
    ): float {
        $x = self::MARGEN_IZQUIERDO;
        $y = 44.0;

        $columnas = [
            'concepto' => [
                'x' => $x,
                'ancho' => 45.0,
                'titulo' => 'CONCEPTO',
                'alineacion' => 'L',
            ],

            'mes' => [
                'x' => $x + 45.0,
                'ancho' => 18.0,
                'titulo' => 'MES',
                'alineacion' => 'L',
            ],

            'forma' => [
                'x' => $x + 63.0,
                'ancho' => 29.0,
                'titulo' => 'FORMA DE PAGO',
                'alineacion' => 'L',
            ],

            'referencia' => [
                'x' => $x + 92.0,
                'ancho' => 45.0,
                'titulo' => 'REFERENCIA / FECHA',
                'alineacion' => 'L',
            ],

            'descuento' => [
                'x' => $x + 137.0,
                'ancho' => 27.0,
                'titulo' => 'DESCUENTO',
                'alineacion' => 'R',
            ],

            'valor' => [
                'x' => $x + 164.0,
                'ancho' => self::ANCHO_UTIL - 164.0,
                'titulo' => 'VALOR RECIBIDO',
                'alineacion' => 'R',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Encabezado de tabla
        |--------------------------------------------------------------------------
        */
        $altoEncabezado = 7.0;

        $pdf->SetFillColor(241, 245, 249);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->Rect(
            $x,
            $y,
            self::ANCHO_UTIL,
            $altoEncabezado,
            'DF'
        );

        $pdf->SetFont('Arial', 'B', 6.8);
        $pdf->SetTextColor(51, 65, 85);

        foreach ($columnas as $columna) {
            $pdf->SetXY(
                $columna['x'] + 1,
                $y + 1.5
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

        /*
        |--------------------------------------------------------------------------
        | Filas
        |--------------------------------------------------------------------------
        */
        $lineas = $data['lineas'] ?? [];

        if ($lineas === []) {
            $pdf->SetFont('Arial', '', 7.5);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->SetXY($x, $y + 3);

            $pdf->Cell(
                self::ANCHO_UTIL,
                6,
                $this->textoPdf(
                    'No se encontraron líneas para este recibo.'
                ),
                0,
                0,
                'C'
            );

            return $y + 12;
        }

        foreach ($lineas as $linea) {
            if ($y >= self::LIMITE_DETALLE_Y) {
                $this->pintarAvisoContinuacion($pdf, $y);

                break;
            }

            $referencia = $this->construirReferencia($linea);

            $altoFila = $this->calcularAltoFila(
                concepto: (string) ($linea['concepto'] ?? ''),
                referencia: $referencia,
            );

            $pdf->SetDrawColor(226, 232, 240);

            /*
             * Bordes inferiores.
             */
            $pdf->Line(
                $x,
                $y + $altoFila,
                $x + self::ANCHO_UTIL,
                $y + $altoFila
            );

            /*
             * Separadores verticales.
             */
            foreach ([
                $x + 45.0,
                $x + 63.0,
                $x + 92.0,
                $x + 137.0,
                $x + 164.0,
            ] as $separadorX) {
                $pdf->Line(
                    $separadorX,
                    $y,
                    $separadorX,
                    $y + $altoFila
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Concepto
            |--------------------------------------------------------------------------
            */
            $concepto = trim((string) $linea['concepto']);

            $tamanoConcepto = match (true) {
                mb_strlen($concepto, 'UTF-8') > 55 => 5.6,
                mb_strlen($concepto, 'UTF-8') > 42 => 6.0,
                mb_strlen($concepto, 'UTF-8') > 30 => 6.5,
                default => 7.0,
            };

            $pdf->SetFont('Arial', 'B', $tamanoConcepto);

            $pdf->SetXY(
                $columnas['concepto']['x'] + 1.5,
                $y + 2
            );

            $pdf->MultiCell(
                $columnas['concepto']['ancho'] - 3,
                3,
                $this->textoPdf($concepto),
                0,
                'L'
            );

            

            /*
            |--------------------------------------------------------------------------
            | Mes
            |--------------------------------------------------------------------------
            */
            $this->celdaTexto(
                pdf: $pdf,
                x: $columnas['mes']['x'],
                y: $y,
                ancho: $columnas['mes']['ancho'],
                alto: $altoFila,
                texto: $linea['mes'] ?? '',
                alineacion: 'L',
            );

            /*
            |--------------------------------------------------------------------------
            | Forma de pago
            |--------------------------------------------------------------------------
            */
            $this->celdaTexto(
                pdf: $pdf,
                x: $columnas['forma']['x'],
                y: $y,
                ancho: $columnas['forma']['ancho'],
                alto: $altoFila,
                texto: $linea['forma_pago'] ?? '—',
                alineacion: 'L',
                negrita: true,
            );

            /*
            |--------------------------------------------------------------------------
            | Referencia y fecha
            |--------------------------------------------------------------------------
            */
            $this->celdaTexto(
                pdf: $pdf,
                x: $columnas['referencia']['x'],
                y: $y,
                ancho: $columnas['referencia']['ancho'],
                alto: $altoFila,
                texto: $referencia,
                alineacion: 'L',
                tamano: 6.5,
            );

            /*
            |--------------------------------------------------------------------------
            | Descuento
            |--------------------------------------------------------------------------
            */
            $this->celdaMoneda(
                pdf: $pdf,
                x: $columnas['descuento']['x'],
                y: $y,
                ancho: $columnas['descuento']['ancho'],
                alto: $altoFila,
                valor: (float) ($linea['descuento'] ?? 0),
                negrita: false,
            );

            /*
            |--------------------------------------------------------------------------
            | Valor recibido
            |--------------------------------------------------------------------------
            */
            $this->celdaMoneda(
                pdf: $pdf,
                x: $columnas['valor']['x'],
                y: $y,
                ancho: $columnas['valor']['ancho'],
                alto: $altoFila,
                valor: (float) ($linea['valor_recibido'] ?? 0),
                negrita: true,
            );

            $y += $altoFila;
        }

        return $y;
    }

    private function pintarZonaInferior(
        Fpdi $pdf,
        array $data,
        float $yInicial,
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Zona inferior anclada al borde general
        |--------------------------------------------------------------------------
        */
        $x = self::MARGEN_IZQUIERDO;
        $ancho = self::ANCHO_UTIL;

        $anchoIzquierdo = 108.0;
        $xTotales = $x + $anchoIzquierdo;
        $anchoTotales = $ancho - $anchoIzquierdo;

        /*
        * Espacio suficiente para:
        * - tres líneas de observación;
        * - registrado por.
        */
        $altoZona = 24.0;

        /*
        * El marco general termina a 6 mm del borde de la hoja.
        * Observaciones y totales terminan exactamente en esa línea.
        */
        $yFinal = 139.7 - 7.5;
        $y = $yFinal - $altoZona;

        /*
        |--------------------------------------------------------------------------
        | Observaciones: borde superior y laterales, sin borde inferior
        |--------------------------------------------------------------------------
        */
        $pdf->SetDrawColor(203, 213, 225);

        // Línea superior
        $pdf->Line(
            $x,
            $y,
            $x + $anchoIzquierdo,
            $y
        );

        // Lateral izquierdo
        $pdf->Line(
            $x,
            $y,
            $x,
            $yFinal
        );

        // División entre observaciones y totales
        $pdf->Line(
            $xTotales,
            $y,
            $xTotales,
            $yFinal
        );

        $pdf->SetFont('Arial', 'B', 8.6);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->SetXY($x + 2, $y + 2);

        $pdf->Cell(
            $anchoIzquierdo - 4,
            3,
            $this->textoPdf('OBSERVACIONES'),
            0,
            1,
            'L'
        );

        $observaciones = trim(
            $this->obtenerObservaciones($data)
        );

        if ($observaciones !== '') {
            $pdf->SetFont('Arial', '', 6.8);
            $pdf->SetTextColor(51, 65, 85);
            $pdf->SetXY($x + 2, $y + 6);

            $pdf->MultiCell(
                $anchoIzquierdo - 4,
                3.5,
                $this->textoPdf($observaciones),
                0,
                'L'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Totales distribuidos en toda la altura
        |--------------------------------------------------------------------------
        */
        $filasTotales = [
            [
                'etiqueta' => 'Valor de obligaciones',
                'valor' => (float) (
                    $data['valor_obligaciones'] ?? 0
                ),
                'negativo' => false,
                'es_total' => false,
            ],
            [
                'etiqueta' => 'Descuento aplicado',
                'valor' => (float) (
                    $data['total_descuentos'] ?? 0
                ),
                'negativo' => true,
                'es_total' => false,
            ],
        ];

        if (
            (float) ($data['saldo_favor_generado'] ?? 0) > 0
        ) {
            $filasTotales[] = [
                'etiqueta' => 'Saldo a favor',
                'valor' => (float) $data['saldo_favor_generado'],
                'negativo' => false,
                'es_total' => false,
            ];
        }

        $filasTotales[] = [
            'etiqueta' => 'TOTAL PAGADO',
            'valor' => (float) (
                $data['total_recibido'] ?? 0
            ),
            'negativo' => false,
            'es_total' => true,
        ];

        $cantidadFilas = count($filasTotales);
        $altoFila = $altoZona / $cantidadFilas;

        /*
        * Lateral derecho.
        */
        $pdf->Line(
            $xTotales + $anchoTotales,
            $y,
            $xTotales + $anchoTotales,
            $yFinal
        );

        foreach ($filasTotales as $indice => $fila) {
            $yFila = $y + ($indice * $altoFila);

            /*
            * Fondo del total principal.
            */
            if ($fila['es_total']) {
                $pdf->SetFillColor(248, 250, 252);

                $pdf->Rect(
                    $xTotales,
                    $yFila,
                    $anchoTotales,
                    $altoFila,
                    'F'
                );
            }

            /*
            * Línea superior de cada fila.
            * No se dibuja línea inferior en la última.
            */
            $pdf->SetDrawColor(203, 213, 225);

            $pdf->Line(
                $xTotales,
                $yFila,
                $xTotales + $anchoTotales,
                $yFila
            );

            /*
            * Etiqueta.
            */
            $pdf->SetFont(
                'Arial',
                $fila['es_total'] ? 'B' : '',
                $fila['es_total'] ? 7.0 : 6.4
            );

            $pdf->SetTextColor(
                $fila['es_total'] ? 15 : 71,
                $fila['es_total'] ? 23 : 85,
                $fila['es_total'] ? 42 : 105
            );

            $pdf->SetXY(
                $xTotales + 2,
                $yFila + (($altoFila - 4) / 2)
            );

            $pdf->Cell(
                35,
                4,
                $this->textoPdf($fila['etiqueta']),
                0,
                0,
                'L'
            );

            /*
            * Valor.
            */
            $textoValor = '$ ' . number_format(
                $fila['valor'],
                0,
                ',',
                '.'
            );

            if (
                $fila['negativo']
                && $fila['valor'] > 0
            ) {
                $textoValor = '-$ ' . number_format(
                    $fila['valor'],
                    0,
                    ',',
                    '.'
                );
            }

            if ($fila['es_total']) {
                $pdf->SetFont('Arial', 'B', 9.0);
                $pdf->SetTextColor(0, 140, 69);
            } else {
                $pdf->SetFont('Arial', 'B', 7.2);
                $pdf->SetTextColor(15, 23, 42);
            }

            $altoTextoValor = $fila['es_total']
                ? 4.5
                : 4.0;

            $ajusteVertical = $fila['es_total']
                ? 0.8
                : 0.0;

            $pdf->SetXY(
                $xTotales + 38,
                $yFila + (($altoFila - $altoTextoValor) / 2) - $ajusteVertical
            );

            $pdf->Cell(
                $anchoTotales - 40,
                $altoTextoValor,
                $textoValor,
                0,
                0,
                'R'
            );
        }

        /*
        * No se dibuja borde inferior.
        * Se utiliza la línea inferior del marco general.
        */
    }

    private function pintarPie(
        Fpdi $pdf,
        array $data,
        bool $esReimpresion,
    ): void {
        // Sin contenido en el pie.
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

        /*
        |--------------------------------------------------------------------------
        | Etiqueta
        |--------------------------------------------------------------------------
        */
        $pdf->SetFont('Arial', '', 5.8);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->SetXY(
            $x + 1.5,
            $y + 1.3
        );

        $pdf->Cell(
            $ancho - 3,
            2.5,
            $this->textoPdf($etiqueta),
            0,
            1,
            'L'
        );

        /*
        |--------------------------------------------------------------------------
        | Valor
        |--------------------------------------------------------------------------
        */
        $textoValor = trim((string) $valor);

        if ($textoValor === '') {
            $textoValor = '—';
        }

        $tamanoValor = match (true) {
            mb_strlen($textoValor, 'UTF-8') > 42 => 6.1,
            mb_strlen($textoValor, 'UTF-8') > 30 => 6.6,
            default => 7.2,
        };

        $pdf->SetFont('Arial', 'B', $tamanoValor);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetXY(
            $x + 1.5,
            $y + 4.2
        );

        $pdf->MultiCell(
            $ancho - 3,
            3.1,
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
        $pdf->SetXY($x + 1.2, $y + 1.5);

        $pdf->MultiCell(
            $ancho - 2.4,
            3.3,
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
        bool $negrita,
    ): void {
        $pdf->SetFont(
            'Arial',
            $negrita ? 'B' : '',
            $negrita ? 7.6 : 7.1
        );

        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetXY($x + 1, $y + 2);

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

    private function filaTotal(
        Fpdi $pdf,
        float $x,
        float $y,
        float $ancho,
        string $etiqueta,
        float $valor,
        bool $mostrarNegativo = false,
    ): void {
        $pdf->SetDrawColor(226, 232, 240);
        $pdf->Rect($x, $y, $ancho, 5);

        $pdf->SetFont('Arial', '', 6.4);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->SetXY($x + 2, $y + 0.8);

        $pdf->Cell(
            38,
            3.5,
            $this->textoPdf($etiqueta),
            0,
            0,
            'L'
        );

        $textoValor = '$ ' . number_format(
            $valor,
            0,
            ',',
            '.'
        );

        if ($mostrarNegativo && $valor > 0) {
            $textoValor = '-$ ' . number_format(
                $valor,
                0,
                ',',
                '.'
            );
        }

        $pdf->SetFont('Arial', 'B', 7.2);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetXY($x + 40, $y + 0.8);

        $pdf->Cell(
            $ancho - 42,
            3.5,
            $textoValor,
            0,
            0,
            'R'
        );
    }

    private function calcularAltoFila(
        string $concepto,
        string $referencia,
    ): float {
        $alto = 8.0;

        if (mb_strlen($concepto, 'UTF-8') > 34) {
            $alto = max($alto, 12.0);
        }

        if (mb_strlen($referencia, 'UTF-8') > 38) {
            $alto = max($alto, 12.0);
        }

        return $alto;
    }

    private function construirReferencia(array $linea): string
    {
        $partes = [];

        if (filled($linea['numero_referencia'] ?? null)) {
            $partes[] = 'Ref. ' . $linea['numero_referencia'];
        }

        if (filled($linea['fecha_consignacion'] ?? null)) {
            $partes[] =
                'Fecha ' . $linea['fecha_consignacion'];
        }

        return implode(' · ', $partes);
    }

    private function obtenerObservaciones(array $data): string
    {
        $detalles = collect($data['lineas'] ?? [])
            ->pluck('detalle')
            ->map(
                fn ($detalle) =>
                    trim((string) $detalle)
            )
            ->filter(
                fn (string $detalle) =>
                    $detalle !== ''
            )
            ->unique()
            ->values();

        $partes = [];

        if ($detalles->isNotEmpty()) {
            $partes[] = $detalles->implode(' | ');
        }

        $registradoPor = trim(
            (string) (
                $data['registrado_por']
                ?? ''
            )
        );

        if ($registradoPor !== '') {
            $partes[] = 'Registrado por: ' . $registradoPor;
        }

        return implode("\n\n", $partes);
    }

    private function cursoCompleto(array $data): string
    {
        $grado = trim(
            (string) ($data['estudiante']['grado'] ?? '')
        );

        $curso = trim(
            (string) ($data['estudiante']['curso'] ?? '')
        );

        $texto = trim(
            implode(' - ', array_filter([
                $grado,
                $curso,
            ]))
        );

        return $texto !== '' ? $texto : '—';
    }

    private function pintarAvisoContinuacion(
        Fpdi $pdf,
        float $y,
    ): void {
        $pdf->SetFont('Arial', 'I', 6.5);
        $pdf->SetTextColor(185, 28, 28);
        $pdf->SetXY(
            self::MARGEN_IZQUIERDO,
            $y + 1
        );

        $pdf->Cell(
            self::ANCHO_UTIL,
            4,
            $this->textoPdf(
                'El recibo contiene más líneas de las disponibles en esta primera versión.'
            ),
            0,
            0,
            'C'
        );
    }

    private function textoPdf(string $texto): string
    {
        return iconv(
            'UTF-8',
            'windows-1252//TRANSLIT',
            $texto
        ) ?: $texto;
    }
}