<?php

namespace App\Services\DocumentosEstudiante;

use RuntimeException;

class ResolverPlantillaPdf
{
    private const CARPETAS = [
        'contrato_servicios' => 'contrato_servicios',
        'comportamiento' => 'comportamiento',
        'pagare' => 'pagare',
        'autorizacion_consulta' => 'autorizacion_consulta',
        'autorizacion_pagare' => 'autorizacion_pagare',
        'autorizacion_datos' => 'autorizacion_datos',
    ];

    public function obtenerRuta(string $tipo): string
    {
        if (!isset(self::CARPETAS[$tipo])) {
            throw new RuntimeException(
                "Tipo de plantilla PDF no reconocido: {$tipo}"
            );
        }

        $directorio = storage_path(
            'app/private/documentos_estudiante/'
            . self::CARPETAS[$tipo]
        );

        if (!is_dir($directorio)) {
            throw new RuntimeException(
                "No existe la carpeta de plantilla para: {$tipo}"
            );
        }

        $archivos = glob(
            $directorio
            . DIRECTORY_SEPARATOR
            . '*.[pP][dD][fF]',
            GLOB_NOSORT
        ) ?: [];

        if (count($archivos) === 0) {
            throw new RuntimeException(
                "No se encontró una plantilla PDF para: {$tipo}"
            );
        }

        if (count($archivos) > 1) {
            throw new RuntimeException(
                "Existe más de una plantilla PDF para: {$tipo}. "
                . 'Debe existir únicamente una plantilla vigente.'
            );
        }

        return $archivos[0];
    }
}