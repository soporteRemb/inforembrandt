<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentDocumento;


use App\Services\DocumentosEstudiante\ContratoServicioPdf;
use App\Services\DocumentosEstudiante\FormatoComportamientoPdf;
use App\Services\DocumentosEstudiante\PagarePdf;
use App\Services\DocumentosEstudiante\AutorizacionConsultaPdf;
use App\Services\DocumentosEstudiante\AutorizacionUsoPagarePdf;
use App\Services\DocumentosEstudiante\AutorizacionDatosPersonalesPdf;
use App\Services\DocumentosEstudiante\ResolverPlantillaPdf;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use RuntimeException;

class StudentPdfController extends Controller
{
    public function preMatricula(Request $request, Student $student)
    {
        $student->load(['guardians', 'course', 'periodoLectivo', 'sede']);

        $revisor  = Auth::user()->name ?? 'Administrador';
        $logoPath = public_path('images/logo.png');
        $logoB64  = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        // Fecha = hoy (auditoría automática, no editable)
        $fecha = now()->format('Y-m-d');

        // Marcar como generado
        $student->documentos()->updateOrCreate(
            ['tipo' => 'pre_matricula'],
            ['generado_at' => now(), 'generado_por' => $revisor]
        );

        $historial = $this->getHistorial($student);

        $pdf = Pdf::loadView('pdf.pre-matricula', compact('student', 'fecha', 'revisor', 'logoB64', 'historial'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream('pre-matricula-' . $student->codigo . '.pdf');
    }

    public function hojaMatricula(Request $request, Student $student)
    {
        $student->load(['guardians', 'course', 'periodoLectivo', 'sede']);

        $revisor  = Auth::user()->name ?? 'Administrador';
        $fecha    = now()->format('Y-m-d'); // auditoría automática

        // Marcar como generado
        $student->documentos()->updateOrCreate(
            ['tipo' => 'hoja_matricula'],
            ['generado_at' => now(), 'generado_por' => $revisor]
        );
        $logoPath = public_path('images/logo.png');
        $logoB64  = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $historial = $this->getHistorial($student);

        $pdf = Pdf::loadView('pdf.hoja-matricula', compact('student', 'fecha', 'revisor', 'logoB64', 'historial'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream('hoja-matricula-' . $student->codigo . '.pdf');
    }

    // ── Página de documentos ──────────────────────────────────────────────────
    public function documentos(Student $student)
    {
        $student->load([
            'guardians',
            'course',
            'periodoLectivo',
            'sede',
            'documentos',
        ]);

        $documentoFirma = $student
            ->archivosDocumentos()
            ->where(
                'tipo_documento',
                'matricula_electronica'
            )
            ->latest()
            ->first();

        $firmaElectronica = $student
            ->firmasElectronicas()
            ->latest()
            ->first();

        return view(
            'documentos.estudiante',
            compact(
                'student',
                'documentoFirma',
                'firmaElectronica'
            )
        );
    }

    // ── PDF genérico para los 6 formatos ────────────────────────────────────
    public function formato(
        Request $request,
        Student $student,
        string $tipo
    ) {
        $todos = StudentDocumento::todos();

        if (!isset($todos[$tipo])) {
            abort(404);
        }

        /*
        * El contrato utiliza su plantilla AcroForm original.
        * Prematrícula y hoja de matrícula no se modifican.
        */
        if ($tipo === 'contrato_servicios') {
            return app(ContratoServicioPdf::class)
                ->generar($student);
        }

        if ($tipo === 'comportamiento') {
            return app(FormatoComportamientoPdf::class)
                ->generar($student);
        }

        if ($tipo === 'pagare') {
            return app(PagarePdf::class)
                ->generar($student);
        }

        if ($tipo === 'autorizacion_consulta') {
            return app(AutorizacionConsultaPdf::class)
                ->generar($student);
        }

        if ($tipo === 'autorizacion_pagare') {
            return app(AutorizacionUsoPagarePdf::class)
                ->generar($student);
        }

        if ($tipo === 'autorizacion_datos') {
            return app(AutorizacionDatosPersonalesPdf::class)
                ->generar($student);
        }

        /*
        * Los demás formatos continúan con el comportamiento
        * que ya tenían.
        */
        $student->load([
            'guardians',
            'course',
            'periodoLectivo',
            'sede',
        ]);

        $doc      = $todos[$tipo];
        $logoPath = public_path('images/logo.png');

        $logoB64 = file_exists($logoPath)
            ? 'data:image/png;base64,'
                . base64_encode(file_get_contents($logoPath))
            : null;

        $fecha   = now()->format('d/m/Y');
        $revisor = Auth::user()->name ?? 'Administrador';

        $pdf = Pdf::loadView(
            'pdf.formato',
            compact(
                'student',
                'doc',
                'tipo',
                'fecha',
                'revisor',
                'logoB64'
            )
        )->setPaper('letter', 'portrait');

        return $pdf->stream(
            str_replace(
                ' ',
                '-',
                strtolower($doc['label'])
            )
            . '-'
            . $student->codigo
            . '.pdf'
        );
    }

    public function plantillaDocumento(
        string $tipo,
        ResolverPlantillaPdf $resolver
    ) {
        $ruta = $resolver->obtenerRuta($tipo);

        return response()->file(
            $ruta,
            [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
                'Pragma' => 'no-cache',
            ]
        );
    }
    // ── Paquete de los 6 documentos PDF ──────────────────────────────────────
    public function paquete(Student $student)
    {
        $student->load([
            'guardians',
            'course',
            'periodoLectivo',
            'sede',
        ]);

        $primerNombre = mb_strtoupper(
            trim((string) $student->primer_nombre)
        );

        $primerApellido = mb_strtoupper(
            trim((string) $student->primer_apellido)
        );

        $nombreArchivo = 'DOCUMENTOS-'
            . $primerNombre
            . '-'
            . $primerApellido
            . '.pdf';

        return view(
            'documentos.paquete-documentos',
            [
                'student' => $student,
                'nombreArchivo' => $nombreArchivo,
            ]
        );
    }

    // ── Toggle marcar/desmarcar documento digital ────────────────────────────
    public function toggleDocumento(Student $student, string $tipo): \Illuminate\Http\JsonResponse
    {
        $tipos = array_keys(\App\Models\StudentDocumento::todos());

        if (!in_array($tipo, $tipos)) {
            return response()->json(['error' => 'Tipo inválido'], 422);
        }

        $existente = $student->documentos()->where('tipo', $tipo)->first();

        if ($existente) {
            $existente->delete();
            return response()->json(['ok' => true, 'estado' => 'desmarcado']);
        }

        $student->documentos()->create([
            'tipo'         => $tipo,
            'generado_at'  => now(),
            'generado_por' => Auth::user()->name ?? 'Administrador',
        ]);

        return response()->json(['ok' => true, 'estado' => 'marcado', 'quien' => Auth::user()->name, 'cuando' => now()->format('d/m/Y H:i')]);
    }


    public static function itemsFisicos(): array
    {
        return collect(
            config(
                'pre_matricula_documentos.tipos',
                []
            )
        )
            ->mapWithKeys(
                function ($configuracion, $tipo) {
                    return [
                        $tipo => $configuracion['nombre']
                            ?? $tipo,
                    ];
                }
            )
            ->all();
    }

    public function documentosFisicos(
        Student $student
    ): \Illuminate\View\View {

        $student->load([
            'course',
            'periodoLectivo',
            'sede',
            'archivosDocumentos.usuarioCarga',
        ]);

        $items = self::itemsFisicos();

        /*
        |--------------------------------------------------------------------------
        | Documentos reales agrupados por tipo
        |--------------------------------------------------------------------------
        */

        $documentosPorTipo = $student
            ->archivosDocumentos
            ->groupBy('tipo_documento');

        /*
        |--------------------------------------------------------------------------
        | Tipos entregados
        |--------------------------------------------------------------------------
        */

        $checked = $documentosPorTipo
            ->keys()
            ->values()
            ->all();

        return view(
            'documentos.fisicos',
            compact(
                'student',
                'items',
                'checked',
                'documentosPorTipo'
            )
        );
    }


    private function getHistorial(Student $student): array
    {
        $historial = [];

        // Años anteriores en Rembrandt (mismo documento, otros periodos)
        $previos = Student::where('documento', $student->documento)
            ->where('id', '!=', $student->id)
            ->with(['course', 'periodoLectivo'])
            ->orderBy('id')
            ->get();

        foreach ($previos as $prev) {
            $historial[] = [
                'grado'      => $prev->course ? ($prev->course->grado . '° - ' . $prev->course->curso) : ($prev->ultimo_grado ?? '-'),
                'anio'       => $prev->periodoLectivo?->nombre ?? '-',
                'institucion' => 'Colegio Rembrandt',
            ];
        }

        // Institución anterior (antes de llegar a Rembrandt)
        if (!empty($student->institucion_anterior) && !empty($student->anio_ultimo_grado)) {
            $historial[] = [
                'grado'      => $student->ultimo_grado ?? '-',
                'anio'       => $student->anio_ultimo_grado,
                'institucion' => $student->institucion_anterior,
            ];
        }

        return $historial;
    }

    public function unirPaquete(
        Request $request,
        Student $student
    ) {
        $request->validate([
            'contrato' => [
                'required',
                'file',
                'mimetypes:application/pdf',
            ],

            'comportamiento' => [
                'required',
                'file',
                'mimetypes:application/pdf',
            ],

            'pagare' => [
                'required',
                'file',
                'mimetypes:application/pdf',
            ],

            'autorizacion_consulta' => [
                'required',
                'file',
                'mimetypes:application/pdf',
            ],

            'autorizacion_pagare' => [
                'required',
                'file',
                'mimetypes:application/pdf',
            ],

            'autorizacion_datos' => [
                'required',
                'file',
                'mimetypes:application/pdf',
            ],
        ]);

        $primerNombre = mb_strtoupper(
            trim((string) $student->primer_nombre)
        );

        $primerApellido = mb_strtoupper(
            trim((string) $student->primer_apellido)
        );

        $nombreArchivo = 'DOCUMENTOS-'
            . $primerNombre
            . '-'
            . $primerApellido
            . '.pdf';

        $directorioTemporal = storage_path(
            'app/private/tmp_documentos/'
            . Str::uuid()
        );

        File::ensureDirectoryExists(
            $directorioTemporal
        );

        $rutaContrato =
            $directorioTemporal
            . DIRECTORY_SEPARATOR
            . 'contrato.pdf';

        $rutaComportamiento =
            $directorioTemporal
            . DIRECTORY_SEPARATOR
            . 'comportamiento.pdf';

        $rutaPagare =
            $directorioTemporal
            . DIRECTORY_SEPARATOR
            . 'pagare.pdf';

        $rutaAutorizacionConsulta =
            $directorioTemporal
            . DIRECTORY_SEPARATOR
            . 'autorizacion-consulta.pdf';

        $rutaAutorizacionPagare =
            $directorioTemporal
            . DIRECTORY_SEPARATOR
            . 'autorizacion-pagare.pdf';

        $rutaAutorizacionDatos =
            $directorioTemporal
            . DIRECTORY_SEPARATOR
            . 'autorizacion-datos.pdf';

        $rutaSalida =
            $directorioTemporal
            . DIRECTORY_SEPARATOR
            . 'documentos.pdf';

        try {
            $request
                ->file('contrato')
                ->move(
                    $directorioTemporal,
                    'contrato.pdf'
                );

            $request
                ->file('comportamiento')
                ->move(
                    $directorioTemporal,
                    'comportamiento.pdf'
                );
            $request
                ->file('pagare')
                ->move(
                    $directorioTemporal,
                    'pagare.pdf'
                );
            $request
                ->file('autorizacion_consulta')
                ->move(
                    $directorioTemporal,
                    'autorizacion-consulta.pdf'
                );

            $request
            ->file('autorizacion_pagare')
            ->move(
                $directorioTemporal,
                'autorizacion-pagare.pdf'
            );

            $request
            ->file('autorizacion_datos')
            ->move(
                $directorioTemporal,
                'autorizacion-datos.pdf'
            );

            $qpdf = config(
                'services.qpdf.binary',
                'qpdf'
            );

            $comando = sprintf(
                '"%s" --empty --pages "%s" 1-z "%s" 1-z "%s" 1-z "%s" 1-z "%s" 1-z "%s" 1-z -- "%s" 2>&1',
                $qpdf,
                $rutaContrato,
                $rutaComportamiento,
                $rutaPagare,
                $rutaAutorizacionConsulta,
                $rutaAutorizacionPagare,
                $rutaAutorizacionDatos,
                $rutaSalida
            );

            exec(
                $comando,
                $salida,
                $codigo
            );

            if (
                $codigo !== 0
                || !is_file($rutaSalida)
            ) {
                throw new RuntimeException(
                    'QPDF no pudo unir los documentos. '
                    . implode(
                        PHP_EOL,
                        $salida
                    )
                );
            }

            $contenido = file_get_contents(
                $rutaSalida
            );

            return response(
                $contenido,
                200,
                [
                    'Content-Type' =>
                        'application/pdf',

                    'Content-Disposition' =>
                        'inline; filename="'
                        . $nombreArchivo
                        . '"',

                    'Cache-Control' =>
                        'private, no-store, no-cache, must-revalidate',

                    'Pragma' =>
                        'no-cache',
                ]
            );
        } finally {
            File::deleteDirectory(
                $directorioTemporal
            );
        }
    }
}
