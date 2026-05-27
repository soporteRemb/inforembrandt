<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\PensumAcademico;
use App\Models\Student;
use App\Models\PeriodoLectivo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\NotaEstudiante;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\RangoDesempenoNota;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
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




    public function mount(): void
    {
        $this->form->fill();
        $this->cargarRangosDesempeno();
    }


    public function updatedDataCourseId($value): void
    {
        $this->cargarEstudiantes();

        $curso = Course::find($value);

        $this->cursoSeleccionado = $curso
            ? $curso->curso . ' - ' . $curso->descripcion
            : null;

        $pensumId = $this->data['pensum_academico_id'] ?? null;
        $pensum = PensumAcademico::find($pensumId);

        $this->asignaturaSeleccionada = $pensum?->nombre;

        $this->periodoTexto = match($this->data['periodo'] ?? null) {
            '1' => 'Primer periodo',
            '2' => 'Segundo periodo',
            '3' => 'Tercer periodo',
            '4' => 'Cuarto periodo',
            default => null,
        };
    }

    

    public function updatedDataPensumAcademicoId($value): void
    {
        $this->cargarEstudiantes();
    }

    public function updatedDataPeriodo($value): void
    {
        $this->cargarEstudiantes();
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
            ->orderBy('primer_nombre')
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
                            ->default(fn () => PeriodoLectivo::query()
                                ->where('sede_id', Auth::user()?->sede_id)
                                ->where('estado', 'abierto')
                                ->latest('id')
                                ->value('id'))
                            ->live(),

                        Forms\Components\Select::make('course_id')
                            ->label('Curso')
                            ->options(function (Forms\Get $get) {
                                $periodoId = $get('periodo_lectivo_id');

                                if (! $periodoId) {
                                    return [];
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
                            ->live(),

                        Forms\Components\Select::make('pensum_academico_id')
                            ->label('Asignatura')
                            ->options(function (Forms\Get $get) {
                                $courseId = $get('course_id');
                                $periodoId = $get('periodo_lectivo_id');

                                if (! $courseId || ! $periodoId) {
                                    return [];
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
                            ->live(),

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
                            ->live(),

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

    public function guardarNotas(): void
    {
        $pensumId = $this->data['pensum_academico_id'] ?? null;
        $periodo = $this->data['periodo'] ?? null;

        if (! $pensumId || ! $periodo) {
            \Filament\Notifications\Notification::make()
                ->title('Seleccione asignatura y periodo académico')
                ->warning()
                ->send();

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
                    'nota' => $estudiante['nota'] !== '' ? $estudiante['nota'] : null,
                    'fallas' => $estudiante['fallas'] ?? 0,
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
        $this->dispatch('notas-cambios-sin-guardar', estado: false);


        \Filament\Notifications\Notification::make()
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

        $path = $this->archivoExcel->getRealPath();

        $spreadsheet = IOFactory::load($path);

        $hojasProcesadas = 0;
        $registrosImportados = 0;
        $errores = [];
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

                foreach ($notasPorPeriodo as $periodo => $nota) {
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
                            'periodo' => (string) $periodo,
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
            }
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



    public function exportarExcel(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Notas');

        $pensumId = $this->data['pensum_academico_id'] ?? null;
        $cursoId = $this->data['course_id'] ?? null;
        $periodo = $this->data['periodo'] ?? null;

        $pensum = PensumAcademico::find($pensumId);
        $curso = Course::find($cursoId);

        $nombreMateria = $pensum?->nombre ?? 'Asignatura';
        $codigoMateria = $pensum?->codigo ?? '';
        $nombreCurso = $curso?->curso ?? 'Curso';
        $docente = $pensum?->docente?->nombre_completo
            ?? $pensum?->docente?->nombres
            ?? $pensum?->docente?->nombre
            ?? $pensum?->teacher?->nombre_completo
            ?? $pensum?->teacher?->nombres
            ?? $pensum?->teacher?->nombre
            ?? 'Docente no asignado';

        /*
        |--------------------------------------------------------------------------
        | Encabezado
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'COLEGIO REMBRANDT');

        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'PLANILLA DE NOTAS');

        $sheet->setCellValue('A4', 'DOCENTE:');
        $sheet->mergeCells('B4:D4');
        $sheet->setCellValue('B4', $docente);

        $sheet->setCellValue('F4', 'MATERIA:');
        $sheet->mergeCells('G4:I4');
        $sheet->setCellValue('G4', trim($codigoMateria . ' ' . $nombreMateria));

        $sheet->setCellValue('F5', 'PERIODO:');
        $sheet->setCellValue('G5', $periodo);

        $sheet->setCellValue('F6', 'CURSO:');
        $sheet->setCellValue('G6', $nombreCurso);

        /*
        |--------------------------------------------------------------------------
        | Encabezados tabla
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue('A8', 'Estudiante');
        $sheet->setCellValue('B8', 'Nota');
        $sheet->setCellValue('C8', 'Desempeño');
        $sheet->setCellValue('D8', 'Fallas');
        $sheet->setCellValue('E8', '01');
        $sheet->setCellValue('F8', '02');
        $sheet->setCellValue('G8', '03');
        $sheet->setCellValue('H8', '04');
        $sheet->setCellValue('I8', 'PGC');

        /*
        |--------------------------------------------------------------------------
        | Datos
        |--------------------------------------------------------------------------
        */

        $fila = 9;

        foreach ($this->estudiantes as $estudiante) {
            $sheet->setCellValue("A{$fila}", $estudiante['nombre']);
            $sheet->setCellValue("B{$fila}", $estudiante['nota']);
            $sheet->setCellValue("C{$fila}", $this->obtenerDesempeno($estudiante['nota']));
            $sheet->setCellValue("D{$fila}", $estudiante['fallas'] ?? 0);
            $sheet->setCellValue("E{$fila}", $estudiante['mejoramientos']['01'] ?? null);
            $sheet->setCellValue("F{$fila}", $estudiante['mejoramientos']['02'] ?? null);
            $sheet->setCellValue("G{$fila}", $estudiante['mejoramientos']['03'] ?? null);
            $sheet->setCellValue("H{$fila}", $estudiante['mejoramientos']['04'] ?? null);
            $sheet->setCellValue("I{$fila}", $estudiante['pgc'] ?? null);

            $fila++;
        }

        $ultimaFila = $fila - 1;

        /*
        |--------------------------------------------------------------------------
        | Estilos
        |--------------------------------------------------------------------------
        */

        $rojo = '991B1B';
        $rojoOscuro = '7F1D1D';
        $grisClaro = 'F8FAFC';
        $grisBorde = 'CBD5E1';
        $filaAlterna = 'F1F5F9';

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => $rojo]],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => $rojo],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('A4:I6')->applyFromArray([
            'font' => ['size' => 11],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => $grisBorde],
                ],
            ],
        ]);

        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('F4:F6')->getFont()->setBold(true);

        $sheet->getStyle('A8:I8')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => $rojoOscuro],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle("A8:I{$ultimaFila}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => $grisBorde],
                ],
            ],
        ]);

        for ($i = 9; $i <= $ultimaFila; $i++) {
            if ($i % 2 === 0) {
                $sheet->getStyle("A{$i}:I{$i}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($filaAlterna);
            }
        }

        $sheet->getStyle("B9:I{$ultimaFila}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A9:A{$ultimaFila}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheet->getColumnDimension('A')->setWidth(38);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(8);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(8);
        $sheet->getColumnDimension('H')->setWidth(8);
        $sheet->getColumnDimension('I')->setWidth(10);

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(8)->setRowHeight(22);

        $sheet->freezePane('A9');
        $sheet->setAutoFilter("A8:I{$ultimaFila}");

        $fileName = 'notas-academicas-' . now()->format('Y-m-d-H-i') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName);
    }    
    





}