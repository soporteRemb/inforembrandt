<?php

namespace App\Http\Controllers;

use App\Models\DocumentoEstudiante;
use App\Models\Student;
use App\Services\DocumentosEstudiante\DocumentoEstudianteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class DocumentoEstudianteController extends Controller
{
    public function ver(
        DocumentoEstudiante $documento
    ): Response {

        $usuario = auth()->user();

        abort_unless(
            $usuario,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Seguridad
        |--------------------------------------------------------------------------
        |
        | Los documentos del expediente del estudiante son privados.
        | Por ahora aplicamos la misma regla administrativa utilizada
        | por los documentos de pre-matrícula.
        |
        */

        if (
            ! $usuario->hasAnyRole([
                'superadmin',
                'admin',
            ])
            && ! $usuario->can('ver_estudiantes')
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

    public function adjuntar(
        Request $request,
        Student $student,
        DocumentoEstudianteService $servicio
    ): RedirectResponse {

        $usuario = auth()->user();

        abort_unless(
            $usuario,
            403
        );

        if (
            ! $usuario->hasAnyRole([
                'superadmin',
                'admin',
            ])
            && ! $usuario->can('editar_estudiantes')
        ) {
            abort(403);
        }

        $request->validate([
            'tipo_documento' => [
                'required',
                'string',
            ],
            'documento' => [
                'required',
                'file',
            ],
        ]);

        $servicio->guardar(
            $student,
            (string) $request->input(
                'tipo_documento'
            ),
            $request->file('documento'),
            $usuario,
            'administrativo'
        );

        return back()->with(
            'success',
            'Documento adjuntado correctamente.'
        );
    }

    public function quitar(
        Student $student,
        DocumentoEstudiante $documento,
        DocumentoEstudianteService $servicio
    ): RedirectResponse {

        $usuario = auth()->user();

        abort_unless(
            $usuario,
            403
        );

        if (
            ! $usuario->hasAnyRole([
                'superadmin',
                'admin',
            ])
            && ! $usuario->can('editar_estudiantes')
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Evitar eliminar documentos de otro estudiante
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int) $documento->student_id
                === (int) $student->id,
            404
        );

        $servicio->eliminar(
            $documento
        );

        return back()->with(
            'success',
            'Documento retirado correctamente.'
        );
    }
}