<?php

namespace App\Services\Boletines\Pdf;

class PdfCoordinates
{
    public const PAGE_WIDTH = 215.9;
    public const PAGE_HEIGHT = 279.4;

    /******************************************************************
     * ENCABEZADO DEL BOLETÍN
     * Coordenadas en milímetros.
     *
     * Guía:
     * - Nombre estudiante: fila 70, columna 10
     * - Documento: debajo del nombre
     * - Grado: fila 75, columna 70
     * - Jornada: fila 70, columna 125
     * - Periodo: fila 70, columna 170
     * - Director de curso: fila 85, columna 90
     ******************************************************************/
    public const ENCABEZADO = [
        'estudiante_nombre' => [10, 70, 38],
        'estudiante_documento' => [10, 79, 58],

        // Grado + curso: subir y mover a la izquierda
        'curso_grado' => [58, 70, 56],

        // Jornada: mover a la izquierda
        'curso_jornada' => [116, 70, 35],

        'periodo_academico' => [170, 70, 28],

        'director_curso' => [83, 83, 75],
    ];

    /******************************************************************
     * TABLA ACADÉMICA - COLUMNA IZQUIERDA
     * Cada asignatura se pinta en bloque:
     * - Nombre asignatura
     * - Docente
     * - Fallas
     * - PGC
     * Donde:ejemplo

        40 = X (comienza justo después del texto "Observaciones:")
        230 = Y (como en la plantilla)
        160 = ancho disponible hasta el borde derecho.
     ******************************************************************/
    public const TABLA_ACADEMICA = [
        'inicio_y' => 104,
        'alto_bloque' => 13.2,

        'asignatura' => [10, 0, 55],
        'docente' => [10, 2.8, 55],
        'fallas' => [10, 5.8, 55],
        'pgc' => [10, 8.8, 55],

        'ih' => [61, 4.5, 8],

        'periodo_1' => [72.5, 1.5, 8],
        'periodo_2' => [81.5, 1.5, 8],
        'periodo_3' => [90.5, 1.5, 8],
        'periodo_4' => [99.5, 1.5, 8],

        'final' => [106.5, 1.5, 12],

        'evidencia' => [121, 0.3, 82],
        
    ];

    public const OBSERVACIONES = [40, 227.5, 160];

    public const CONVENCIONES = [
        'inicio_x' => 8,
        'inicio_y' => 250,
    ];


    
}