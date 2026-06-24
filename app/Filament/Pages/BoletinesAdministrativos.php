<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;

use ZipArchive;


use App\Models\PeriodoLectivo;
use App\Models\PeriodoAcademico;
use App\Models\Course;
use App\Models\Student;
use App\Models\PensumAcademico;
use App\Models\NotaEstudiante;
use App\Models\RangoDesempenoNota;
use App\Models\BoletinDesempeno;
use App\Models\BoletinRecomendacionEstudiante;
use App\Models\BoletinRecomendacion;
use App\Models\BoletinGenerado;
use App\Models\Docente;


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


use App\Services\Boletines\BoletinDataService;
use App\Services\Boletines\BoletinPdfService;




class BoletinesAdministrativos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.boletines-administrativos';

    protected static ?string $navigationGroup = 'Académico';

    protected static ?string $navigationLabel = 'Boletines Administrativos';

    protected static ?string $title = 'Boletines Administrativos';

    protected ?string $maxContentWidth = 'full';

    protected static ?int $navigationSort = 80;


    

    public ?int $periodoLectivoId = null;
    public ?int $periodoAcademicoId = null;
    public ?string $grado = null;
    public ?int $courseId = null;
    public ?int $studentId = null;

    public ?Student $student = null;

    public ?\App\Models\PeriodoAcademico $periodoAcademicoSeleccionado = null;


    public array $periodosLectivos = [];
    public array $periodosAcademicos = [];
    public array $grados = [];
    public array $cursos = [];
    public array $estudiantes = [];
    public array $desempenoAcademico = [];
    public array $rangosDesempeno = [];
    public array $desempenosPorAsignatura = [];
    public array $actividadesMejoramiento = [];
    public array $catalogoPerfilRembrandtino = [];
    public array $catalogoAcompanamientoFamiliar = [];
    public array $codigosPerfil = [null, null, null, null];
    public array $codigosAcompanamiento = [null, null, null, null];


    public ?BoletinGenerado $boletinGenerado = null;
    public ?string $observaciones = null;

    public array $registroBoletin = [
        'estado' => '-',
        'creado_por' => '-',
        'modificado_por' => '-',
        'ultima_modificacion' => '-',
        'ultimo_pdf' => '-',
    ];



    public function generarPdfEstudiante(
        BoletinDataService $dataService,
        BoletinPdfService $pdfService
    ): void {
        if (! $this->studentId || ! $this->periodoAcademicoId) {
            Notification::make()
                ->title('Datos incompletos')
                ->body('Debe seleccionar un estudiante y un periodo académico antes de generar el PDF.')
                ->danger()
                ->send();

            return;
        }

        $this->guardarCambios();

        $data = $dataService->generar(
            (int) $this->studentId,
            (int) $this->periodoAcademicoId
        );

        $ruta = $pdfService->generar($data);

        $this->cargarBoletinGenerado();

        Notification::make()
            ->title('PDF generado')
            ->body('El boletín individual del estudiante fue generado correctamente.')
            ->success()
            ->send();

        $this->dispatch('abrir-pdf-boletin', url(Storage::disk('public')->url($ruta)));
    }
    

    public function generarPdfCurso(
        BoletinDataService $dataService,
        BoletinPdfService $pdfService
    ): void {
        if (! $this->periodoLectivoId || ! $this->periodoAcademicoId || ! $this->courseId) {
            Notification::make()
                ->title('Seleccione periodo lectivo, periodo académico y curso.')
                ->warning()
                ->send();

            return;
        }

        $students = Student::query()
            ->where('sede_id', session('sede_id'))
            ->where('periodo_lectivo_id', $this->periodoLectivoId)
            ->where('course_id', $this->courseId)
            ->where('estado', 'activo')
            ->orderBy('primer_apellido')
            ->orderBy('segundo_apellido')
            ->orderBy('primer_nombre')
            ->get();

        if ($students->isEmpty()) {
            Notification::make()
                ->title('No hay estudiantes activos en este curso.')
                ->warning()
                ->send();

            return;
        }

        $curso = $this->limpiarNombreArchivo($this->cursos[$this->courseId] ?? 'curso');
        $periodo = $this->limpiarNombreArchivo($this->periodosAcademicos[$this->periodoAcademicoId] ?? 'periodo');
        $anio = now()->format('Y');

        $zipNombre = "{$curso}-boletines-{$periodo}{$anio}.zip";
        $zipRutaRelativa = "boletines/zip/{$zipNombre}";
        $zipRutaAbsoluta = Storage::disk('public')->path($zipRutaRelativa);

        Storage::disk('public')->makeDirectory('boletines/zip');

        $zip = new ZipArchive();

        if ($zip->open($zipRutaAbsoluta, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Notification::make()
                ->title('No se pudo crear el archivo comprimido.')
                ->danger()
                ->send();

            return;
        }

        foreach ($students as $student) {
            $data = $dataService->generar($student->id, $this->periodoAcademicoId);
            $pdfRutaRelativa = $pdfService->generar($data);
            $pdfRutaAbsoluta = Storage::disk('public')->path($pdfRutaRelativa);

            if (file_exists($pdfRutaAbsoluta)) {
                $zip->addFile($pdfRutaAbsoluta, basename($pdfRutaRelativa));
            }
        }

        $zip->close();

        Notification::make()
            ->title('Boletines del curso generados correctamente.')
            ->success()
            ->send();
        
        $this->dispatch(
            'abrir-pdf-boletin',
            url(Storage::disk('public')->url($zipRutaRelativa))
        );

        
    }

    public function publicarBoletin(): void
    {
        Notification::make()
            ->title('Publicar boletín')
            ->body('Más adelante se publicará para acudientes.')
            ->warning()
            ->send();
    }

    public function mount(): void
    {
        $this->cargarPeriodosLectivos();
        $this->cargarPeriodosAcademicos();
        $this->cargarRangosDesempeno();
    }

    public function updatedPeriodoLectivoId(): void
    {
        $this->periodoAcademicoId = null;
        $this->grado = null;
        $this->courseId = null;
        $this->studentId = null;
        

        $this->periodosAcademicos = [];
        $this->grados = [];
        $this->cursos = [];
        $this->estudiantes = [];

        $this->periodoAcademicoSeleccionado = null;

        $this->cargarPeriodosAcademicos();
    }

    public function updatedPeriodoAcademicoId(): void
    {
        $this->grado = null;
        $this->courseId = null;
        $this->studentId = null;

        $this->grados = [];
        $this->cursos = [];
        $this->estudiantes = [];

        if ($this->periodoAcademicoId) {
            $this->cargarGrados();
        }
        $this->cargarPeriodoAcademicoSeleccionado();
    }
    

    public function updatedGrado(): void
    {
        $this->courseId = null;
        $this->studentId = null;

        $this->cursos = [];
        $this->estudiantes = [];

        if ($this->grado) {
            $this->cargarCursos();
        }
    }

    public function updatedCourseId(): void
    {
        $this->studentId = null;
        $this->student = null;
        $this->estudiantes = [];
        $this->desempenoAcademico = [];
        $this->desempenosPorAsignatura = [];
        $this->actividadesMejoramiento = [];

        if ($this->courseId) {
            $this->cargarEstudiantes();
        }
    }

    public function updatedStudentId(): void
    {
        $this->cargarEstudianteSeleccionado();
    }

    private function cargarPeriodosLectivos(): void
    {
        $periodos = PeriodoLectivo::query()
            ->with('sede')
            ->orderByDesc('nombre')
            ->orderBy('sede_id')
            ->get();

        $this->periodosLectivos = $periodos
            ->mapWithKeys(fn ($periodo) => [
                $periodo->id => ($periodo->sede?->nombre ?? 'Sin sede') . ' - ' . $periodo->nombre,
            ])
            ->toArray();

        $sedeSesion = session('sede_id');
        $anioSesion = session('anio');

        $periodoActual = $periodos->first(function ($periodo) use ($sedeSesion, $anioSesion) {
            return (int) $periodo->sede_id === (int) $sedeSesion
                && (string) $periodo->nombre === (string) $anioSesion;
        });

        $this->periodoLectivoId = $periodoActual?->id ?? array_key_first($this->periodosLectivos);
    }

    private function cargarPeriodosAcademicos(): void
    {
        $this->periodosAcademicos = PeriodoAcademico::query()
            ->where('periodo_lectivo_id', $this->periodoLectivoId)
            ->orderBy('numero')
            ->pluck('nombre', 'id')
            ->toArray();

        $this->periodoAcademicoId = null;
    }

    private function cargarGrados(): void
    {
        if (! $this->periodoLectivoId || ! $this->periodoAcademicoId) {
            $this->grados = [];
            $this->grado = null;
            return;
        }

        $cursoDirectorId = $this->cursoDirectorAsignadoId();

        $query = Course::query()
            ->where('periodo_lectivo_id', $this->periodoLectivoId)
            ->where('sede_id', session('sede_id'))
            ->where('estado', 'activo');

        if ($this->usuarioEsDirectorGrupoRestringido()) {
            $query->where('id', $cursoDirectorId);
        }

        $this->grados = $query
            ->orderBy('grado')
            ->pluck('grado', 'grado')
            ->toArray();

        $this->grado = null;
    }

    private function cargarCursos(): void
    {
        if (! $this->periodoLectivoId || ! $this->periodoAcademicoId || ! $this->grado) {
            $this->cursos = [];
            $this->courseId = null;
            return;
        }

        $cursoDirectorId = $this->cursoDirectorAsignadoId();

        $query = Course::query()
            ->where('periodo_lectivo_id', $this->periodoLectivoId)
            ->where('sede_id', session('sede_id'))
            ->where('grado', $this->grado)
            ->where('estado', 'activo');

        if ($this->usuarioEsDirectorGrupoRestringido()) {
            $query->where('id', $cursoDirectorId);
        }

        $this->cursos = $query
            ->orderBy('curso')
            ->pluck('curso', 'id')
            ->toArray();

        $this->courseId = null;
    }

    private function cargarEstudiantes(): void
    {
        if (! $this->periodoLectivoId || ! $this->periodoAcademicoId || ! $this->courseId) {
            $this->estudiantes = [];
            $this->studentId = null;
            return;
        }

        if (
            $this->usuarioEsDirectorGrupoRestringido()
            && (int) $this->courseId !== (int) $this->cursoDirectorAsignadoId()
        ) {
            $this->estudiantes = [];
            $this->studentId = null;
            return;
        }

        $this->estudiantes = Student::query()
            ->where('sede_id', session('sede_id'))
            ->where('periodo_lectivo_id', $this->periodoLectivoId)
            ->where('course_id', $this->courseId)
            ->where('estado', 'activo')
            ->orderBy('primer_apellido')
            ->orderBy('segundo_apellido')
            ->orderBy('primer_nombre')
            ->get()
            ->mapWithKeys(fn ($student) => [
                $student->id => collect([
                    $student->primer_nombre,
                    $student->segundo_nombre,
                    $student->primer_apellido,
                    $student->segundo_apellido,
                ])->filter()->implode(' '),
            ])
            ->toArray();

        $this->studentId = null;
        $this->student = null;
        $this->desempenoAcademico = [];
        $this->desempenosPorAsignatura = [];
        $this->actividadesMejoramiento = [];
    }

    private function cargarEstudianteSeleccionado(): void
    {
        if (! $this->studentId) {
            $this->student = null;
            $this->desempenoAcademico = [];
            $this->desempenosPorAsignatura = [];
            $this->actividadesMejoramiento = [];
            return;
        }

        $this->student = Student::query()
            ->with('course')
            ->where('sede_id', session('sede_id'))
            ->where('periodo_lectivo_id', $this->periodoLectivoId)
            ->where('course_id', $this->courseId)
            ->where('id', $this->studentId)
            ->where('estado', 'activo')
            ->first();
        $this->cargarDesempenoAcademico();
    }

    private function cargarPeriodoAcademicoSeleccionado(): void
    {
        $this->periodoAcademicoSeleccionado = $this->periodoAcademicoId
            ? PeriodoAcademico::find($this->periodoAcademicoId)
            : null;
    }


    public function getFotoEstudianteUrlProperty(): ?string
    {
        if (! $this->student || ! $this->student->foto) {
            return null;
        }

        return Storage::disk('public')->url($this->student->foto);
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
        $this->desempenosPorAsignatura = [];
        $this->actividadesMejoramiento = [];

        if (! $this->student || ! $this->periodoLectivoId || ! $this->periodoAcademicoId || ! $this->courseId) {
            return;
        }

        $periodoAcademico = PeriodoAcademico::find($this->periodoAcademicoId);

        if (! $periodoAcademico) {
            return;
        }

        $course = Course::find($this->courseId);

        if (! $course) {
            return;
        }

        $pensum = PensumAcademico::query()
            ->where('periodo_lectivo_id', $this->periodoLectivoId)
            ->where('sede_id', session('sede_id'))
            ->where('grado', $course->grado)
            ->where('estado', 'activo')
            ->where('tipo', 'asignatura')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $notas = NotaEstudiante::query()
            ->where('student_id', $this->student->id)
            ->where('periodo', $periodoAcademico->numero)
            ->whereIn('pensum_academico_id', $pensum->pluck('id'))
            ->get()
            ->keyBy('pensum_academico_id');

        $this->desempenoAcademico = $pensum
            ->map(function ($asignatura) use ($notas) {
                $nota = $notas->get($asignatura->id);

                return [
                    'pensum_academico_id' => $asignatura->id,
                    'asignatura' => $asignatura->nombre_corto ?: $asignatura->nombre,
                    'ih' => $asignatura->intensidad_horaria ?? '-',
                    'fallas' => $nota?->fallas ?? 0,
                    'pgc' => $nota?->pgc ?? '-',
                    'nota' => $nota?->nota ?? '-',
                    'desempeno' => $this->obtenerDesempeno($nota?->nota),
                ];
            })
            ->toArray();
        $this->cargarDesempenosPorAsignatura();
        $this->cargarActividadesMejoramiento();
        $this->cargarCatalogosFinales();
        $this->cargarCodigosFinalesEstudiante();
        $this->cargarBoletinGenerado();
        
    
    }

    private function cargarDesempenosPorAsignatura(): void
    {
        $this->desempenosPorAsignatura = [];
        $this->actividadesMejoramiento = [];

        if (! $this->student || ! $this->periodoLectivoId || ! $this->periodoAcademicoSeleccionado || ! $this->courseId) {
            return;
        }

        $course = Course::find($this->courseId);

        if (! $course) {
            return;
        }

        $pensumIds = collect($this->desempenoAcademico)
            ->pluck('pensum_academico_id')
            ->filter()
            ->values();

        if ($pensumIds->isEmpty()) {
            return;
        }

        $desempenos = BoletinDesempeno::query()
            ->with('pensumAcademico')
            ->where('sede_id', session('sede_id'))
            ->where('periodo_lectivo_id', $this->periodoLectivoId)
            ->where('grado', $course->grado)
            ->where('periodo_academico', $this->periodoAcademicoSeleccionado->numero)
            ->whereIn('pensum_academico_id', $pensumIds)
            ->get()
            ->keyBy('pensum_academico_id');

        $this->desempenosPorAsignatura = collect($this->desempenoAcademico)
            ->map(function ($fila) use ($desempenos) {
                $registro = $desempenos->get($fila['pensum_academico_id'] ?? null);

                $items = collect([
                    $registro?->desempeno_1,
                    $registro?->desempeno_2,
                    $registro?->desempeno_3,
                    $registro?->desempeno_4,
                ])->filter()->values()->toArray();

                return [
                    'asignatura' => $fila['asignatura'],
                    'items' => $items,
                ];
            })
            ->toArray();
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
            ->with('pensumAcademico')
            ->where('sede_id', session('sede_id'))
            ->where('periodo_lectivo_id', $this->periodoLectivoId)
            ->where('grado', $this->grado)
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
            ])->filter()->values();

            foreach ($codigos as $codigo) {
                $recomendacion = $recomendaciones
                    ->get($pensumId, collect())
                    ->firstWhere('codigo', (string) $codigo);

                $this->actividadesMejoramiento[] = [
                    'asignatura' => $fila['asignatura'],
                    'codigo' => $codigo,
                    'descripcion' => $recomendacion?->descripcion ?? 'Código no encontrado',
                ];
            }
        }
    }

    

    private function cargarCatalogosFinales(): void
    {
        $this->catalogoPerfilRembrandtino = [];
        $this->catalogoAcompanamientoFamiliar = [];

        if (! $this->periodoLectivoId || ! $this->periodoAcademicoSeleccionado || ! $this->grado) {
            return;
        }

        $base = BoletinRecomendacion::query()
            ->where('sede_id', session('sede_id'))
            ->where('periodo_lectivo_id', $this->periodoLectivoId)
            ->where('grado', $this->grado)
            ->where('periodo_academico', $this->periodoAcademicoSeleccionado->numero)
            ->where('activo', true)
            ->orderBy('codigo');

        $this->catalogoPerfilRembrandtino = (clone $base)
            ->where('tipo', 'perfil')
            ->get(['id', 'codigo', 'descripcion'])
            ->toArray();

        $this->catalogoAcompanamientoFamiliar = (clone $base)
            ->where('tipo', 'acompanamiento')
            ->get(['id', 'codigo', 'descripcion'])
            ->toArray();
    }

    private function cargarCodigosFinalesEstudiante(): void
    {
        $this->codigosPerfil = [null, null, null, null];
        $this->codigosAcompanamiento = [null, null, null, null];

        if (! $this->student || ! $this->periodoAcademicoSeleccionado) {
            return;
        }

        $asignaciones = BoletinRecomendacionEstudiante::query()
            ->with('recomendacion')
            ->where('student_id', $this->student->id)
            ->where('periodo_academico', $this->periodoAcademicoSeleccionado->numero)
            ->orderBy('orden')
            ->get();

        $perfil = $asignaciones
            ->filter(fn ($item) => $item->recomendacion?->tipo === 'perfil')
            ->pluck('recomendacion.codigo')
            ->values()
            ->take(4)
            ->toArray();

        $acompanamiento = $asignaciones
            ->filter(fn ($item) => $item->recomendacion?->tipo === 'acompanamiento')
            ->pluck('recomendacion.codigo')
            ->values()
            ->take(4)
            ->toArray();

        foreach ($perfil as $i => $codigo) {
            $this->codigosPerfil[$i] = $codigo;
        }

        foreach ($acompanamiento as $i => $codigo) {
            $this->codigosAcompanamiento[$i] = $codigo;
        }
    }

    public function guardarCambios(): void
    {
        if (! $this->student || ! $this->periodoAcademicoSeleccionado) {
            Notification::make()
                ->title('Seleccione un estudiante')
                ->body('Debe seleccionar periodo, grado, curso y estudiante antes de guardar.')
                ->danger()
                ->send();

            return;
        }

        $codigosPerfilOriginal = collect($this->codigosPerfil)
            ->map(fn ($codigo) => trim((string) $codigo))
            ->filter()
            ->values();

        if ($codigosPerfilOriginal->count() !== $codigosPerfilOriginal->unique()->count()) {
            Notification::make()
                ->title('Código repetido')
                ->body('No puede repetir códigos dentro de Perfil Rembrandtino.')
                ->danger()
                ->send();

            return;
        }

        $codigosPerfil = $codigosPerfilOriginal->unique()->values();

        if ($codigosPerfil->count() < 1) {
            Notification::make()
                ->title('Perfil Rembrandtino requerido')
                ->body('Debe asignar mínimo 1 código de Perfil Rembrandtino.')
                ->danger()
                ->send();

            return;
        }

        if ($codigosPerfil->count() > 4) {
            Notification::make()
                ->title('Máximo 4 códigos')
                ->body('Solo puede asignar máximo 4 códigos de Perfil Rembrandtino.')
                ->danger()
                ->send();

            return;
        }

        $codigosAcompanamientoOriginal = collect($this->codigosAcompanamiento)
            ->map(fn ($codigo) => trim((string) $codigo))
            ->filter()
            ->values();

        if ($codigosAcompanamientoOriginal->count() !== $codigosAcompanamientoOriginal->unique()->count()) {
            Notification::make()
                ->title('Código repetido')
                ->body('No puede repetir códigos dentro de Acompañamiento Familiar.')
                ->danger()
                ->send();

            return;
        }

        $codigosAcompanamiento = $codigosAcompanamientoOriginal->unique()->values();

        if ($codigosAcompanamiento->count() < 1) {
            Notification::make()
                ->title('Acompañamiento Familiar requerido')
                ->body('Debe asignar mínimo 1 código de Acompañamiento Familiar.')
                ->danger()
                ->send();

            return;
        }

        if ($codigosAcompanamiento->count() > 4) {
            Notification::make()
                ->title('Máximo 4 códigos')
                ->body('Solo puede asignar máximo 4 códigos de Acompañamiento Familiar.')
                ->danger()
                ->send();

            return;
        }

        $recomendacionesPerfil = BoletinRecomendacion::query()
            ->where('sede_id', session('sede_id'))
            ->where('periodo_lectivo_id', $this->periodoLectivoId)
            ->where('grado', $this->grado)
            ->where('periodo_academico', $this->periodoAcademicoSeleccionado->numero)
            ->where('tipo', 'perfil')
            ->where('activo', true)
            ->whereIn('codigo', $codigosPerfil)
            ->get()
            ->keyBy(fn ($item) => (string) $item->codigo);

        $recomendacionesAcompanamiento = BoletinRecomendacion::query()
            ->where('sede_id', session('sede_id'))
            ->where('periodo_lectivo_id', $this->periodoLectivoId)
            ->where('grado', $this->grado)
            ->where('periodo_academico', $this->periodoAcademicoSeleccionado->numero)
            ->where('tipo', 'acompanamiento')
            ->where('activo', true)
            ->whereIn('codigo', $codigosAcompanamiento)
            ->get()
            ->keyBy(fn ($item) => (string) $item->codigo);

        $codigosInvalidos = $codigosPerfil
            ->reject(fn ($codigo) => $recomendacionesPerfil->has((string) $codigo))
            ->values();

        if ($codigosInvalidos->isNotEmpty()) {
            Notification::make()
                ->title('Códigos no válidos')
                ->body('Estos códigos no existen o no están activos: ' . $codigosInvalidos->implode(', '))
                ->danger()
                ->send();

            return;
        }

        $codigosInvalidosAcompanamiento = $codigosAcompanamiento
            ->reject(fn ($codigo) => $recomendacionesAcompanamiento->has((string) $codigo))
            ->values();

        if ($codigosInvalidosAcompanamiento->isNotEmpty()) {
            Notification::make()
                ->title('Códigos no válidos')
                ->body('Estos códigos de Acompañamiento Familiar no existen o no están activos: ' . $codigosInvalidosAcompanamiento->implode(', '))
                ->danger()
                ->send();

            return;
        }

        BoletinRecomendacionEstudiante::query()
            ->where('student_id', $this->student->id)
            ->where('periodo_academico', $this->periodoAcademicoSeleccionado->numero)
            ->whereHas('recomendacion', fn ($query) => $query->where('tipo', 'perfil'))
            ->delete();

        BoletinRecomendacionEstudiante::query()
            ->where('student_id', $this->student->id)
            ->where('periodo_academico', $this->periodoAcademicoSeleccionado->numero)
            ->whereHas('recomendacion', fn ($query) => $query->where('tipo', 'acompanamiento'))
            ->delete();

        foreach ($codigosPerfil as $index => $codigo) {
            BoletinRecomendacionEstudiante::create([
                'student_id' => $this->student->id,
                'boletin_recomendacion_id' => $recomendacionesPerfil[(string) $codigo]->id,
                'periodo_academico' => $this->periodoAcademicoSeleccionado->numero,
                'orden' => $index + 1,
                'created_by' => auth()->id(),
            ]);
        }

        foreach ($codigosAcompanamiento as $index => $codigo) {
            BoletinRecomendacionEstudiante::create([
                'student_id' => $this->student->id,
                'boletin_recomendacion_id' => $recomendacionesAcompanamiento[(string) $codigo]->id,
                'periodo_academico' => $this->periodoAcademicoSeleccionado->numero,
                'orden' => $index + 1,
                'created_by' => auth()->id(),
            ]);
        }

        $this->cargarCodigosFinalesEstudiante();

        $this->validate([
            'observaciones' => ['nullable', 'string', 'max:68'],
        ]);

        $this->boletinGenerado = BoletinGenerado::updateOrCreate(
            [
                'periodo_lectivo_id' => $this->periodoLectivoId,
                'periodo_academico_id' => $this->periodoAcademicoId,
                'course_id' => $this->courseId,
                'student_id' => $this->student->id,
            ],
            [
                'observaciones' => $this->observaciones,
                'codigos_perfil' => $codigosPerfil->values()->toArray(),
                'codigos_acompanamiento' => $codigosAcompanamiento->values()->toArray(),
                'estado' => 'disponible',
                'created_by' => $this->boletinGenerado?->created_by ?? auth()->id(),
                'updated_by' => auth()->id(),
            ]
        );

        $this->cargarBoletinGenerado();

        Notification::make()
            ->title('Cambios guardados')
            ->body('Se guardaron correctamente los datos de Perfil Rembrandtino, Acompañamiento Familiar y Observaciones.')
            ->success()
            ->send();
    }

    private function cargarBoletinGenerado(): void
    {
        $this->boletinGenerado = null;
        $this->observaciones = null;

        $this->registroBoletin = [
            'estado' => '-',
            'creado_por' => '-',
            'modificado_por' => '-',
            'ultima_modificacion' => '-',
            'ultimo_pdf' => '-',
        ];

        if (! $this->student || ! $this->periodoLectivoId || ! $this->periodoAcademicoId || ! $this->courseId) {
            return;
        }

        $this->boletinGenerado = BoletinGenerado::query()
            ->with(['creadoPor', 'modificadoPor'])
            ->where('periodo_lectivo_id', $this->periodoLectivoId)
            ->where('periodo_academico_id', $this->periodoAcademicoId)
            ->where('course_id', $this->courseId)
            ->where('student_id', $this->student->id)
            ->first();

        if (! $this->boletinGenerado) {
            return;
        }

        $this->observaciones = $this->boletinGenerado->observaciones;

        $this->registroBoletin = [
            'estado' => ucfirst($this->boletinGenerado->estado ?? 'borrador'),
            'creado_por' => $this->boletinGenerado->creadoPor?->name ?? '-',
            'modificado_por' => $this->boletinGenerado->modificadoPor?->name ?? '-',
            'ultima_modificacion' => $this->boletinGenerado->updated_at?->format('d/m/Y h:i a') ?? '-',
            'ultimo_pdf' => $this->boletinGenerado->generado_en?->format('d/m/Y h:i a') ?? '-',
        ];
    }

    private function limpiarNombreArchivo(string $texto): string
    {
        $texto = trim($texto);

        $texto = str_replace(
            ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ'],
            ['a','e','i','o','u','A','E','I','O','U','n','N'],
            $texto
        );

        $texto = preg_replace('/[^A-Za-z0-9]+/', '', $texto);

        return $texto ?: 'archivo';
    }
    

    private function cursoDirectorAsignadoId(): ?int
    {
        $user = Auth::user();

        if (! $user || $user->hasAnyRole(['superadmin', 'admin'])) {
            return null;
        }

        if (! $user->hasRole('director_grupo')) {
            return null;
        }

        return Docente::query()
            ->where('user_id', $user->id)
            ->where('estado', 'activo')
            ->value('direccion_curso_id');
    }

    private function usuarioEsDirectorGrupoRestringido(): bool
    {
        $user = Auth::user();

        return $user
            && $user->hasRole('director_grupo')
            && ! $user->hasAnyRole(['superadmin', 'admin']);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user
            && $user->hasAnyRole(['superadmin', 'admin', 'director_grupo']);
    }



}