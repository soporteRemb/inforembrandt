<?php

namespace App\Http\Controllers;

use App\Models\PreMatriculaDocumento;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PreMatriculaDocumentoController extends Controller
{
    public function ver(
        PreMatriculaDocumento $documento
    ): Response {

        $usuario = auth()->user();

        abort_unless(
            $usuario,
            403
        );

        $documento->loadMissing('preMatricula');

        $preMatricula = $documento->preMatricula;

        abort_unless(
            $preMatricula,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Usuario temporal
        |--------------------------------------------------------------------------
        |
        | Solo puede visualizar documentos de su propia pre-matrícula.
        |
        */

        if ($usuario->hasRole('temporal')) {

            abort_unless(
                (int) $preMatricula->user_id
                    === (int) $usuario->id,
                403
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Usuarios administrativos
        |--------------------------------------------------------------------------
        |
        | Por ahora permitimos únicamente los roles administrativos que
        | trabajan actualmente con el módulo.
        |
        */

        elseif (
            ! $usuario->hasAnyRole([
                'superadmin',
                'admin',
                'admisiones',
            ])
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar archivo privado
        |--------------------------------------------------------------------------
        */

        abort_unless(
            filled($documento->ruta_archivo)
            && Storage::disk('local')->exists(
                $documento->ruta_archivo
            ),
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Entregar archivo al navegador
        |--------------------------------------------------------------------------
        */

        $contenido = Storage::disk('local')->get(
            $documento->ruta_archivo
        );

        return response(
            $contenido,
            200,
            [
                'Content-Type' =>
                    $documento->mime_type
                    ?: 'application/octet-stream',

                'Content-Disposition' =>
                    'inline; filename="' .
                    addslashes(
                        $documento->nombre_original
                    ) .
                    '"',

                'X-Content-Type-Options' =>
                    'nosniff',

                'Cache-Control' =>
                    'private, no-store, no-cache, must-revalidate',
            ]
        );
    }
}