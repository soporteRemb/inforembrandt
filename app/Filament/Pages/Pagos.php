<?php

namespace App\Filament\Pages;

use App\Models\PeriodoLectivo;
use App\Services\Financiero\Pagos\PagosService;
use Filament\Pages\Page;

class Pagos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Financiero';

    protected static ?string $navigationLabel = 'Pagos';

    protected static ?string $title = 'Pagos';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.pagos';

    protected static ?string $slug = 'pagos';

    /*
    |--------------------------------------------------------------------------
    | CONTEXTO ACTIVO
    |--------------------------------------------------------------------------
    */

    public ?int $sede_id = null;

    public ?int $periodo_lectivo_id = null;

    /*
    |--------------------------------------------------------------------------
    | BÚSQUEDA DEL ESTUDIANTE
    |--------------------------------------------------------------------------
    */

    public string $buscarEstudiante = '';

    public array $resultadosBusqueda = [];

    public ?int $student_id = null;

    public array $estudianteSeleccionado = [];

    public array $resumenFinanciero = [
        'deuda_obligatoria' => 0,
        'penalizaciones' => 0,
        'otros_conceptos' => 0,
        'total_pendiente' => 0,
        'saldo_favor' => 0,
        'total_neto' => 0,
        'cantidad_obligaciones' => 0,
    ];

    /*
    |--------------------------------------------------------------------------
    | CARGA INICIAL
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->sede_id = session('sede_id');

        $this->periodo_lectivo_id = session('periodo_lectivo_id');

        if ($this->sede_id && ! $this->periodo_lectivo_id) {
            $this->periodo_lectivo_id = PeriodoLectivo::query()
                ->where('sede_id', $this->sede_id)
                ->orderByDesc('nombre')
                ->value('id');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BÚSQUEDA EN TIEMPO REAL
    |--------------------------------------------------------------------------
    */

    public function updatedBuscarEstudiante(): void
    {
        $termino = trim($this->buscarEstudiante);

        if (
            mb_strlen($termino) < 2
            || ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            $this->resultadosBusqueda = [];

            return;
        }

        $estudiantes = app(PagosService::class)
            ->cartera()
            ->buscarEstudiantes(
                sedeId: $this->sede_id,
                periodoLectivoId: $this->periodo_lectivo_id,
                termino: $termino,
            );

        $this->resultadosBusqueda = $estudiantes
            ->map(function ($student) {
                $nombreCompleto = trim(
                    ($student->primer_nombre ?? '') . ' ' .
                    ($student->segundo_nombre ?? '') . ' ' .
                    ($student->primer_apellido ?? '') . ' ' .
                    ($student->segundo_apellido ?? '')
                );

                return [
                    'id' => $student->id,
                    'nombre' => $nombreCompleto,
                    'documento' => $student->documento ?: '-',
                    'codigo' => $student->codigo ?: '-',
                    'curso' => $student->course?->curso
                        ?? $student->course?->descripcion
                        ?? '-',
                    'grado' => $student->course?->grado
                        ?? $student->ultimo_grado
                        ?? '-',
                    'estado' => $student->estado ?: '-',
                ];
            })
            ->values()
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | SELECCIONAR ESTUDIANTE
    |--------------------------------------------------------------------------
    */

    public function seleccionarEstudiante(int $studentId): void
    {
        if (! $this->sede_id || ! $this->periodo_lectivo_id) {
            return;
        }

        $student = app(PagosService::class)
            ->cartera()
            ->obtenerEstudiante(
                studentId: $studentId,
                sedeId: $this->sede_id,
                periodoLectivoId: $this->periodo_lectivo_id,
            );

        if (! $student) {
            $this->limpiarEstudiante();

            return;
        }

        $nombreCompleto = trim(
            ($student->primer_nombre ?? '') . ' ' .
            ($student->segundo_nombre ?? '') . ' ' .
            ($student->primer_apellido ?? '') . ' ' .
            ($student->segundo_apellido ?? '')
        );

        $iniciales = mb_strtoupper(
            mb_substr($student->primer_nombre ?? '', 0, 1) .
            mb_substr($student->primer_apellido ?? '', 0, 1)
        );

        $acudiente = $student->guardians
            ->firstWhere('pivot.estado', 'activo')
            ?? $student->guardians->first();

        $this->student_id = $student->id;

        $this->estudianteSeleccionado = [
            'id' => $student->id,
            'nombre' => $nombreCompleto,
            'iniciales' => $iniciales ?: 'ES',
            'documento' => $student->documento ?: '-',
            'tipo_documento' => $student->tipo_documento ?: '-',
            'codigo' => $student->codigo ?: '-',
            'foto' => $student->foto,
            'grado' => $student->course?->grado
                ?? $student->ultimo_grado
                ?? '-',
            'curso' => $student->course?->curso
                ?? $student->course?->descripcion
                ?? '-',
            'jornada' => $student->course?->jornada ?? 'Completa',
            'estado' => $student->estado ?: '-',
            'acudiente' => $acudiente?->nombre ?? '',
            'parentesco' => $acudiente?->parentesco
                ?? $acudiente?->pivot?->tipo
                ?? '',
        ];

        $this->resumenFinanciero = app(PagosService::class)
            ->cartera()
            ->obtenerResumen(
                studentId: $student->id,
                sedeId: $this->sede_id,
                periodoLectivoId: $this->periodo_lectivo_id,
            );
        $this->buscarEstudiante = $nombreCompleto;

        $this->resultadosBusqueda = [];
    }

    /*
    |--------------------------------------------------------------------------
    | LIMPIAR ESTUDIANTE
    |--------------------------------------------------------------------------
    */

    public function limpiarEstudiante(): void
    {
        $this->student_id = null;

        $this->estudianteSeleccionado = [];

        $this->buscarEstudiante = '';

        $this->resultadosBusqueda = [];
        
        $this->resumenFinanciero = [
            'deuda_obligatoria' => 0,
            'penalizaciones' => 0,
            'otros_conceptos' => 0,
            'total_pendiente' => 0,
            'saldo_favor' => 0,
            'total_neto' => 0,
            'cantidad_obligaciones' => 0,
        ];
    }

    public function getHeading(): string
    {
        return '';
    }
}