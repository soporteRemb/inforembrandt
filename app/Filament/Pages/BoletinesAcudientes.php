<?php

namespace App\Filament\Pages;

use App\Models\BoletinRecomendacion;
use App\Models\NotaEstudiante;
use App\Models\PensumAcademico;
use App\Models\PeriodoAcademico;
use App\Models\RangoDesempenoNota;
use App\Models\Student;
use App\Models\Guardian;

use App\Services\Boletines\BoletinDataService;
use App\Services\Boletines\BoletinPdfService;
use Filament\Notifications\Notification;

use Filament\Pages\Page;


use Illuminate\Support\Facades\Storage;

class BoletinesAcudientes extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.boletines-acudientes';
    protected static ?string $navigationGroup = 'Portal del Acudiente';
    protected static ?string $navigationLabel = 'Boletines';
    protected static ?string $title = 'Boletines';
    protected ?string $maxContentWidth = 'full';
    protected static ?int $navigationSort = 1;

    public ?int $periodoAcademicoId = null;
    public ?int $studentId = null;

    public array $periodosAcademicos = [];
    public array $estudiantes = [];
    public array $rangosDesempeno = [];
    public array $desempenoAcademico = [];
    public array $actividadesMejoramiento = [];

    public ?Student $student = null;
    public ?PeriodoAcademico $periodoAcademicoSeleccionado = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Acudiente', 'acudiente']) ?? false;
    }

    public function mount(): void
    {
        $this->cargarRangosDesempeno();

        $this->cargarEstudiantesAcudiente();

        if (! empty($this->estudiantes)) {
            $this->studentId = (int) array_key_first($this->estudiantes);

            $this->cargarEstudianteSeleccionado();
        }
    }

    public function updatedStudentId(): void
    {
        $this->periodoAcademicoId = null;
        $this->periodoAcademicoSeleccionado = null;
        $this->periodosAcademicos = [];
        $this->desempenoAcademico = [];
        $this->actividadesMejoramiento = [];

        $this->cargarEstudianteSeleccionado();
    }

    public function updatedPeriodoAcademicoId(): void
    {
        $this->cargarPeriodoAcademicoSeleccionado();
        $this->cargarDesempenoAcademico();
    }

    private function cargarEstudiantesAcudiente(): void
    {
        $students = Student::query()
            ->whereHas('guardians', function ($query) {
                $query->where('user_id', auth()->id())
                    ->where('estado', 'activo');
            })
            ->where('estado', 'activo')
            ->with('course')
            ->orderBy('primer_apellido')
            ->orderBy('segundo_apellido')
            ->orderBy('primer_nombre')
            ->get();

        $this->estudiantes = $students
            ->mapWithKeys(fn (Student $student) => [
                $student->id => trim(
                    $student->primer_apellido . ' ' .
                    $student->segundo_apellido . ' - ' .
                    $student->primer_nombre . ' ' .
                    $student->segundo_nombre
                ),
            ])
            ->toArray();
    }

    private function cargarEstudianteSeleccionado(): void
    {
        $this->student = null;

        if (! $this->studentId) {
            return;
        }

        $this->student = Student::query()
            ->where('id', $this->studentId)
            ->where('estado', 'activo')
            ->whereHas('guardians', function ($query) {
                $query->where('user_id', auth()->id())
                    ->where('estado', 'activo');
            })
            ->with(['course', 'periodoLectivo'])
            ->first();

        if (! $this->student) {
            $this->studentId = null;
            return;
        }

        $this->cargarPeriodosAcademicos();
    }

    private function cargarPeriodosAcademicos(): void
    {
        $this->periodosAcademicos = [];
        $this->periodoAcademicoId = null;
        $this->periodoAcademicoSeleccionado = null;

        if (! $this->student?->periodo_lectivo_id) {
            return;
        }

        $this->periodosAcademicos = PeriodoAcademico::query()
            ->where('periodo_lectivo_id', $this->student->periodo_lectivo_id)
            ->orderBy('numero')
            ->pluck('nombre', 'id')
            ->toArray();
    }

    private function cargarPeriodoAcademicoSeleccionado(): void
    {
        $this->periodoAcademicoSeleccionado = null;

        if (! $this->periodoAcademicoId || ! $this->student?->periodo_lectivo_id) {
            return;
        }

        $this->periodoAcademicoSeleccionado = PeriodoAcademico::query()
            ->where('id', $this->periodoAcademicoId)
            ->where('periodo_lectivo_id', $this->student->periodo_lectivo_id)
            ->first();
    }

    private function cargarRangosDesempeno(): void
    {
        $this->rangosDesempeno = RangoDesempenoNota::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get()
            ->map(fn ($rango) => [
                'nombre' => $rango->nombre,
                'desde' => (float) $rango->desde,
                'hasta' => (float) $rango->hasta,
            ])
            ->toArray();
    }

    private function obtenerDesempeno($nota): string
    {
        if ($nota === null || $nota === '' || ! is_numeric($nota)) {
            return '-';
        }

        $nota = (float) $nota;

        foreach ($this->rangosDesempeno as $rango) {
            if ($nota >= $rango['desde'] && $nota <= $rango['hasta']) {
                return $rango['nombre'];
            }
        }

        return '-';
    }

    private function cargarDesempenoAcademico(): void
    {
        $this->desempenoAcademico = [];
        $this->actividadesMejoramiento = [];

        if (! $this->student || ! $this->periodoAcademicoSeleccionado || ! $this->student->course) {
            return;
        }

        $pensum = PensumAcademico::query()
            ->where('periodo_lectivo_id', $this->student->periodo_lectivo_id)
            ->where('sede_id', $this->student->sede_id)
            ->where('grado', $this->student->course->grado)
            ->where('estado', 'activo')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $notas = NotaEstudiante::query()
            ->where('student_id', $this->student->id)
            ->where('periodo', $this->periodoAcademicoSeleccionado->numero)
            ->whereIn('pensum_academico_id', $pensum->pluck('id'))
            ->get()
            ->keyBy('pensum_academico_id');

        $this->desempenoAcademico = $pensum
            ->map(function ($asignatura) use ($notas) {
                $nota = $notas->get($asignatura->id);

                return [
                    'pensum_academico_id' => $asignatura->id,
                    'asignatura' => $asignatura->nombre,
                    'ih' => $asignatura->intensidad_horaria ?? '-',
                    'fallas' => $nota?->fallas ?? 0,
                    'pgc' => $nota?->pgc ?? '-',
                    'nota' => $nota?->nota ?? '-',
                    'desempeno' => $this->obtenerDesempeno($nota?->nota),
                ];
            })
            ->toArray();

        $this->cargarActividadesMejoramiento();
    }

    private function cargarActividadesMejoramiento(): void
    {
        $this->actividadesMejoramiento = [];

        if (! $this->student || ! $this->periodoAcademicoSeleccionado || empty($this->desempenoAcademico)) {
            return;
        }

        $pensumIds = collect($this->desempenoAcademico)
            ->pluck('pensum_academico_id')
            ->filter()
            ->values();

        if ($pensumIds->isEmpty()) {
            return;
        }

        $notas = NotaEstudiante::query()
            ->where('student_id', $this->student->id)
            ->where('periodo', $this->periodoAcademicoSeleccionado->numero)
            ->whereIn('pensum_academico_id', $pensumIds)
            ->get()
            ->keyBy('pensum_academico_id');

        $recomendaciones = BoletinRecomendacion::query()
            ->where('sede_id', $this->student->sede_id)
            ->where('periodo_lectivo_id', $this->student->periodo_lectivo_id)
            ->where('grado', $this->student->course?->grado)
            ->where('periodo_academico', $this->periodoAcademicoSeleccionado->numero)
            ->where('tipo', 'mejoramiento')
            ->where('activo', true)
            ->whereIn('pensum_academico_id', $pensumIds)
            ->get()
            ->groupBy('pensum_academico_id');

        foreach ($this->desempenoAcademico as $fila) {
            $pensumId = $fila['pensum_academico_id'] ?? null;

            if (! $pensumId) {
                continue;
            }

            $nota = $notas->get($pensumId);

            $codigos = collect([
                $nota?->mejoramiento_01,
                $nota?->mejoramiento_02,
                $nota?->mejoramiento_03,
                $nota?->mejoramiento_04,
            ])
                ->filter(fn ($codigo) => filled($codigo))
                ->values();

            foreach ($codigos as $codigo) {
                $recomendacion = $recomendaciones
                    ->get($pensumId, collect())
                    ->firstWhere('codigo', (string) $codigo);

                if (! $recomendacion) {
                    continue;
                }

                $this->actividadesMejoramiento[] = [
                    'asignatura' => $fila['asignatura'],
                    'descripcion' => $recomendacion->descripcion,
                ];
            }
        }
    }

    public function getFotoEstudianteUrlProperty(): ?string
    {
        if (! $this->student || ! $this->student->foto) {
            return null;
        }

        return Storage::disk('public')->url($this->student->foto);
    }


    public function descargarPdf(
        BoletinDataService $dataService,
        BoletinPdfService $pdfService
    ): void {
        if (! $this->studentId || ! $this->periodoAcademicoId) {
            Notification::make()
                ->title('Datos incompletos')
                ->body('Seleccione el estudiante y el periodo académico.')
                ->warning()
                ->send();

            return;
        }

        $tieneAcceso = Guardian::query()
            ->where('user_id', auth()->id())
            ->where('student_id', $this->studentId)
            ->where('estado', 'activo')
            ->exists();

        if (! $tieneAcceso) {
            Notification::make()
                ->title('Acceso no permitido')
                ->body('No tiene permiso para descargar este boletín.')
                ->danger()
                ->send();

            return;
        }

        $data = $dataService->generar(
            (int) $this->studentId,
            (int) $this->periodoAcademicoId
        );

        $ruta = $pdfService->generar($data);

        Notification::make()
            ->title('Boletín generado')
            ->body('El PDF fue generado correctamente.')
            ->success()
            ->send();

        $this->dispatch(
            'abrir-pdf-boletin',
            url(Storage::disk('public')->url($ruta))
        );
    }
}