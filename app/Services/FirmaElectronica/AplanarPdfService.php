<?php

namespace App\Services\FirmaElectronica;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class AplanarPdfService
{
    public function aplanar(
        string $rutaOrigen,
        ?string $directorioDestino = null
    ): string {
        if (! is_file($rutaOrigen)) {
            throw new RuntimeException(
                'No se encontró el PDF que se debe aplanar.'
            );
        }

        $directorioDestino ??= storage_path(
            'app/private/tmp_firma_electronica/'
            . Str::uuid()
        );

        File::ensureDirectoryExists(
            $directorioDestino
        );

        $rutaSalida =
            $directorioDestino
            . DIRECTORY_SEPARATOR
            . 'documento-aplanado.pdf';

        $pdftk = config(
            'services.pdftk.binary',
            'pdftk'
        );

        /*
         * PDFtk convierte los campos AcroForm y sus valores
         * visibles en contenido fijo del PDF.
         */
        $comando = sprintf(
            '"%s" "%s" output "%s" flatten 2>&1',
            $pdftk,
            $rutaOrigen,
            $rutaSalida
        );

        $salida = [];
        $codigo = 0;

        exec(
            $comando,
            $salida,
            $codigo
        );

        if (
            $codigo !== 0
            || ! is_file($rutaSalida)
        ) {
            throw new RuntimeException(
                'PDFtk no pudo aplanar el documento. '
                . implode(
                    PHP_EOL,
                    $salida
                )
            );
        }

        if (filesize($rutaSalida) === 0) {
            throw new RuntimeException(
                'PDFtk generó un documento vacío.'
            );
        }

        return $rutaSalida;
    }
}