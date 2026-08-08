<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentDocumento;


use App\Services\DocumentosEstudiante\ContratoServicioPdf;


use Barryvdh\DomPDF\Facade\Pdf;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPdfController extends Controller
{
    public function preMatricula(Request $request, Student $student)
    {
        $student->load(['guardians', 'course', 'periodoLectivo', 'sede']);

        $revisor  = Auth::user()->name ?? 'Administrador';
        $logoPath = public_path('images/Logo.png');
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
        $logoPath = public_path('images/Logo.png');
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
        $student->load(['guardians', 'course', 'periodoLectivo', 'sede', 'documentos']);
        return view('documentos.estudiante', compact('student'));
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
        $logoPath = public_path('images/Logo.png');

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

    // ── Documentos físicos (checklist 14 ítems) ──────────────────────────────
    public static function itemsFisicos(): array
    {
        return [
            'fotocopia_documento'  => 'Fotocopia del documento de identidad',
            'registro_civil'       => 'Registro civil de nacimiento',
            'cert_salud'           => 'Certificado de afiliación a salud (EPS)',
            'cert_estudios'        => 'Certificados de estudios anteriores',
            'acta_grado'           => 'Acta o diploma de grado (si aplica)',
            'formulario_matricula' => 'Formulario de matrícula firmado',
            'fotografias'          => 'Fotografías tipo documento',
            'pago_matricula'       => 'Comprobante de pago de matrícula',
            'carne_vacunacion'     => 'Carné de vacunación',
            'doc_acudiente'        => 'Copia del documento del acudiente',
            'contrato'             => 'Contrato o compromiso estudiantil firmado',
            'cert_residencia'      => 'Certificado de residencia',
            'autorizacion_datos'   => 'Autorización de uso de datos personales',
            'examen_medico'        => 'Exámenes médicos o aptitud física',
        ];
    }

    public function documentosFisicos(Student $student): \Illuminate\View\View
    {
        $student->load(['course', 'periodoLectivo', 'sede']);
        $items   = self::itemsFisicos();
        $checked = $student->documentos_fisicos ?? [];
        return view('documentos.fisicos', compact('student', 'items', 'checked'));
    }

    public function toggleDocumentoFisico(Student $student, string $item): \Illuminate\Http\JsonResponse
    {
        if (!array_key_exists($item, self::itemsFisicos())) {
            return response()->json(['error' => 'Ítem inválido'], 422);
        }
        $docs = $student->documentos_fisicos ?? [];
        if (in_array($item, $docs)) {
            $docs = array_values(array_filter($docs, fn($d) => $d !== $item));
        } else {
            $docs[] = $item;
        }
        $student->update(['documentos_fisicos' => $docs]);
        return response()->json(['ok' => true, 'checked' => in_array($item, $docs)]);
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
}
