<?php

namespace App\Services\FirmaElectronica;

use App\Models\FirmaElectronicaEstudiante;
use App\Services\DocumentosEstudiante\DocumentoEstudianteService;
use RuntimeException;

class ProcesarDocumentoFirmadoService
{
    public function __construct(
        private ClickAndSignService $clickAndSign,
        private DocumentoEstudianteService $documentos
    ) {
    }

    public function procesar(
        FirmaElectronicaEstudiante $firma
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Idempotencia
        |--------------------------------------------------------------------------
        */

        if ($firma->estado === 'firmada') {
            return;
        }

        $student = $firma->student;

        if (! $student) {
            throw new RuntimeException(
                'La solicitud de firma no tiene un estudiante asociado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar si el documento final ya existe
        |--------------------------------------------------------------------------
        |
        | Esto cubre también el caso en que el archivo se haya guardado
        | correctamente pero el proceso se interrumpa antes de actualizar
        | FirmaElectronicaEstudiante.
        |
        */

        $documentoExistente = $student
            ->archivosDocumentos()
            ->where(
                'tipo_documento',
                'matricula_electronica'
            )
            ->latest('id')
            ->first();

        if ($documentoExistente) {
            $firma->forceFill([
                'estado' => 'firmada',
                'firmada_en' =>
                    $firma->firmada_en ?: now(),
                'nombre_archivo_firmado' =>
                    $documentoExistente->nombre_original,
                'ultimo_error' => null,
            ])->save();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Necesitamos signature_id
        |--------------------------------------------------------------------------
        */

        $signatureId = trim(
            (string) $firma->signature_id
        );

        if ($signatureId === '') {
            throw new RuntimeException(
                'La solicitud no tiene signature_id de Click & Sign.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Consultar únicamente los documentos SIGNED
        |--------------------------------------------------------------------------
        */

        $respuesta = $this->clickAndSign
            ->obtenerListaArchivos(
                $signatureId,
                'SIGNED'
            );

        $archivos = $respuesta['file_list']['files']
            ?? [];

        /*
        |--------------------------------------------------------------------------
        | Confirmar el grupo recibido
        |--------------------------------------------------------------------------
        */

        $firmados = array_values(
            array_filter(
                $archivos,
                static fn (array $archivo): bool =>
                    strtoupper(
                        trim(
                            (string) (
                                $archivo['file_group']
                                ?? ''
                            )
                        )
                    ) === 'SIGNED'
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Nuestra operación envía exactamente un PDF unificado
        |--------------------------------------------------------------------------
        */

        if (count($firmados) !== 1) {
            throw new RuntimeException(
                sprintf(
                    'Se esperaba exactamente un archivo SIGNED y Click & Sign devolvió %d.',
                    count($firmados)
                )
            );
        }

        $archivo = $firmados[0];

        $fileId = (int) (
            $archivo['file_id']
            ?? 0
        );

        if ($fileId <= 0) {
            throw new RuntimeException(
                'Click & Sign no devolvió un file_id válido para el documento firmado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Descargar PDF final
        |--------------------------------------------------------------------------
        */

        $contenidoPdf = $this->clickAndSign
            ->obtenerArchivo(
                $fileId
            );

        /*
        |--------------------------------------------------------------------------
        | Guardar documento canónico
        |--------------------------------------------------------------------------
        |
        | El servicio DocumentoEstudianteService vuelve a comprobar
        | idempotencia y valida que el contenido sea realmente un PDF.
        |
        */

        $documento = $this->documentos
            ->guardarMatriculaElectronicaFirmada(
                $student,
                $contenidoPdf,
                null,
                isset($archivo['filename'])
                    ? (string) $archivo['filename']
                    : null
            );

        /*
        |--------------------------------------------------------------------------
        | La firma solo queda finalizada después de guardar el PDF
        |--------------------------------------------------------------------------
        */

        $firma->forceFill([
            'estado' => 'firmada',
            'firmada_en' => now(),
            'nombre_archivo_firmado' =>
                $documento->nombre_original,
            'ultimo_error' => null,
        ])->save();
    }
}