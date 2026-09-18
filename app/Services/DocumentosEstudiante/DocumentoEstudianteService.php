<?php

namespace App\Services\DocumentosEstudiante;

use App\Models\DocumentoEstudiante;
use App\Models\PreMatriculaDocumento;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentoEstudianteService
{
    /*
    |--------------------------------------------------------------------------
    | Catálogo de documentos
    |--------------------------------------------------------------------------
    */

    public function tipos(): array
    {
        return config(
            'pre_matricula_documentos.tipos',
            []
        );
    }

    public function tipoExiste(string $tipo): bool
    {
        return array_key_exists(
            $tipo,
            $this->tipos()
        );
    }

    public function nombreTipo(string $tipo): string
    {
        return (string) (
            $this->tipos()[$tipo]['nombre']
            ?? $tipo
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar documento nuevo del estudiante
    |--------------------------------------------------------------------------
    */

    public function guardar(
        Student $student,
        string $tipoDocumento,
        UploadedFile $archivo,
        User $usuario,
        string $origen = 'administrativo'
    ): DocumentoEstudiante {

        $this->validarTipoDocumento(
            $tipoDocumento
        );

        $this->validarArchivo(
            $archivo
        );

        $directorio = sprintf(
            'estudiantes/%d/documentos',
            $student->id
        );

        $extension = strtolower(
            $archivo->getClientOriginalExtension()
        );

        $nombreFisico = sprintf(
            '%s_%s.%s',
            $tipoDocumento,
            Str::uuid(),
            $extension
        );

        $ruta = $archivo->storeAs(
            $directorio,
            $nombreFisico,
            'local'
        );

        if (! $ruta) {
            throw ValidationException::withMessages([
                'documento' =>
                    'No fue posible guardar el documento.',
            ]);
        }

        try {
            return DocumentoEstudiante::create([
                'student_id' =>
                    $student->id,

                'tipo_documento' =>
                    $tipoDocumento,

                'nombre_original' =>
                    $archivo->getClientOriginalName(),

                'ruta_archivo' =>
                    $ruta,

                'mime_type' =>
                    $archivo->getMimeType(),

                'tamano' =>
                    $archivo->getSize(),

                'origen' =>
                    $origen,

                'subido_por' =>
                    $usuario->id,

                'pre_matricula_documento_id' =>
                    null,
            ]);
        } catch (\Throwable $e) {
            if (
                Storage::disk('local')->exists(
                    $ruta
                )
            ) {
                Storage::disk('local')->delete(
                    $ruta
                );
            }

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar matrícula electrónica firmada
    |--------------------------------------------------------------------------
    |
    | Guarda el PDF final recibido directamente desde Click & Sign.
    |
    | Esta operación es idempotente: si el estudiante ya tiene una
    | matrícula electrónica registrada, devuelve ese mismo documento
    | y no crea una segunda copia.
    |
    */

    public function guardarMatriculaElectronicaFirmada(
        Student $student,
        string $contenidoPdf,
        ?User $usuario = null,
        ?string $nombreOriginal = null
    ): DocumentoEstudiante {

        $tipoDocumento = 'matricula_electronica';

        $this->validarTipoDocumento(
            $tipoDocumento
        );

        /*
        |--------------------------------------------------------------------------
        | Validar PDF recibido
        |--------------------------------------------------------------------------
        */

        if (
            $contenidoPdf === ''
            || ! str_starts_with(
                ltrim($contenidoPdf),
                '%PDF-'
            )
        ) {
            throw ValidationException::withMessages([
                'documento' =>
                    'Click & Sign no devolvió un archivo PDF válido.',
            ]);
        }

        if (
            strlen($contenidoPdf)
            > 25 * 1024 * 1024
        ) {
            throw ValidationException::withMessages([
                'documento' =>
                    'El PDF firmado supera el tamaño máximo permitido.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Operación idempotente y protegida contra concurrencia
        |--------------------------------------------------------------------------
        */

        return DB::transaction(function () use (
            $student,
            $contenidoPdf,
            $usuario,
            $nombreOriginal,
            $tipoDocumento
        ) {

            /*
            |--------------------------------------------------------------------------
            | Bloquear la fila del estudiante
            |--------------------------------------------------------------------------
            |
            | Dos Jobs del mismo estudiante no pueden superar simultáneamente
            | la comprobación de existencia.
            |
            */

            Student::query()
                ->whereKey($student->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Comprobar documento canónico existente
            |--------------------------------------------------------------------------
            */

            $existente = DocumentoEstudiante::query()
                ->where(
                    'student_id',
                    $student->id
                )
                ->where(
                    'tipo_documento',
                    $tipoDocumento
                )
                ->latest('id')
                ->first();

            if ($existente) {
                return $existente;
            }

            /*
            |--------------------------------------------------------------------------
            | Preparar ruta privada
            |--------------------------------------------------------------------------
            */

            $directorio = sprintf(
                'estudiantes/%d/documentos',
                $student->id
            );

            $nombreFisico = sprintf(
                '%s_%s.pdf',
                $tipoDocumento,
                Str::uuid()
            );

            $ruta = $directorio
                . '/'
                . $nombreFisico;

            /*
            |--------------------------------------------------------------------------
            | Guardar archivo
            |--------------------------------------------------------------------------
            */

            $guardado = Storage::disk('local')->put(
                $ruta,
                $contenidoPdf
            );

            if (! $guardado) {
                throw ValidationException::withMessages([
                    'documento' =>
                        'No fue posible guardar el PDF firmado.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Registrar documento
            |--------------------------------------------------------------------------
            */

            try {
                return DocumentoEstudiante::create([
                    'student_id' =>
                        $student->id,

                    'tipo_documento' =>
                        $tipoDocumento,

                    'nombre_original' =>
                        $nombreOriginal
                        ?: 'MATRICULA-ELECTRONICA-FIRMADA.pdf',

                    'ruta_archivo' =>
                        $ruta,

                    'mime_type' =>
                        'application/pdf',

                    'tamano' =>
                        strlen($contenidoPdf),

                    'origen' =>
                        'firma_electronica',

                    'subido_por' =>
                        $usuario?->id,

                    'pre_matricula_documento_id' =>
                        null,
                ]);
            } catch (\Throwable $e) {
                /*
                |--------------------------------------------------------------------------
                | La transacción revierte BD, pero Storage no es transaccional
                |--------------------------------------------------------------------------
                */

                if (
                    Storage::disk('local')->exists(
                        $ruta
                    )
                ) {
                    Storage::disk('local')->delete(
                        $ruta
                    );
                }

                throw $e;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Incorporar documento proveniente de pre-matrícula
    |--------------------------------------------------------------------------
    |
    | Se COPIA el archivo al expediente definitivo del estudiante.
    | El archivo original de la pre-matrícula permanece intacto.
    |
    */

    public function incorporarDesdePreMatricula(
        Student $student,
        PreMatriculaDocumento $documento
    ): DocumentoEstudiante {

        /*
        |--------------------------------------------------------------------------
        | Evitar duplicados
        |--------------------------------------------------------------------------
        */

        $existente = DocumentoEstudiante::query()
            ->where(
                'student_id',
                $student->id
            )
            ->where(
                'pre_matricula_documento_id',
                $documento->id
            )
            ->first();

        if ($existente) {
            return $existente;
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar archivo original
        |--------------------------------------------------------------------------
        */

        if (
            blank($documento->ruta_archivo)
            || ! Storage::disk('local')->exists(
                $documento->ruta_archivo
            )
        ) {
            throw ValidationException::withMessages([
                'documento' =>
                    'El archivo original de la pre-matrícula no existe.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Crear carpeta definitiva del estudiante
        |--------------------------------------------------------------------------
        */

        $directorio = sprintf(
            'estudiantes/%d/documentos',
            $student->id
        );

        /*
        |--------------------------------------------------------------------------
        | Conservar extensión original
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Copiar archivo
        |--------------------------------------------------------------------------
        */

        $copiado = Storage::disk('local')->copy(
            $documento->ruta_archivo,
            $rutaDestino
        );

        if (! $copiado) {
            throw ValidationException::withMessages([
                'documento' =>
                    'No fue posible incorporar el documento al expediente del estudiante.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Registrar documento definitivo
        |--------------------------------------------------------------------------
        */

        try {
            return DocumentoEstudiante::create([
                'student_id' =>
                    $student->id,

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

                'origen' =>
                    'pre_matricula',

                'subido_por' =>
                    $documento->subido_por,

                'pre_matricula_documento_id' =>
                    $documento->id,
            ]);
        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Si falla BD, eliminar solamente la copia nueva
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

    /*
    |--------------------------------------------------------------------------
    | Eliminar documento del expediente
    |--------------------------------------------------------------------------
    |
    | Solamente elimina la copia perteneciente al estudiante.
    | Nunca elimina el archivo original de la pre-matrícula.
    |
    */

    public function eliminar(
        DocumentoEstudiante $documento
    ): void {

        if (
            filled($documento->ruta_archivo)
            && Storage::disk('local')->exists(
                $documento->ruta_archivo
            )
        ) {
            Storage::disk('local')->delete(
                $documento->ruta_archivo
            );
        }

        $documento->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Documentos del estudiante
    |--------------------------------------------------------------------------
    */

    public function documentos(
        Student $student
    ) {
        return $student
            ->archivosDocumentos()
            ->with('usuarioCarga')
            ->latest()
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Utilidades de previsualización
    |--------------------------------------------------------------------------
    */

    public function esImagen(
        DocumentoEstudiante $documento
    ): bool {
        return in_array(
            strtolower(
                (string) $documento->mime_type
            ),
            [
                'image/jpeg',
                'image/png',
                'image/webp',
            ],
            true
        );
    }

    public function esPdf(
        DocumentoEstudiante $documento
    ): bool {
        return strtolower(
            (string) $documento->mime_type
        ) === 'application/pdf';
    }

    /*
    |--------------------------------------------------------------------------
    | Validaciones
    |--------------------------------------------------------------------------
    */

    private function validarTipoDocumento(
        string $tipoDocumento
    ): void {

        if (! $this->tipoExiste($tipoDocumento)) {
            throw ValidationException::withMessages([
                'tipo_documento' =>
                    'El tipo de documento seleccionado no es válido.',
            ]);
        }
    }

    private function validarArchivo(
        UploadedFile $archivo
    ): void {

        if ($archivo->getSize() > 10 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'documento' =>
                    'El documento no puede superar los 10 MB.',
            ]);
        }

        $mimePermitidos = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        if (
            ! in_array(
                strtolower(
                    (string) $archivo->getMimeType()
                ),
                $mimePermitidos,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'documento' =>
                    'Solo se permiten archivos PDF, JPG, JPEG, PNG o WebP.',
            ]);
        }
    }
}