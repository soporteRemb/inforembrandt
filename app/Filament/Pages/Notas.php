<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\PensumAcademico;
use App\Models\Student;
use App\Models\PeriodoLectivo;
use App\Models\NotaEstudiante;
use App\Models\RangoDesempenoNota;
use App\Models\Docente;
use App\Models\DocenteAsignatura;
use App\Models\PeriodoAcademico;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

use Illuminate\Support\Facades\Auth;

use Livewire\WithFileUploads;

use Symfony\Component\HttpFoundation\StreamedResponse;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;





class Notas extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.notas';

    protected static ?string $navigationLabel = 'Notas';

    protected static ?string $title = 'Notas Académicas';

    protected static ?string $navigationGroup = 'Académico';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public array $estudiantes = [];

    public string $buscarEstudiante = '';

    public $archivoExcel = null;
    public bool $mostrarModalImportar = false;

    public array $erroresImportacion = [];

    public bool $mostrarModalErrores = false;

    public bool $periodoAcademicoCerrado = false;

    public array $indicadores = [
        'total' => 0,
        'registradas' => 0,
        'pendientes' => 0,
        'promedio' => null,
        'mayor' => null,
        'menor' => null,
    ];

    public array $notasRegistradas = [];

    public array $pendientes = [];

    public array $rangosDesempeno = [];

    public ?string $cursoSeleccionado = null;
    public ?string $asignaturaSeleccionada = null;
    public ?string $periodoTexto = null;

    public bool $hayCambiosSinGuardar = false;
    public bool $mostrarModalSalirSinGuardar = false;

    public bool $mostrarModalExportar = false;

    public ?int $docenteExportarId = null;

    public array $docentesExportar = [];

    public string $buscarDocenteExportar = '';

    public array $erroresNotas = [];
    public bool $hayErroresNotas = false;




    public function mount(): void
    {
        $this->form->fill();
        $this->cargarRangosDesempeno();
    }

    public function verificarPeriodoAcademicoCerrado(): void
    {
        $periodoLectivoId = $this->data['periodo_lectivo_id'] ?? null;
        $periodoAcademico = $this->data['periodo'] ?? null;

        if (! $periodoLectivoId || ! $periodoAcademico) {
            $this->periodoAcademicoCerrado = false;
            return;
        }

        $this->periodoAcademicoCerrado = PeriodoAcademico::query()
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('numero', $periodoAcademico)
            ->where('estado', 'cerrado')
            ->exists();
    }


    public function updatedDataCourseId($value): void
    {
        $pensumAnteriorId = $this->data['pensum_academico_id'] ?? null;
        $pensumAnterior = PensumAcademico::find($pensumAnteriorId);
        $cursoNuevo = Course::find($value);

        if ($pensumAnterior && $cursoNuevo) {
            $nuevoPensum = PensumAcademico::query()
                ->where('periodo_lectivo_id', $cursoNuevo->periodo_lectivo_id)
                ->where('sede_id', $cursoNuevo->sede_id)
                ->where('grado', $cursoNuevo->grado)
                ->where('estado', 'activo')
                ->where(function ($query) use ($pensumAnterior) {
                    $query->where('codigo', $pensumAnterior->codigo)
                        ->orWhere('nombre', $pensumAnterior->nombre);
                })
                ->first();

            $this->data['pensum_academico_id'] = $nuevoPensum?->id;
        }

        $this->actualizarTextosFiltro();
        $this->refrescarTablaNotas();
    }

    

    public function updatedDataPensumAcademicoId($value): void
    {
        $this->actualizarTextosFiltro();
        $this->refrescarTablaNotas();
    }

    public function updatedDataPeriodo($value): void
    {
        $this->actualizarTextosFiltro();
        $this->refrescarTablaNotas();
    }



    private function refrescarTablaNotas(): void
    {
        $this->estudiantes = [];
        $this->notasRegistradas = [];
        $this->pendientes = [];

        if (
            ! empty($this->data['course_id']) &&
            ! empty($this->data['pensum_academico_id']) &&
            ! empty($this->data['periodo'])
        ) {
            $this->cargarEstudiantes();
            return;
        }

        $this->calcularIndicadores();
    }

    private function actualizarTextosFiltro(): void
    {
        $curso = Course::find($this->data['course_id'] ?? null);

        $this->cursoSeleccionado = $curso
            ? $curso->curso . ' - ' . $curso->descripcion
            : null;

        $pensum = PensumAcademico::find($this->data['pensum_academico_id'] ?? null);

        $this->asignaturaSeleccionada = $pensum?->nombre;

        $this->periodoTexto = match((string) ($this->data['periodo'] ?? null)) {
            '1' => 'Primer periodo',
            '2' => 'Segundo periodo',
            '3' => 'Tercer periodo',
            '4' => 'Cuarto periodo',
            default => null,
        };
    }







    public function cargarEstudiantes(): void
    {
        $courseId = $this->data['course_id'] ?? null;
        $pensumId = $this->data['pensum_academico_id'] ?? null;
        $periodo = $this->data['periodo'] ?? null;

        if (! $courseId) {
            $this->estudiantes = [];
            $this->notasRegistradas = [];
            $this->pendientes = [];

            $this->indicadores = [
                'total' => 0,
                'registradas' => 0,
                'pendientes' => 0,
                'promedio' => null,
                'mayor' => null,
                'menor' => null,
            ];

            return;
        }

        $notas = collect();

        if ($pensumId && $periodo) {
            $notas = NotaEstudiante::query()
                ->where('pensum_academico_id', $pensumId)
                ->where('periodo', $periodo)
                ->get()
                ->keyBy('student_id');
        }

        $this->estudiantes = Student::query()
            ->where('course_id', $courseId)
            ->orderBy('primer_apellido')
            ->orderBy('segundo_apellido')
            ->orderBy('primer_nombre')
            ->orderBy('segundo_nombre')
            ->get()
            ->map(function ($student) use ($notas) {
                $nota = $notas->get($student->id);

                return [
                    'student_id' => $student->id,
                    'nombre' => trim(
                        $student->primer_apellido . ' ' .
                        $student->segundo_apellido . ' ' .
                        $student->primer_nombre . ' ' .
                        $student->segundo_nombre
                    ),
                    'nota' => $nota?->nota,
                    'fallas' => $nota?->fallas ?? 0,
                    'mejoramientos' => [
                        '01' => $nota?->mejoramiento_01,
                        '02' => $nota?->mejoramiento_02,
                        '03' => $nota?->mejoramiento_03,
                        '04' => $nota?->mejoramiento_04,
                    ],
                    'pgc' => $nota?->pgc,
                    'registrada' => (bool) $nota,
                ];
            })
            ->toArray();

        $this->notasRegistradas = collect($this->estudiantes)
            ->where('registrada', true)
            ->values()
            ->toArray();

        $this->pendientes = collect($this->estudiantes)
            ->where('registrada', false)
            ->values()
            ->toArray();

        $this->calcularIndicadores();    
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Filtros académicos')
                    ->compact()
                    ->schema([

                        Forms\Components\Select::make('periodo_lectivo_id')
                            ->label('Periodo lectivo')
                            ->options(fn () => PeriodoLectivo::query()
                                ->with('sede')
                                ->orderByDesc('id')
                                ->get()
                                ->mapWithKeys(fn ($periodo) => [
                                    $periodo->id => ($periodo->sede?->nombre ?? 'Sin sede') . ' - ' . $periodo->nombre,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(function () {
                                $sedeId = session('sede_id');
                                $anio = session('anio');

                                if (! $sedeId || ! $anio) {
                                    return null;
                                }

                                return PeriodoLectivo::query()
                                    ->where('sede_id', $sedeId)
                                    ->where('estado', 'abierto')
                                    ->where('nombre', 'like', "%{$anio}%")
                                    ->value('id');
                            })
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->actualizarTextosFiltro();
                                $this->refrescarTablaNotas();
                                $this->verificarPeriodoAcademicoCerrado();
                            }),

                        Forms\Components\Select::make('course_id')
                            ->label('Curso')
                            ->options(function (Forms\Get $get) {
                                $periodoId = $get('periodo_lectivo_id');
                                $user = Auth::user();

                                if (! $periodoId || ! $user) {
                                    return [];
                                }

                                if ($user->hasRole('docente') || $user->hasRole('Docente')) {
                                    $docente = Docente::where('user_id', $user->id)->first();

                                    if (! $docente) {
                                        return [];
                                    }

                                    return DocenteAsignatura::query()
                                        ->with('course')
                                        ->where('docente_id', $docente->id)
                                        ->whereHas('course', fn ($q) => $q->where('periodo_lectivo_id', $periodoId))
                                        ->get()
                                        ->pluck('course')
                                        ->filter()
                                        ->unique('id')
                                        ->sortBy('curso')
                                        ->mapWithKeys(fn ($course) => [
                                            $course->id => "{$course->curso} - {$course->descripcion}",
                                        ])
                                        ->toArray();
                                }

                                return Course::query()
                                    ->where('periodo_lectivo_id', $periodoId)
                                    ->orderBy('grado')
                                    ->orderBy('curso')
                                    ->get()
                                    ->mapWithKeys(fn ($course) => [
                                        $course->id => "{$course->curso} - {$course->descripcion}",
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->actualizarTextosFiltro();
                                $this->refrescarTablaNotas();
                            }),

                        Forms\Components\Select::make('pensum_academico_id')
                            ->label('Asignatura')
                            ->options(function (Forms\Get $get) {
                                $courseId = $get('course_id');
                                $periodoId = $get('periodo_lectivo_id');
                                $user = Auth::user();

                                if (! $courseId || ! $periodoId || ! $user) {
                                    return [];
                                }

                               if ($user->hasRole('docente') || $user->hasRole('Docente')) {
                                    $docente = Docente::where('user_id', $user->id)->first();

                                    if (! $docente) {
                                        return [];
                                    }

                                    return DocenteAsignatura::query()
                                        ->with('pensumAcademico')
                                        ->where('docente_id', $docente->id)
                                        ->where('course_id', $courseId)
                                        ->whereHas('pensumAcademico', fn ($q) => $q->where('periodo_lectivo_id', $periodoId))
                                        ->get()
                                        ->pluck('pensumAcademico')
                                        ->filter()
                                        ->unique('id')
                                        ->sortBy('nombre')
                                        ->mapWithKeys(fn ($pensum) => [
                                            $pensum->id => $pensum->nombre,
                                        ])
                                        ->toArray();
                                }

                                $course = Course::find($courseId);

                                if (! $course) {
                                    return [];
                                }

                                return PensumAcademico::query()
                                    ->where('periodo_lectivo_id', $periodoId)
                                    ->where('sede_id', $course->sede_id)
                                    ->where('grado', $course->grado)
                                    ->where('estado', 'activo')
                                    ->orderBy('orden')
                                    ->orderBy('nombre')
                                    ->pluck('nombre', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->actualizarTextosFiltro();
                                $this->refrescarTablaNotas();
                            }),

                        Forms\Components\Select::make('periodo')
                            ->label('Periodo académico')
                            ->options([
                                1 => 'Primer periodo',
                                2 => 'Segundo periodo',
                                3 => 'Tercer periodo',
                                4 => 'Cuarto periodo',
                            ])
                            ->native(false)
                            ->placeholder('Seleccione una opción')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->actualizarTextosFiltro();
                                $this->refrescarTablaNotas();
                                $this->verificarPeriodoAcademicoCerrado();
                            }),

                    ])
                    ->columns(4),

            ])
            ->statePath('data');
    }

    public function calcularIndicadores(): void
    {
        $total = count($this->estudiantes);

        $conNota = collect($this->estudiantes)
            ->filter(fn ($e) => $e['nota'] !== null && $e['nota'] !== '');

        $registradas = $conNota->count();
        $pendientes = $total - $registradas;

        $promedio = $registradas > 0
            ? round($conNota->avg(fn ($e) => (float) $e['nota']), 2)
            : null;

        $mayor = $conNota
            ->sortByDesc(fn ($e) => (float) $e['nota'])
            ->first();

        $menor = $conNota
            ->sortBy(fn ($e) => (float) $e['nota'])
            ->first();

        $this->indicadores = [
            'total' => $total,
            'registradas' => $registradas,
            'pendientes' => $pendientes,
            'promedio' => $promedio,
            'mayor' => $mayor,
            'menor' => $menor,
        ];
    }


    public function getEstudiantesFiltradosProperty()
    {
        if (trim($this->buscarEstudiante) === '') {
            return $this->estudiantes;
        }

        return collect($this->estudiantes)
            ->filter(fn ($e) => str_contains(
                mb_strtolower($e['nombre']),
                mb_strtolower($this->buscarEstudiante)
            ))
            ->values()
            ->toArray();
    }



    private function docentePuedeGestionarAsignatura(int $courseId, int $pensumAcademicoId): bool
    {
        $user = Auth::user();

        if (! $user || $user->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        $docente = Docente::where('user_id', $user->id)->first();

        if (! $docente) {
            return false;
        }

        return DocenteAsignatura::query()
            ->where('docente_id', $docente->id)
            ->where('course_id', $courseId)
            ->where('pensum_academico_id', $pensumAcademicoId)
            ->exists();
    }

    private function notificarAsignaturaNoAutorizada(): void
    {
        Notification::make()
            ->title('Acceso no autorizado')
            ->body('No tiene asignado este curso o asignatura. No es posible registrar, importar o modificar estas notas.')
            ->danger()
            ->send();
    }


    private function periodoLectivoEstaAbierto(): bool
    {
        $periodoId = $this->data['periodo_lectivo_id'] ?? null;

        if (! $periodoId) {
            return false;
        }

        return PeriodoLectivo::query()
            ->where('id', $periodoId)
            ->where('estado', 'abierto')
            ->exists();
    }

    private function notificarPeriodoCerrado(): void
    {
        Notification::make()
            ->title('Periodo lectivo cerrado')
            ->body('Este periodo lectivo está cerrado. Solo puede consultar la información registrada.')
            ->warning()
            ->send();
    }





    public function guardarNotas(): void
    {
        $this->verificarPeriodoAcademicoCerrado();

        if ($this->periodoAcademicoCerrado) {
            Notification::make()
                ->title('Periodo académico cerrado')
                ->body('No es posible guardar notas en un periodo académico cerrado.')
                ->danger()
                ->send();

            return;
        }
        
        $this->validarNotas();

        if ($this->hayErroresNotas) {

            Notification::make()
                ->title('Hay notas inválidas')
                ->body('Verifique las notas marcadas en rojo.')
                ->danger()
                ->send();

            return;
        }

        $courseId = (int) ($this->data['course_id'] ?? 0);
        $pensumId = (int) ($this->data['pensum_academico_id'] ?? 0);
        $periodo = $this->data['periodo'] ?? null;

        if (! $courseId || ! $pensumId || ! $periodo) {

            Notification::make()
                ->title('Filtros incompletos')
                ->body('Seleccione curso, asignatura y periodo académico antes de guardar.')
                ->warning()
                ->send();

            return;
        }

        if (! $this->periodoLectivoEstaAbierto()) {
            $this->notificarPeriodoCerrado();
            return;
        }


        if (! $this->docentePuedeGestionarAsignatura($courseId, $pensumId)) {
            $this->notificarAsignaturaNoAutorizada();
            return;
        }

        foreach ($this->estudiantes as $estudiante) {
            NotaEstudiante::updateOrCreate(
                [
                    'student_id' => $estudiante['student_id'],
                    'pensum_academico_id' => $pensumId,
                    'periodo' => $periodo,
                ],
                [
                    'nota' => is_numeric($estudiante['nota'] ?? null)
                        ? (float) $estudiante['nota']
                        : null,

                    'fallas' => is_numeric($estudiante['fallas'] ?? null)
                        ? (int) $estudiante['fallas']
                        : 0,

                    'mejoramiento_01' => $estudiante['mejoramientos']['01'] ?? null,
                    'mejoramiento_02' => $estudiante['mejoramientos']['02'] ?? null,
                    'mejoramiento_03' => $estudiante['mejoramientos']['03'] ?? null,
                    'mejoramiento_04' => $estudiante['mejoramientos']['04'] ?? null,
                    'pgc' => $estudiante['pgc'] ?? null,
                ]
            );
        }

        $this->cargarEstudiantes();
        $this->calcularIndicadores();

        $this->hayCambiosSinGuardar = false;
        $this->dispatch('notas-cambios-sin-guardar', estado: false);

        Notification::make()
            ->title('Notas guardadas correctamente')
            ->success()
            ->send();
    }

    public function abrirModalImportar(): void
    {
        $this->archivoExcel = null;
        $this->mostrarModalImportar = true;
    }

    public function cerrarModalImportar(): void
    {
        $this->archivoExcel = null;
        $this->mostrarModalImportar = false;
    }




    

    public function importarExcel(): void
    {
        if (! $this->archivoExcel) {
            Notification::make()
                ->title('Seleccione un archivo Excel')
                ->warning()
                ->send();

            return;
        }

        if (! $this->periodoLectivoEstaAbierto()) {
            $this->notificarPeriodoCerrado();
            return;
        }


        $path = $this->archivoExcel->getRealPath();

        $spreadsheet = IOFactory::load($path);

        $hojasProcesadas = 0;
        $registrosImportados = 0;
        $errores = [];

        $debugHojas = [];

        $this->erroresImportacion = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $hojaNombre = $sheet->getTitle();

            $codigoMateria = trim((string) $sheet->getCell('F2')->getValue());
            $nombreMateria = trim((string) $sheet->getCell('G2')->getValue());
            $cursoCodigo = trim((string) $sheet->getCell('F4')->getValue());

            if ($codigoMateria === '' || $cursoCodigo === '') {
                $errores[] = "{$hojaNombre}: No se encontró materia o curso en la cabecera.";
                continue;
            }

            $course = Course::query()
                ->where('curso', $cursoCodigo)
                ->first();

            if (! $course) {
                $errores[] = "{$hojaNombre}: Curso {$cursoCodigo} no encontrado en el sistema.";
                continue;
            }

            $pensum = PensumAcademico::query()
                ->where('sede_id', $course->sede_id)
                ->where('periodo_lectivo_id', $course->periodo_lectivo_id)
                ->where('grado', $course->grado)
                ->where('estado', 'activo')
                ->where(function ($query) use ($codigoMateria, $nombreMateria) {
                    $query->where('codigo', $codigoMateria)
                        ->orWhere('nombre', $nombreMateria);
                })
                ->first();

            if (! $pensum) {
                $errores[] = "{$hojaNombre}: La materia {$codigoMateria} - {$nombreMateria} no está configurada en el pensum del curso {$cursoCodigo}.";
                continue;
            }

            if (! $this->docentePuedeGestionarAsignatura((int) $course->id, (int) $pensum->id)) {
                $errores[] = "{$hojaNombre}: No tiene autorización para importar notas del curso {$cursoCodigo} y la asignatura {$nombreMateria}.";
                continue;
            }

            $fila = 8;

            while (true) {
                $codigoEstudiante = trim((string) $sheet->getCell("B{$fila}")->getValue());
                $nombreEstudiante = trim((string) $sheet->getCell("C{$fila}")->getValue());

                if ($codigoEstudiante === '' && $nombreEstudiante === '') {
                    break;
                }

                $student = Student::query()
                    ->where('codigo', $codigoEstudiante)
                    ->first();

                if (! $student) {
                    $errores[] = "{$hojaNombre} - Fila {$fila}: Estudiante código {$codigoEstudiante} no encontrado.";
                    $fila++;
                    continue;
                }

                if ((int) $student->course_id !== (int) $course->id) {
                    $cursoEstudiante =
                        optional($student->course)->curso
                        ?? $student->course_id;

                    $errores[] =
                        "{$hojaNombre} - Fila {$fila}: El estudiante {$codigoEstudiante} pertenece al curso {$cursoEstudiante} y no al curso {$cursoCodigo}.";

                    $fila++;
                    continue;
                }

                $notasPorPeriodo = [
                    1 => $sheet->getCell("D{$fila}")->getValue(),
                    2 => $sheet->getCell("F{$fila}")->getValue(),
                    3 => $sheet->getCell("H{$fila}")->getValue(),
                    4 => $sheet->getCell("J{$fila}")->getValue(),
                ];

                foreach ($notasPorPeriodo as $periodoNota => $nota) {
                    if ($nota === null || $nota === '') {
                        continue;
                    }

                    $notaOriginal = trim((string) $nota);

                    if (! is_numeric($notaOriginal)) {
                        $errores[] = "{$hojaNombre} - Fila {$fila}: La nota '{$notaOriginal}' no es numérica.";
                        continue;
                    }

                    $nota = (float) $notaOriginal;

                    if ($nota < 0) {
                        $errores[] = "{$hojaNombre} - Fila {$fila}: La nota {$nota} no puede ser negativa.";
                        continue;
                    }

                    if ($nota == 0) {
                        continue;
                    }

                    if ($nota > 100) {
                        $errores[] = "{$hojaNombre} - Fila {$fila}: La nota {$nota} está fuera del rango permitido (0-100).";
                        continue;
                    }

                    NotaEstudiante::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'pensum_academico_id' => $pensum->id,
                            'periodo' => (string) $periodoNota,
                        ],
                        [
                            'nota' => $nota,
                            'fallas' => 0,
                        ]
                    );

                    $registrosImportados++;
                }

                $fila++;
            }

            $hojasProcesadas++;
        }

        


        $this->mostrarModalImportar = false;
        $this->archivoExcel = null;

        $this->cargarEstudiantes();

        $this->erroresImportacion = $errores;

        if (count($errores) === 0) {

            Notification::make()
                ->title('Importación completada')
                ->body(
                    "Se procesaron {$hojasProcesadas} hojas y se importaron {$registrosImportados} registros correctamente."
                )
                ->success()
                ->send();

        } else {

            Notification::make()
                ->title('Importación finalizada con observaciones')
                ->body(
                    "Hojas procesadas: {$hojasProcesadas}. " .
                    "Registros importados: {$registrosImportados}. " .
                    "Inconsistencias encontradas: " . count($errores)
                )
                ->warning()
                ->send();

            $this->mostrarModalErrores = true;
        }
    }

    public function abrirModalErrores(): void
    {
        $this->mostrarModalErrores = true;
    }

    public function cerrarModalErrores(): void
    {
        $this->mostrarModalErrores = false;
    }

    public function getTotalEstudiantesProperty(): int
    {
        return count($this->estudiantes);
    }

    public function getNotasRegistradasProperty(): int
    {
        return collect($this->estudiantes)
            ->filter(fn ($e) => isset($e['nota']) && $e['nota'] !== '' && is_numeric($e['nota']))
            ->count();
    }

    public function getNotasPendientesProperty(): int
    {
        return $this->totalEstudiantes - $this->notasRegistradas;
    }

    public function getPromedioCursoProperty(): ?float
    {
        $notas = collect($this->estudiantes)
            ->pluck('nota')
            ->filter(fn ($nota) => $nota !== '' && is_numeric($nota))
            ->map(fn ($nota) => (float) $nota);

        if ($notas->isEmpty()) {
            return null;
        }

        return round($notas->avg(), 2);
    }

    public function getMejorDesempenoProperty(): ?array
    {
        return collect($this->estudiantes)
            ->filter(fn ($e) => isset($e['nota']) && $e['nota'] !== '' && is_numeric($e['nota']))
            ->sortByDesc(fn ($e) => (float) $e['nota'])
            ->first();
    }

    public function getRequiereApoyoProperty(): ?array
    {
        return collect($this->estudiantes)
            ->filter(fn ($e) => isset($e['nota']) && $e['nota'] !== '' && is_numeric($e['nota']))
            ->sortBy(fn ($e) => (float) $e['nota'])
            ->first();
    }
       
    
    public function updatedEstudiantes(): void
    {
        $this->validarNotas();
        $this->calcularIndicadores();
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'estudiantes.')) {

            $this->hayCambiosSinGuardar = true;

            $this->calcularIndicadores();

            $this->dispatch('notas-cambios-sin-guardar', estado: true);
        }
    }

    public function cargarRangosDesempeno(): void
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

    public function obtenerDesempeno($nota): string
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


    public function exportarExcel()
    {
        $user = Auth::user();
        $esAdmin = $user->hasAnyRole(['superadmin', 'admin']);

        if ($esAdmin) {
            if (! $this->docenteExportarId) {
                Notification::make()
                    ->title('Seleccione un docente')
                    ->body('Debe seleccionar un docente para exportar las planillas.')
                    ->warning()
                    ->send();

                return;
            }

            $docenteSesion = Docente::find($this->docenteExportarId);
        } else {
            $docenteSesion = Docente::where('user_id', $user->id)->first();

            if (! $docenteSesion) {
                Notification::make()
                    ->title('No se puede exportar')
                    ->body('Comuníquese con administración para configurar su perfil docente ya que su usuario no tiene un docente asociado.')
                    ->warning()
                    ->send();

                return;
            }
        }

        if (! $docenteSesion) {
            Notification::make()
                ->title('Docente no encontrado')
                ->warning()
                ->send();

            return;
        }

        $asignaciones = DocenteAsignatura::query()
            ->with(['docente', 'course', 'pensumAcademico'])
            ->where('docente_id', $docenteSesion->id)
            ->get();

        if ($asignaciones->isEmpty()) {
            Notification::make()
                ->title('Sin asignaciones')
                ->body('El docente seleccionado no tiene cursos o asignaturas asignadas.')
                ->warning()
                ->send();

            return;
        }

        $templatePath = storage_path('app/templates/plantilla_notas_rembrandt.xlsx');

        $spreadsheet = IOFactory::load($templatePath);
        $templateSheet = clone $spreadsheet->getActiveSheet();

        while ($spreadsheet->getSheetCount() > 0) {
            $spreadsheet->removeSheetByIndex(0);
        }

        foreach ($asignaciones as $asignacion) {
            $pensum = $asignacion->pensumAcademico;
            $curso = $asignacion->course;

            if ($pensum && $curso) {
                $pensum = PensumAcademico::query()
                    ->where('periodo_lectivo_id', $curso->periodo_lectivo_id)
                    ->where('sede_id', $curso->sede_id)
                    ->where('grado', $curso->grado)
                    ->where('estado', 'activo')
                    ->where(function ($query) use ($pensum) {
                        $query->where('codigo', $pensum->codigo)
                            ->orWhere('nombre', $pensum->nombre);
                    })
                    ->first();
            }

            if (! $pensum || ! $curso) {
                continue;
            }

            $sheet = clone $templateSheet;

            $nombreHoja = substr(
                preg_replace('/[\\\\\\/\\?\\*\\[\\]\\:]/', '', ($curso->curso ?? $curso->codigo ?? 'Curso') . ' ' . $pensum->nombre),
                0,
                31
            );

            $sheet->setTitle($nombreHoja);

            $spreadsheet->addSheet($sheet);

            $docenteNombre = $docenteSesion->nombre_completo
                ?? trim(($docenteSesion->nombres ?? '') . ' ' . ($docenteSesion->apellidos ?? ''));

            $sheet->setCellValue('C4', 'DOCENTE: ' . $docenteNombre);
            $sheet->setCellValue('F2', $pensum->codigo ?? '');
            $sheet->setCellValue('G2', $pensum->nombre ?? '');
            $sheet->setCellValue('F4', $curso->curso ?? $curso->codigo ?? '');

            $estudiantes = Student::query()
                ->where('course_id', $curso->id)
                ->orderBy('primer_apellido')
                ->orderBy('segundo_apellido')
                ->orderBy('primer_nombre')
                ->orderBy('segundo_nombre')
                ->get();

            $fila = 8;
            $ultimoPeriodoConNotas = 1;

            foreach ($estudiantes as $index => $student) {
                $notas = NotaEstudiante::query()
                    ->where('student_id', $student->id)
                    ->where('pensum_academico_id', $pensum->id)
                    ->get()
                    ->keyBy('periodo');

                $notaP1 = $notas->get('1')?->nota;
                $notaP2 = $notas->get('2')?->nota;
                $notaP3 = $notas->get('3')?->nota;
                $notaP4 = $notas->get('4')?->nota;

                if (is_numeric($notaP1) && (float) $notaP1 > 0) {
                    $ultimoPeriodoConNotas = max($ultimoPeriodoConNotas, 1);
                }

                if (is_numeric($notaP2) && (float) $notaP2 > 0) {
                    $ultimoPeriodoConNotas = max($ultimoPeriodoConNotas, 2);
                }

                if (is_numeric($notaP3) && (float) $notaP3 > 0) {
                    $ultimoPeriodoConNotas = max($ultimoPeriodoConNotas, 3);
                }

                if (is_numeric($notaP4) && (float) $notaP4 > 0) {
                    $ultimoPeriodoConNotas = max($ultimoPeriodoConNotas, 4);
                }

                $sheet->setCellValue("A{$fila}", $index + 1);
                $sheet->setCellValue("B{$fila}", $student->codigo);
                $nombreEstudiante = trim(
                    ($student->primer_apellido ?? '') . ' ' .
                    ($student->segundo_apellido ?? '') . ' ' .
                    ($student->primer_nombre ?? '') . ' ' .
                    ($student->segundo_nombre ?? '')
                );

                $sheet->setCellValue("C{$fila}", $nombreEstudiante);

                $sheet->setCellValue("D{$fila}", $notaP1);
                $sheet->setCellValue("F{$fila}", $notaP2);
                $sheet->setCellValue("H{$fila}", $notaP3);
                $sheet->setCellValue("J{$fila}", $notaP4);

                $sheet->setCellValue("O{$fila}", $notas->get((string) $ultimoPeriodoConNotas)?->fallas ?? 0);
                $sheet->setCellValue("P{$fila}", $notas->get((string) $ultimoPeriodoConNotas)?->mejoramiento_01);
                $sheet->setCellValue("Q{$fila}", $notas->get((string) $ultimoPeriodoConNotas)?->mejoramiento_02);
                $sheet->setCellValue("R{$fila}", $notas->get((string) $ultimoPeriodoConNotas)?->mejoramiento_03);
                $sheet->setCellValue("S{$fila}", $notas->get((string) $ultimoPeriodoConNotas)?->mejoramiento_04);
                $sheet->setCellValue("T{$fila}", $notas->get((string) $ultimoPeriodoConNotas)?->pgc);

                $fila++;
            }

            $sheet->setCellValue('F3', $ultimoPeriodoConNotas);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $this->mostrarModalExportar = false;
        $this->docenteExportarId = null;

        $fileName = 'planillas-notas-' . str($docenteSesion->nombre_completo ?? 'docente')->slug() . '-' . now()->format('Y-m-d-H-i') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName);
    }

       



    public function abrirModalExportar()
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['superadmin', 'admin'])) {

            $this->docentesExportar = Docente::query()
                ->where('estado', 'Activo')
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->get()
                ->map(fn ($docente) => [
                    'id' => $docente->id,
                    'nombre' => $docente->nombre_completo,
                ])
                ->toArray();

            $this->docenteExportarId = null;
            $this->mostrarModalExportar = true;

            return;
        }

        return $this->exportarExcel();
    }

    public function cerrarModalExportar(): void
    {
        $this->mostrarModalExportar = false;
        $this->docenteExportarId = null;
    }
    


    public function getDocentesFiltradosExportarProperty()
    {
        return collect($this->docentesExportar)
            ->filter(function ($docente) {

                if ($this->buscarDocenteExportar === '') {
                    return true;
                }

                return str_contains(
                    mb_strtolower($docente['nombre']),
                    mb_strtolower($this->buscarDocenteExportar)
                );
            })
            ->values();
    }


    public function validarNotas(): void
    {
        $this->erroresNotas = [];
        $this->hayErroresNotas = false;

        foreach ($this->estudiantes as $index => $estudiante) {

            $notaOriginal = $estudiante['nota'] ?? '';

            $notaTexto = trim((string) $notaOriginal);

            // vacío permitido
            if ($notaTexto === '') {
                continue;
            }

            // bloquear letras, e, símbolos raros
            if (!preg_match('/^\d+(\.\d+)?$/', $notaTexto)) {

                $this->erroresNotas[$index] =
                    'Solo números entre 0 y 100';

                $this->hayErroresNotas = true;

                continue;
            }

            $nota = (float) $notaTexto;

            if ($nota < 0 || $nota > 100) {

                $this->erroresNotas[$index] =
                    'La nota debe estar entre 0 y 100';

                $this->hayErroresNotas = true;
            }
        }
    }

    

    



}