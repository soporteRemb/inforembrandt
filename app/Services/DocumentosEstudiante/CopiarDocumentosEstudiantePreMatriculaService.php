<?php

namespace App\Services\DocumentosEstudiante;

use App\Models\DocumentoEstudiante;
use App\Models\PreMatricula;
use App\Models\PreMatriculaDocumento;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CopiarDocumentosEstudiantePreMatriculaService
{
    public function ejecutar(
        Student $student,
        PreMatricula $preMatricula
    ): array {
        $documentos = $student
            ->archivosDocumentos()
            ->orderBy('id')
            ->get();

        $copiados = 0;
        $yaExistentes = 0;

        /*
        |--------------------------------------------------------------------------
        | Rutas creadas durante esta ejecución
        |--------------------------------------------------------------------------
        |
        | La transacción de MySQL puede revertir los registros de base de datos,
        | pero no puede revertir archivos físicos. Por eso conservamos las rutas
        | creadas en esta ejecución para poder limpiarlas si una copia posterior
        | falla.
        |
        */

        $rutasCreadas = [];

        try {
            foreach ($documentos as $documento) {
                $existe = $preMatricula
                    ->documentos()
                    ->where('tipo_documento', $documento->tipo_documento)
                    ->where('nombre_original', $documento->nombre_original)
                    ->where('tamano', $documento->tamano)
                    ->where('mime_type', $documento->mime_type)
                    ->exists();

                if ($existe) {
                    $yaExistentes++;
                    continue;
                }

                $documentoCopiado = $this->copiarDocumento(
                    $documento,
                    $preMatricula
                );

                $rutasCreadas[] = $documentoCopiado->ruta_archivo;

                $copiados++;
            }
        } catch (\Throwable $e) {
            /*
            |--------------------------------------------------------------------------
            | Limpiar únicamente las copias creadas en esta ejecución
            |--------------------------------------------------------------------------
            |
            | Nunca se eliminan los documentos originales del estudiante ni los
            | documentos que ya existían previamente en la pre-matrícula.
            |
            */

            foreach ($rutasCreadas as $ruta) {
                if (
                    filled($ruta)
                    && Storage::disk('local')->exists($ruta)
                ) {
                    Storage::disk('local')->delete($ruta);
                }
            }

            throw $e;
        }

        return [
            'documentos_encontrados' => $documentos->count(),
            'documentos_copiados' => $copiados,
            'documentos_ya_existentes' => $yaExistentes,
        ];
    }

    private function copiarDocumento(
        DocumentoEstudiante $documento,
        PreMatricula $preMatricula
    ): PreMatriculaDocumento {
        if (
            blank($documento->ruta_archivo)
            || ! Storage::disk('local')->exists(
                $documento->ruta_archivo
            )
        ) {
            throw ValidationException::withMessages([
                'documento' =>
                    'El archivo original del expediente del estudiante no existe.',
            ]);
        }

        $directorio = sprintf(
            'pre-matriculas/%d/documentos',
            $preMatricula->id
        );

        $extension = strtolower(
            pathinfo(
                $documento->ruta_archivo,
                PATHINFO_EXTENSION
            )
        );

        $nombreFisico = sprintf(
            '%s_%s%s',
            $documento->tipo_documento,
            Str::uuid(),
            $extension !== ''
                ? '.' . $extension
                : ''
        );

        $rutaDestino = $directorio
            . '/'
            . $nombreFisico;

        $copiado = Storage::disk('local')->copy(
            $documento->ruta_archivo,
            $rutaDestino
        );

        if (! $copiado) {
            throw ValidationException::withMessages([
                'documento' =>
                    'No fue posible copiar el documento del expediente a la pre-matrícula.',
            ]);
        }

        try {
            return PreMatriculaDocumento::create([
                'pre_matricula_id' =>
                    $preMatricula->id,

                'tipo_documento' =>
                    $documento->tipo_documento,

                'nombre_original' =>
                    $documento->nombre_original,

                'ruta_archivo' =>
                    $rutaDestino,

                'mime_type' =>
                    $documento->mime_type,

                'tamano' =>
                    $documento->tamano,

                /*
                |--------------------------------------------------------------------------
                | Origen
                |--------------------------------------------------------------------------
                |
                | Usamos "administrativo" porque es uno de los orígenes
                | actualmente admitidos y el archivo proviene del expediente
                | institucional, no de una carga realizada por la familia.
                |
                */

                'origen' =>
                    'administrativo',

                'subido_por' =>
                    $documento->subido_por,
            ]);
        } catch (\Throwable $e) {
            /*
            |--------------------------------------------------------------------------
            | Si falla BD, eliminar únicamente la copia nueva
            |--------------------------------------------------------------------------
            */

            if (
                Storage::disk('local')->exists(
                    $rutaDestino
                )
            ) {
                Storage::disk('local')->delete(
                    $rutaDestino
                );
            }

            throw $e;
        }
    }
}