<?php

namespace App\Services\PreMatriculas;

use App\Models\PreMatricula;
use App\Models\PreMatriculaDocumento;
use App\Models\User;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PreMatriculaDocumentoService
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

    public function tipoFormularioExiste(
        string $tipo
    ): bool {
        return array_key_exists(
            $tipo,
            $this->tiposFormulario()
        );
    }

    public function tiposFormulario(): array
    {
        return config(
            'pre_matricula_documentos.tipos_formulario',
            []
        );
    }

    public function nombreTipo(string $tipo): string
    {
        return (string) (
            $this->tipos()[$tipo]['nombre']
            ?? $tipo
        );
    }

    public function tipoExiste(string $tipo): bool
    {
        return array_key_exists(
            $tipo,
            $this->tipos()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar documento
    |--------------------------------------------------------------------------
    */

    public function guardar(
        PreMatricula $preMatricula,
        string $tipoDocumento,
        UploadedFile $archivo,
        User $usuario,
        string $origen = 'temporal'
    ): PreMatriculaDocumento {

        $this->validarTipoDocumento(
            $tipoDocumento
        );

        $this->validarOrigen(
            $origen
        );

        $this->validarArchivo(
            $archivo
        );

        /*
        |--------------------------------------------------------------------------
        | Carpeta privada por pre-matrícula
        |--------------------------------------------------------------------------
        |
        | Se usa el disco "local".
        | En Laravel 11 estándar esto queda fuera de public/.
        |
        */

        $directorio = sprintf(
            'pre-matriculas/%d/documentos',
            $preMatricula->id
        );

        /*
        |--------------------------------------------------------------------------
        | Nombre físico seguro
        |--------------------------------------------------------------------------
        |
        | No usamos el nombre original como nombre físico.
        | El nombre original sí queda guardado en BD.
        |
        */

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

        /*
        |--------------------------------------------------------------------------
        | Registrar en base de datos
        |--------------------------------------------------------------------------
        */

        return PreMatriculaDocumento::create([
            'pre_matricula_id' =>
                $preMatricula->id,

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
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar documento
    |--------------------------------------------------------------------------
    */

    public function eliminar(
        PreMatriculaDocumento $documento
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
    | Reemplazar documento
    |--------------------------------------------------------------------------
    */

    public function reemplazar(
        PreMatriculaDocumento $documento,
        UploadedFile $archivo,
        User $usuario
    ): PreMatriculaDocumento {

        $this->validarArchivo(
            $archivo
        );

        $preMatricula = $documento->preMatricula;

        $tipoDocumento =
            $documento->tipo_documento;

        $origen =
            $documento->origen;

        /*
        |--------------------------------------------------------------------------
        | Primero guardar el nuevo
        |--------------------------------------------------------------------------
        |
        | Esto evita perder el documento anterior si falla la nueva carga.
        |
        */

        $nuevoDocumento = $this->guardar(
            $preMatricula,
            $tipoDocumento,
            $archivo,
            $usuario,
            $origen
        );

        /*
        |--------------------------------------------------------------------------
        | Después eliminar el anterior
        |--------------------------------------------------------------------------
        */

        $this->eliminar(
            $documento
        );

        return $nuevoDocumento;
    }

    /*
    |--------------------------------------------------------------------------
    | Documentos de una pre-matrícula
    |--------------------------------------------------------------------------
    */

    public function documentos(
        PreMatricula $preMatricula
    ) {
        return $preMatricula
            ->documentos()
            ->with('usuarioCarga')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Información para previsualización
    |--------------------------------------------------------------------------
    */

    public function esImagen(
        PreMatriculaDocumento $documento
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
        PreMatriculaDocumento $documento
    ): bool {
        return strtolower(
            (string) $documento->mime_type
        ) === 'application/pdf';
    }

    /*
    |--------------------------------------------------------------------------
    | Validaciones internas
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

    private function validarOrigen(
        string $origen
    ): void {

        if (
            ! in_array(
                $origen,
                [
                    'temporal',
                    'administrativo',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'documento' =>
                    'El origen del documento no es válido.',
            ]);
        }
    }

    private function validarArchivo(
        UploadedFile $archivo
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Máximo 10 MB
        |--------------------------------------------------------------------------
        */

        if ($archivo->getSize() > 10 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'documento' =>
                    'El documento no puede superar los 10 MB.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Formatos permitidos
        |--------------------------------------------------------------------------
        */

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