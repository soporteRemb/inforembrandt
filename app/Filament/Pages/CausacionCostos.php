<?php

namespace App\Filament\Pages;

use App\Models\AsignacionConcepto;
use App\Models\ConceptoCobro;
use App\Models\PeriodoLectivo;
use App\Models\Sede;
use App\Models\MovimientoCarteraEstudiante;
use App\Models\ReciboPago;
use App\Models\Course;
use App\Models\Student;

use App\Traits\HasPagePermissions;


use App\Services\Financiero\CausacionCostosService;

use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Notifications\Notification;
use App\Services\Financiero\CausacionMasivaService;

use Illuminate\Support\Facades\Auth;

class CausacionCostos extends Page
{

    use HasPagePermissions;

    protected static ?string $viewPermission =
        'ver_causacion_costos';


    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Financiero';
    protected static ?string $navigationLabel = 'Causación de Costos';
    protected static ?string $title = 'Causación de Costos';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.causacion-costos';

    public ?int $sede_id = null;
    public ?int $periodo_lectivo_id = null;

    public ?string $grado_obligatorio = null;
    public ?int $concepto_obligatorio_id = null;
    public ?string $mes_pension = null;

    public ?string $grado_no_obligatorio = null;
    public ?int $concepto_no_obligatorio_id = null;
    public string $curso_no_obligatorio = 'todos';

    public array $estudiantesNoObligatorios = [];

    public array $estudiantesSeleccionadosNoObligatorios = [];

    public array $movimientosSeleccionadosReversion = [];

    public string $buscarEstudianteNoObligatorio = '';

    public string $valor_no_obligatorio = '';

    public string $filtroHistorialBuscar = '';
    public string $filtroHistorialEstado = '';
    public string $filtroHistorialGrado = '';

    public array $resumenObligatorio = [
        'estudiantes' => 0,
        'tarifa_base' => 0,
        'valor_base_total' => 0,
        'personalizados' => 0,
        'diferencia_personalizados' => 0,
        'total_causar' => 0,
    ];

    public array $resumenNoObligatorio = [
        'estudiantes' => 0,
        'tarifa_base' => 0,
        'valor_base_total' => 0,
        'personalizados' => 0,
        'diferencia_personalizados' => 0,
        'total_causar' => 0,
    ];

    public array $meses = [
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
    ];

    public array $historialCausaciones = [];

    public bool $mostrarModalReversar = false;

    public bool $mostrarModalReversionBloqueada = false;

    public array $detalleReversionBloqueada = [
        'concepto' => '',
        'grado' => '',
        'mes' => '',
        'estudiantes_con_pagos' => 0,
        'movimientos_con_pagos' => 0,
    ];

    public string $motivoReversion = '';

    public array $confirmacionReversion = [
        'concepto' => '',
        'grado' => '',
        'mes' => '',
        'estudiantes' => 0,
        'total' => 0,
    ];


    public bool $mostrarModalDetalleHistorial = false;

    public array $detalleHistorial = [
        'general' => [],
        'movimientos' => [],
    ];

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function mount(): void
    {
        $this->sede_id = session('sede_id');

        $this->periodo_lectivo_id = session('periodo_lectivo_id');

        if (! $this->periodo_lectivo_id) {
            $this->periodo_lectivo_id = PeriodoLectivo::query()
                ->where('sede_id', $this->sede_id)
                ->orderByDesc('nombre')
                ->value('id');
        }

        $this->cargarHistorialCausaciones();
    }

    public function getGradosProperty()
    {
        return AsignacionConcepto::query()
            ->where('sede_id', $this->sede_id)
            ->where('periodo_lectivo_id', $this->periodo_lectivo_id)
            ->where('activo', true)
            ->where('grado', '!=', 'todos')
            ->orderBy('grado')
            ->pluck('grado', 'grado')
            ->unique();
    }

    public function getCursosNoObligatoriosProperty()
    {
        if (
            ! $this->sede_id
            || ! $this->periodo_lectivo_id
            || ! $this->grado_no_obligatorio
            || $this->grado_no_obligatorio === 'todos'
        ) {
            return collect();
        }

        return Course::query()
            ->where('sede_id', $this->sede_id)
            ->where(
                'periodo_lectivo_id',
                $this->periodo_lectivo_id
            )
            ->where('grado', $this->grado_no_obligatorio)
            ->orderBy('curso')
            ->get()
            ->mapWithKeys(function ($course) {
                return [
                    (string) $course->id =>
                        $course->curso
                        ?: $course->descripcion
                        ?: 'Curso ' . $course->id,
                ];
            });
    }

    public function getConceptosObligatoriosProperty()
    {
        return ConceptoCobro::query()
            ->where('sede_id', $this->sede_id)
            ->where('periodo_lectivo_id', $this->periodo_lectivo_id)
            ->where('obligatorio', true)
            ->where('activo', true)
            ->orderBy('codigo')
            ->get()
            ->mapWithKeys(fn ($c) => [
                $c->id => $c->codigo . ' - ' . $c->descripcion,
            ]);
    }

    public function getConceptosNoObligatoriosProperty()
    {
        if (
            ! $this->sede_id
            || ! $this->periodo_lectivo_id
            || ! $this->grado_no_obligatorio
        ) {
            return collect();
        }

        return AsignacionConcepto::query()
            ->with('conceptoCobro')
            ->where('sede_id', $this->sede_id)
            ->where(
                'periodo_lectivo_id',
                $this->periodo_lectivo_id
            )
            ->where('activo', true)

            /*
            |--------------------------------------------------------------------------
            | Alcance por grado
            |--------------------------------------------------------------------------
            |
            | Todos los grados:
            | muestra todas las asignaciones existentes, sin importar el grado.
            |
            | Grado específico:
            | muestra las asignaciones propias del grado y las generales.
            */
            ->when(
                $this->grado_no_obligatorio !== 'todos',
                function ($query) {
                    $query->whereIn('grado', [
                        $this->grado_no_obligatorio,
                        'todos',
                    ]);
                }
            )

            ->whereHas('conceptoCobro', function ($query) {
                $query
                    ->where('obligatorio', false)
                    ->where('activo', true);
            })
            ->get()

            /*
            * Cuando se selecciona un grado específico,
            * prioriza su asignación sobre la general.
            */
            ->sortBy(function ($asignacion) {
                return [
                    $asignacion->grado === 'todos' ? 1 : 0,
                    (int) (
                        $asignacion->conceptoCobro?->codigo
                        ?? 0
                    ),
                ];
            })

            /*
            * Un concepto puede estar asignado a varios grados.
            * En la lista debe aparecer una sola vez.
            */
            ->unique('concepto_cobro_id')

            ->sortBy(function ($asignacion) {
                return (int) (
                    $asignacion->conceptoCobro?->codigo
                    ?? 0
                );
            })

            ->mapWithKeys(function ($asignacion) {
                $concepto = $asignacion->conceptoCobro;

                return [
                    $concepto->id =>
                        $concepto->codigo
                        . ' - '
                        . $concepto->descripcion,
                ];
            });
    }

    public function updatedGradoObligatorio(): void
    {
        $this->calcularResumenObligatorio();
    }

    public function updatedConceptoObligatorioId(): void
    {
        if (! $this->conceptoObligatorioEsPension()) {
            $this->mes_pension = null;
        }

        $this->calcularResumenObligatorio();
    }

    public function updatedMesPension(): void
    {
        $this->calcularResumenObligatorio();
    }

    public function updatedGradoNoObligatorio(): void
    {
        $this->curso_no_obligatorio = 'todos';
        $this->concepto_no_obligatorio_id = null;
        $this->valor_no_obligatorio = '';

        $this->estudiantesNoObligatorios = [];
        $this->estudiantesSeleccionadosNoObligatorios = [];

        $this->calcularResumenNoObligatorio();
    }

    public function updatedCursoNoObligatorio(): void
    {
        $this->cargarEstudiantesNoObligatorios();
        $this->calcularResumenNoObligatorio();
    }

    public function updatedConceptoNoObligatorioId(): void
    {
        $this->valor_no_obligatorio = '';

        if (
            $this->sede_id
            && $this->periodo_lectivo_id
            && $this->grado_no_obligatorio
            && $this->concepto_no_obligatorio_id
        ) {
            $asignacion = AsignacionConcepto::query()
                ->where('sede_id', $this->sede_id)
                ->where(
                    'periodo_lectivo_id',
                    $this->periodo_lectivo_id
                )
                ->where(
                    'concepto_cobro_id',
                    $this->concepto_no_obligatorio_id
                )
                ->where('activo', true)
                ->when(
                    $this->grado_no_obligatorio !== 'todos',
                    function ($query) {
                        $query->whereIn('grado', [
                            $this->grado_no_obligatorio,
                            'todos',
                        ]);
                    }
                )
                ->orderByRaw(
                    'CASE WHEN grado = ? THEN 0 ELSE 1 END',
                    [$this->grado_no_obligatorio]
                )
                ->first();

            if (
                $asignacion
                && $asignacion->tarifa_ordinaria !== null
            ) {
                $this->valor_no_obligatorio =
                    $this->formatearMonedaVisual(
                        $asignacion->tarifa_ordinaria
                    );
            }
        }

        $this->cargarEstudiantesNoObligatorios();
        $this->calcularResumenNoObligatorio();
    }

    public function seleccionarTodosNoObligatorios(): void
    {
        $this->estudiantesSeleccionadosNoObligatorios =
            collect($this->estudiantesNoObligatorios)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->toArray();

        $this->calcularResumenNoObligatorio();
    }

    public function desmarcarTodosNoObligatorios(): void
    {
        $this->estudiantesSeleccionadosNoObligatorios = [];

        $this->calcularResumenNoObligatorio();
    }

    public function calcularResumenObligatorio(): void
    {
        $this->resumenObligatorio = app(CausacionCostosService::class)->calcularResumen(
            sedeId: $this->sede_id,
            periodoLectivoId: $this->periodo_lectivo_id,
            grado: $this->grado_obligatorio,
            conceptoCobroId: $this->concepto_obligatorio_id,
            mesNumero: $this->conceptoObligatorioEsPension() && $this->mes_pension
                ? (int) $this->mes_pension
                : null,
        );
    }

    public function calcularResumenNoObligatorio(): void
    {
        $courseId = (
            $this->curso_no_obligatorio !== 'todos'
            && is_numeric($this->curso_no_obligatorio)
        )
            ? (int) $this->curso_no_obligatorio
            : null;

        $valorManual = trim(
            $this->valor_no_obligatorio
        ) !== ''
            ? $this->convertirMonedaAFloat(
                $this->valor_no_obligatorio
            )
            : null;

        $this->resumenNoObligatorio = app(
            CausacionCostosService::class
        )->calcularResumen(
            sedeId: $this->sede_id,
            periodoLectivoId:
                $this->periodo_lectivo_id,
            grado:
                $this->grado_no_obligatorio,
            conceptoCobroId:
                $this->concepto_no_obligatorio_id,
            mesNumero: null,
            courseId: $courseId,
            studentIds:
                $this->estudiantesSeleccionadosNoObligatorios,
            valorBaseManual: $valorManual,
        );
    }


    public function getSedesProperty()
    {
        return Sede::orderBy('nombre')->pluck('nombre', 'id');
    }

    public function getPeriodosProperty()
    {
        return PeriodoLectivo::query()
            ->with('sede')
            ->where('sede_id', $this->sede_id)
            ->orderByDesc('nombre')
            ->get()
            ->mapWithKeys(fn ($periodo) => [
                $periodo->id => ($periodo->sede?->nombre ?? '') . ' - ' . $periodo->nombre,
            ]);
    }


    public function nombreSede(): string
    {
        return Sede::find($this->sede_id)?->nombre ?? 'Rembrandt';
    }

    public function nombrePeriodo(): string
    {
        $periodo = PeriodoLectivo::find($this->periodo_lectivo_id);

        return $periodo
            ? (($periodo->sede?->nombre ?? 'Rembrandt') . ' - ' . $periodo->nombre)
            : 'Rembrandt - 2026';
    }


    public function updatedSedeId(): void
    {
        $this->periodo_lectivo_id = PeriodoLectivo::query()
            ->where('sede_id', $this->sede_id)
            ->where('estado', 'abierto')
            ->value('id');

        $this->grado_obligatorio = null;
        $this->concepto_obligatorio_id = null;
        $this->mes_pension = null;
        $this->grado_no_obligatorio = null;
        $this->curso_no_obligatorio = 'todos';
        $this->concepto_no_obligatorio_id = null;
        $this->valor_no_obligatorio = '';

        $this->resetResumenes();
    }

    public function updatedPeriodoLectivoId(): void
    {
        $this->grado_obligatorio = null;
        $this->concepto_obligatorio_id = null;
        $this->mes_pension = null;
        $this->grado_no_obligatorio = null;
        $this->curso_no_obligatorio = 'todos';
        $this->concepto_no_obligatorio_id = null;
        $this->valor_no_obligatorio = '';

        $this->resetResumenes();
    }

    public function resetResumenes(): void
    {
        $this->resumenObligatorio = [
            'estudiantes'      => 0,
            'tarifa_base'      => 0,
            'valor_base_total' => 0,
            'personalizados' => 0,
            'diferencia_personalizados' => 0,
            'total_causar' => 0,
        ];

        $this->resumenNoObligatorio = [
            'estudiantes'      => 0,
            'tarifa_base'      => 0,
            'valor_base_total' => 0,
            'personalizados' => 0,
            'diferencia_personalizados' => 0,
            'total_causar' => 0,
        ];
    }


    public function conceptoObligatorioEsPension(): bool
    {
        if (! $this->concepto_obligatorio_id) {
            return false;
        }

        $concepto = ConceptoCobro::find($this->concepto_obligatorio_id);

        if (! $concepto) {
            return false;
        }

        $descripcion = mb_strtolower($concepto->descripcion);

        return str_contains($descripcion, 'pensión')
            || str_contains($descripcion, 'pension');
    }

    public function validarOrdenPension(
        string $grado,
        int $conceptoId,
        int $mesNumero
    ): bool {
        /*
        |--------------------------------------------------------------------------
        | Febrero es el primer mes de pensión
        |--------------------------------------------------------------------------
        */
        if ($mesNumero <= 2) {
            return true;
        }

        $mesesAnteriores = range(2, $mesNumero - 1);

        $mesesCausados = MovimientoCarteraEstudiante::query()
            ->where('sede_id', $this->sede_id)
            ->where(
                'periodo_lectivo_id',
                $this->periodo_lectivo_id
            )
            ->when(
                $grado !== 'todos',
                fn ($query) => $query->where('grado', $grado)
            )
            ->where('concepto_cobro_id', $conceptoId)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->whereIn('mes_numero', $mesesAnteriores)
            ->distinct()
            ->pluck('mes_numero')
            ->map(fn ($mes) => (int) $mes)
            ->toArray();

        return count(
            array_diff($mesesAnteriores, $mesesCausados)
        ) === 0;
    }
    public bool $mostrarModalCausar = false;
    public ?string $tipoCausacion = null;

    public array $confirmacionCausacion = [
        'concepto' => '',
        'grado' => '',
        'mes' => '',
        'estudiantes' => 0,
        'total' => 0,
    ];

    public function causar(): void
    {
        $obligatorioListo =
            $this->grado_obligatorio
            && $this->concepto_obligatorio_id;

        $noObligatorioListo =
            $this->grado_no_obligatorio
            && $this->concepto_no_obligatorio_id;

        

        if (
            $obligatorioListo
            && $this->conceptoObligatorioEsPension()
            && ! $this->mes_pension
        ) {
            Notification::make()
                ->title('Seleccione el mes de pensión.')
                ->warning()
                ->send();

            return;
        }

        if (
            $obligatorioListo
            && $this->conceptoObligatorioEsPension()
            && $this->mes_pension
        ) {
            $ordenValido = $this->validarOrdenPension(
                (string) $this->grado_obligatorio,
                (int) $this->concepto_obligatorio_id,
                (int) $this->mes_pension,
            );

            if (! $ordenValido) {
                Notification::make()
                    ->title('No puede saltar meses de pensión.')
                    ->body(
                        'Debe causar primero los meses anteriores '
                        . 'antes de continuar.'
                    )
                    ->warning()
                    ->send();

                return;
            }
        }

        if ($obligatorioListo && $noObligatorioListo) {
            Notification::make()
                ->title('Seleccione solo un bloque para causar.')
                ->body(
                    'Use los filtros de obligatorios o no obligatorios, '
                    . 'pero no ambos al mismo tiempo.'
                )
                ->warning()
                ->send();

            return;
        }

        if (! $obligatorioListo && ! $noObligatorioListo) {
            Notification::make()
                ->title('Filtros incompletos.')
                ->body(
                    'Seleccione grado y concepto antes de causar.'
                )
                ->warning()
                ->send();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Validaciones de conceptos no obligatorios
        |--------------------------------------------------------------------------
        */
        if ($noObligatorioListo) {
            $valorIngresado = trim(
                (string) $this->valor_no_obligatorio
            );

            if ($valorIngresado === '') {
                Notification::make()
                    ->title('Debe ingresar el valor a causar.')
                    ->body(
                        'Indique el valor que se aplicará '
                        . 'a los estudiantes seleccionados.'
                    )
                    ->warning()
                    ->send();

                return;
            }

            /*
            * Evita que un valor negativo termine convertido
            * accidentalmente en positivo al limpiar el formato.
            */
            if (str_contains($valorIngresado, '-')) {
                Notification::make()
                    ->title('El valor no puede ser negativo.')
                    ->warning()
                    ->send();

                return;
            }

            $valorCausar = $this->convertirMonedaAFloat(
                $this->valor_no_obligatorio
            );

            if ($valorCausar <= 0) {
                Notification::make()
                    ->title('Ingrese un valor mayor que cero.')
                    ->warning()
                    ->send();

                return;
            }

            $estudiantesSeleccionados = collect(
                $this->estudiantesSeleccionadosNoObligatorios
            )
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            if ($estudiantesSeleccionados->isEmpty()) {
                Notification::make()
                    ->title('Seleccione al menos un estudiante.')
                    ->body(
                        'La causación no puede realizarse '
                        . 'sin estudiantes seleccionados.'
                    )
                    ->warning()
                    ->send();

                return;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Control general de duplicados para conceptos obligatorios
        |--------------------------------------------------------------------------
        |
        | Los conceptos no obligatorios se validan estudiante por estudiante
        | dentro de CausacionMasivaService. Esto permite que los estudiantes
        | que ya tienen el concepto sean omitidos y que los nuevos sí se creen.
        |
        | Cuando se seleccionan todos los grados, también se deja que el servicio
        | realice la validación individual para no bloquear grados pendientes.
        */
        if (
            $obligatorioListo
            && $this->grado_obligatorio !== 'todos'
        ) {
            $mesNumeroValidar =
                $this->conceptoObligatorioEsPension()
                && $this->mes_pension
                    ? (int) $this->mes_pension
                    : null;

            $yaExisteCausacionActiva =
                MovimientoCarteraEstudiante::query()
                    ->where('sede_id', $this->sede_id)
                    ->where(
                        'periodo_lectivo_id',
                        $this->periodo_lectivo_id
                    )
                    ->where(
                        'grado',
                        $this->grado_obligatorio
                    )
                    ->where(
                        'concepto_cobro_id',
                        $this->concepto_obligatorio_id
                    )
                    ->where(
                        'mes_numero',
                        $mesNumeroValidar
                    )
                    ->where(
                        'tipo_movimiento',
                        'causacion'
                    )
                    ->where('estado', 'activo')
                    ->exists();

            if ($yaExisteCausacionActiva) {
                Notification::make()
                    ->title('Esta causación ya existe.')
                    ->body(
                        'Para volver a causarla, primero debe reversar '
                        . 'la causación activa.'
                    )
                    ->warning()
                    ->send();

                return;
            }
        }

        $this->tipoCausacion = $obligatorioListo
            ? 'obligatorio'
            : 'no_obligatorio';

        $conceptoId = $obligatorioListo
            ? $this->concepto_obligatorio_id
            : $this->concepto_no_obligatorio_id;

        $concepto = ConceptoCobro::find($conceptoId);

        $resumen = $obligatorioListo
            ? $this->resumenObligatorio
            : $this->resumenNoObligatorio;

        $this->confirmacionCausacion = [
            'concepto' =>
                $concepto?->codigo
                . ' - '
                . $concepto?->descripcion,

            'grado' =>
                $obligatorioListo
                    ? $this->grado_obligatorio
                    : $this->grado_no_obligatorio,

            'mes' =>
                $obligatorioListo
                && $this->mes_pension
                    ? (
                        $this->meses[
                            (int) $this->mes_pension
                        ] ?? ''
                    )
                    : '-',

            'estudiantes' => $resumen['estudiantes'],

            'valor_unitario' => $noObligatorioListo
                ? $this->convertirMonedaAFloat(
                    $this->valor_no_obligatorio
                )
                : ($resumen['tarifa_base'] ?? 0),

            'total' => $resumen['total_causar'],
        ];

        $this->mostrarModalCausar = true;
    }

    public function confirmarCausacion(): void
    {
        $esObligatorio =
            $this->tipoCausacion === 'obligatorio';

        /*
        |--------------------------------------------------------------------------
        | Segunda validación de seguridad
        |--------------------------------------------------------------------------
        |
        | Se repite aquí porque la selección o el valor pudieron cambiar
        | después de abrir el modal.
        */
        $studentIds = null;
        $valorBaseManual = null;

        if (! $esObligatorio) {
            $studentIds = collect(
                $this->estudiantesSeleccionadosNoObligatorios
            )
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->toArray();

            if ($studentIds === []) {
                $this->mostrarModalCausar = false;

                Notification::make()
                    ->title('Seleccione al menos un estudiante.')
                    ->warning()
                    ->send();

                return;
            }

            $valorBaseManual =
                $this->convertirMonedaAFloat(
                    $this->valor_no_obligatorio
                );

            if ($valorBaseManual <= 0) {
                $this->mostrarModalCausar = false;

                Notification::make()
                    ->title(
                        'Ingrese un valor mayor que cero.'
                    )
                    ->warning()
                    ->send();

                return;
            }
        }

        $resultado = app(
            CausacionMasivaService::class
        )->causar(
            sedeId: $this->sede_id,
            periodoLectivoId:
                $this->periodo_lectivo_id,

            grado: $esObligatorio
                ? $this->grado_obligatorio
                : $this->grado_no_obligatorio,

            conceptoCobroId: $esObligatorio
                ? $this->concepto_obligatorio_id
                : $this->concepto_no_obligatorio_id,

            mesNumero:
                $esObligatorio
                && $this->conceptoObligatorioEsPension()
                && $this->mes_pension
                    ? (int) $this->mes_pension
                    : null,

            userId: Auth::id(),

            studentIds: $studentIds,

            valorBaseManual: $valorBaseManual,
        );

        $this->mostrarModalCausar = false;

        $this->limpiarFormularioCausacion();

        $this->calcularResumenObligatorio();
        $this->calcularResumenNoObligatorio();
        $this->cargarHistorialCausaciones();

        Notification::make()
            ->title(
                'Causación realizada correctamente.'
            )
            ->body(
                'Creados: '
                . $resultado['creados']
                . ' | Omitidos: '
                . $resultado['omitidos']
                . ' | Total: $'
                . number_format(
                    $resultado['total_causado'],
                    0,
                    ',',
                    '.'
                )
            )
            ->success()
            ->send();
    }


    private function obtenerMovimientosConPagosConfirmados(
        int $conceptoId,
        string $grado,
        ?int $mesNumero
    ) {
        return MovimientoCarteraEstudiante::query()
            ->where('sede_id', $this->sede_id)
            ->where(
                'periodo_lectivo_id',
                $this->periodo_lectivo_id
            )
            ->when(
                $grado !== 'todos',
                fn ($query) =>
                    $query->where('grado', $grado)
            )
            ->where('concepto_cobro_id', $conceptoId)
            ->where('mes_numero', $mesNumero)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->whereHas(
                'aplicacionesPago.reciboPago',
                function ($query) {
                    $query->where(
                        'estado',
                        ReciboPago::ESTADO_CONFIRMADO
                    );
                }
            )
            ->get();
    }



    public function reversar(): void
    {
        $obligatorioListo =
            $this->grado_obligatorio
            && $this->concepto_obligatorio_id;

        $noObligatorioListo =
            $this->grado_no_obligatorio
            && $this->concepto_no_obligatorio_id;

        /*
        |--------------------------------------------------------------------------
        | Protección temporal: reversión por curso
        |--------------------------------------------------------------------------
        |
        | La reversión todavía trabaja por grado completo. Esta validación evita
        | que el usuario seleccione un curso y termine reversando todo el grado.
        */
        if (
            $noObligatorioListo
            && $this->curso_no_obligatorio !== 'todos'
        ) {
            Notification::make()
                ->title('La reversión por curso aún no está habilitada.')
                ->body(
                    'Seleccione Curso: Todos para usar '
                    . 'la reversión actual.'
                )
                ->warning()
                ->send();

            return;
        }

        if (
            $obligatorioListo
            && $this->conceptoObligatorioEsPension()
            && ! $this->mes_pension
        ) {
            Notification::make()
                ->title('Seleccione el mes de pensión.')
                ->warning()
                ->send();

            return;
        }

        if ($obligatorioListo && $noObligatorioListo) {
            Notification::make()
                ->title('Seleccione solo un bloque para reversar.')
                ->body(
                    'Use los filtros de obligatorios o no obligatorios, '
                    . 'pero no ambos al mismo tiempo.'
                )
                ->warning()
                ->send();

            return;
        }

        if (! $obligatorioListo && ! $noObligatorioListo) {
            Notification::make()
                ->title('Filtros incompletos.')
                ->body(
                    'Seleccione grado y concepto antes de reversar.'
                )
                ->warning()
                ->send();

            return;
        }

        $conceptoId = $obligatorioListo
            ? $this->concepto_obligatorio_id
            : $this->concepto_no_obligatorio_id;

        $grado = $obligatorioListo
            ? $this->grado_obligatorio
            : $this->grado_no_obligatorio;

        $mesNumero =
            $obligatorioListo
            && $this->conceptoObligatorioEsPension()
            && $this->mes_pension
                ? (int) $this->mes_pension
                : null;

        $movimientos =
            MovimientoCarteraEstudiante::query()
                ->where('sede_id', $this->sede_id)
                ->where(
                    'periodo_lectivo_id',
                    $this->periodo_lectivo_id
                )
                ->when(
                    $grado !== 'todos',
                    function ($query) use ($grado) {
                        $query->where('grado', $grado);
                    }
                )
                ->where(
                    'concepto_cobro_id',
                    $conceptoId
                )
                ->where('mes_numero', $mesNumero)
                ->where(
                    'tipo_movimiento',
                    'causacion'
                )
                ->where('estado', 'activo')
                ->get();

        if ($movimientos->isEmpty()) {
            Notification::make()
                ->title(
                    'No hay causaciones activas para reversar.'
                )
                ->body(
                    'Revise los filtros seleccionados.'
                )
                ->warning()
                ->send();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Bloquear reversa cuando existen pagos confirmados
        |--------------------------------------------------------------------------
        */
        $movimientosConPagos =
            $this->obtenerMovimientosConPagosConfirmados(
                conceptoId: (int) $conceptoId,
                grado: (string) $grado,
                mesNumero: $mesNumero,
            );

        if ($movimientosConPagos->isNotEmpty()) {
            $concepto = ConceptoCobro::find($conceptoId);

            $this->detalleReversionBloqueada = [
                'concepto' =>
                    ($concepto?->codigo ?? '')
                    . ' - '
                    . ($concepto?->descripcion ?? ''),

                'grado' =>
                    $grado === 'todos'
                        ? 'Todos los grados'
                        : (string) $grado,

                'mes' =>
                    $mesNumero
                        ? (
                            $this->meses[$mesNumero]
                            ?? '-'
                        )
                        : '-',

                'estudiantes_con_pagos' =>
                    $movimientosConPagos
                        ->pluck('student_id')
                        ->unique()
                        ->count(),

                'movimientos_con_pagos' =>
                    $movimientosConPagos->count(),
            ];

            $this->mostrarModalReversionBloqueada = true;

            return;
        }

        $concepto = ConceptoCobro::find($conceptoId);

        $this->confirmacionReversion = [
            'concepto' =>
                $concepto?->codigo
                . ' - '
                . $concepto?->descripcion,

            'grado' =>
                $grado === 'todos'
                    ? 'Todos los grados'
                    : $grado,

            'mes' =>
                $mesNumero
                    ? (
                        $this->meses[$mesNumero]
                        ?? '-'
                    )
                    : '-',

            'estudiantes' =>
                $movimientos->count(),

            'total' =>
                $movimientos->sum('valor'),
        ];

        $this->motivoReversion = '';

        $this->mostrarModalReversar = true;
    }

    public function confirmarReversion(): void
    {
        $this->validate([
            'motivoReversion' => [
                'required',
            ],
        ], [
            'motivoReversion.required' =>
                'Debe indicar el motivo de la reversión.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | IDs exactos seleccionados desde el historial
        |--------------------------------------------------------------------------
        */
        $movimientoIds = collect(
            $this->movimientosSeleccionadosReversion
        )
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->toArray();

        if ($movimientoIds === []) {
            $this->mostrarModalReversar = false;

            Notification::make()
                ->title('No se identificó la causación a reversar.')
                ->body('Cierre el modal e intente nuevamente desde el historial.')
                ->warning()
                ->send();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Consultar nuevamente los movimientos activos
        |--------------------------------------------------------------------------
        |
        | Se vuelve a consultar porque el estado pudo cambiar después
        | de abrir el modal.
        */
        $movimientosActivos = MovimientoCarteraEstudiante::query()
            ->whereIn('id', $movimientoIds)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->get();

        if ($movimientosActivos->isEmpty()) {
            $this->mostrarModalReversar = false;
            $this->movimientosSeleccionadosReversion = [];
            $this->motivoReversion = '';

            $this->cargarHistorialCausaciones();

            Notification::make()
                ->title('Esta causación ya no está activa.')
                ->body('No se encontraron movimientos pendientes por reversar.')
                ->warning()
                ->send();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Segunda validación de pagos confirmados
        |--------------------------------------------------------------------------
        |
        | Un pago pudo registrarse después de abrir el modal.
        */
        $movimientosConPagos =
            $this->obtenerMovimientosConPagosConfirmadosPorIds(
                $movimientosActivos
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->toArray()
            );

        if ($movimientosConPagos->isNotEmpty()) {
            $primerMovimiento = $movimientosActivos->first();

            $concepto = ConceptoCobro::find(
                $primerMovimiento->concepto_cobro_id
            );

            $grados = $movimientosActivos
                ->pluck('grado')
                ->filter()
                ->unique()
                ->values();

            $this->detalleReversionBloqueada = [
                'concepto' =>
                    ($concepto?->codigo ?? '')
                    . ' - '
                    . ($concepto?->descripcion ?? ''),

                'grado' =>
                    $grados->count() > 1
                        ? 'Varios grados'
                        : ($grados->first() ?? '-'),

                'mes' =>
                    $primerMovimiento->mes_numero
                        ? (
                            $this->meses[
                                (int) $primerMovimiento->mes_numero
                            ] ?? '-'
                        )
                        : '-',

                'estudiantes_con_pagos' =>
                    $movimientosConPagos
                        ->pluck('student_id')
                        ->unique()
                        ->count(),

                'movimientos_con_pagos' =>
                    $movimientosConPagos->count(),
            ];

            $this->mostrarModalReversar = false;
            $this->mostrarModalReversionBloqueada = true;

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Reversar exclusivamente el lote seleccionado
        |--------------------------------------------------------------------------
        */
        $actualizados = MovimientoCarteraEstudiante::query()
            ->whereIn(
                'id',
                $movimientosActivos
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->toArray()
            )
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->update([
                'estado' => 'reversado',
                'reversado_por' => Auth::id(),
                'reversado_en' => now(),
                'motivo_reversion' =>
                    trim($this->motivoReversion),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Limpiar estado y refrescar pantalla
        |--------------------------------------------------------------------------
        */
        $this->mostrarModalReversar = false;
        $this->motivoReversion = '';
        $this->movimientosSeleccionadosReversion = [];

        $this->calcularResumenObligatorio();
        $this->calcularResumenNoObligatorio();
        $this->cargarHistorialCausaciones();

        Notification::make()
            ->title('Causación reversada correctamente.')
            ->body(
                'Movimientos reversados: '
                . $actualizados
            )
            ->success()
            ->send();
    }

    public function cargarHistorialCausaciones(): void
    {
        $this->historialCausaciones = MovimientoCarteraEstudiante::query()
            ->with(['conceptoCobro', 'causadoPor'])
            ->where('sede_id', $this->sede_id)
            ->where('periodo_lectivo_id', $this->periodo_lectivo_id)
            ->where('tipo_movimiento', 'causacion')
            ->when($this->filtroHistorialEstado, function ($query) {
                $query->where('estado', $this->filtroHistorialEstado);
            })
            ->when($this->filtroHistorialGrado, function ($query) {
                $query->where('grado', $this->filtroHistorialGrado);
            })
            ->when($this->filtroHistorialBuscar, function ($query) {
                $query->whereHas('conceptoCobro', function ($q) {
                    $q->where('descripcion', 'like', '%' . $this->filtroHistorialBuscar . '%')
                        ->orWhere('codigo', 'like', '%' . $this->filtroHistorialBuscar . '%');
                });
            })
            ->selectRaw("
                referencia,
                concepto_cobro_id,
                MIN(grado) as grado_unico,
                COUNT(DISTINCT grado) as total_grados,
                mes,
                mes_numero,
                tipo_concepto,
                estado,
                causado_por,
                MAX(causado_en) as fecha,
                COUNT(*) as estudiantes,
                SUM(valor) as total_causado
            ")
            ->groupBy(
                'referencia',
                'concepto_cobro_id',
                'mes',
                'mes_numero',
                'tipo_concepto',
                'estado',
                'causado_por'
            )
            ->orderByDesc('fecha')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'fecha' => $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y h:i a') : '-',
                'tipo' => $item->tipo_concepto === 'obligatorio' ? 'Obligatorio' : 'No obligatorio',
                'grado' => (int) $item->total_grados > 1 ? 'Todos' : $item->grado_unico,
                'concepto' => ($item->conceptoCobro?->codigo ?? '') . ' - ' . ($item->conceptoCobro?->descripcion ?? ''),
                'mes' => $item->mes ?: '-',
                'estudiantes' => $item->estudiantes,
                'total_causado' => $item->total_causado,
                'usuario' => $item->causadoPor?->name ?? 'Sistema',
                'referencia' => $item->referencia,
                'estado_raw' => $item->estado,
                'estado' => $item->estado === 'reversado'
                    ? 'Reversado'
                    : 'Causado',
            ])
            ->toArray();
    }


    public function verDetalleHistorial(string $referencia, string $estado): void
    {
        $movimientos = MovimientoCarteraEstudiante::query()
            ->with(['student', 'conceptoCobro', 'causadoPor', 'reversadoPor'])
            ->where('referencia', $referencia)
            ->where('estado', $estado)
            ->orderBy('id')
            ->get();

        if ($movimientos->isEmpty()) {
            Notification::make()
                ->title('No se encontró el detalle.')
                ->warning()
                ->send();

            return;
        }

        $primero = $movimientos->first();

        $this->detalleHistorial = [
            'general' => [
                'referencia' => $referencia,
                'estado' => $estado === 'reversado' ? 'Reversado' : 'Causado',
                'concepto' => ($primero->conceptoCobro?->codigo ?? '') . ' - ' . ($primero->conceptoCobro?->descripcion ?? ''),
                'grado' => $primero->grado,
                'mes' => $primero->mes ?: '-',
                'estudiantes' => $movimientos->count(),
                'total' => $movimientos->sum('valor'),
                'causado_por' => $primero->causadoPor?->name ?? 'Sistema',
                'causado_en' => $primero->causado_en?->format('d/m/Y h:i a') ?? '-',
                'reversado_por' => $primero->reversadoPor?->name ?? '-',
                'reversado_en' => $primero->reversado_en?->format('d/m/Y h:i a') ?? '-',
                'motivo_reversion' => $primero->motivo_reversion ?? '-',
            ],
            'movimientos' => $movimientos->map(fn ($m) => [
                'estudiante' => trim(($m->student?->primer_nombre ?? '') . ' ' . ($m->student?->segundo_nombre ?? '') . ' ' . ($m->student?->primer_apellido ?? '') . ' ' . ($m->student?->segundo_apellido ?? '')),
                'documento' => $m->student?->documento ?? '-',
                'valor_base' => $m->valor_base,
                'valor_personalizado' => $m->valor_personalizado,
                'valor' => $m->valor,
            ])->toArray(),
        ];

        $this->mostrarModalDetalleHistorial = true;
    }


    public function updatedFiltroHistorialBuscar(): void
    {
        $this->cargarHistorialCausaciones();
    }

    public function updatedFiltroHistorialEstado(): void
    {
        $this->cargarHistorialCausaciones();
    }

    public function updatedFiltroHistorialGrado(): void
    {
        $this->cargarHistorialCausaciones();
    }

    public function limpiarFiltrosHistorial(): void
    {
        $this->filtroHistorialBuscar = '';
        $this->filtroHistorialEstado = '';
        $this->filtroHistorialGrado = '';

        $this->cargarHistorialCausaciones();
    }


    public function cargarEstudiantesNoObligatorios(): void
    {
        $this->estudiantesNoObligatorios = [];
        $this->estudiantesSeleccionadosNoObligatorios = [];

        if (
            ! $this->sede_id
            || ! $this->periodo_lectivo_id
            || ! $this->grado_no_obligatorio
            || ! $this->concepto_no_obligatorio_id
        ) {
            return;
        }

        $courseId = (
            $this->curso_no_obligatorio !== 'todos'
            && is_numeric($this->curso_no_obligatorio)
        )
            ? (int) $this->curso_no_obligatorio
            : null;

        /*
        |--------------------------------------------------------------------------
        | Determinar los grados en los que el concepto está asignado
        |--------------------------------------------------------------------------
        */
        $gradosAsignados = AsignacionConcepto::query()
            ->where('sede_id', $this->sede_id)
            ->where(
                'periodo_lectivo_id',
                $this->periodo_lectivo_id
            )
            ->where(
                'concepto_cobro_id',
                $this->concepto_no_obligatorio_id
            )
            ->where('activo', true)
            ->pluck('grado')
            ->filter()
            ->unique()
            ->values();

        $asignadoATodos = $gradosAsignados->contains('todos');

        $query = Student::query()
            ->with('course')
            ->where('sede_id', $this->sede_id)
            ->where(
                'periodo_lectivo_id',
                $this->periodo_lectivo_id
            );

        /*
        |--------------------------------------------------------------------------
        | Filtrar por grado
        |--------------------------------------------------------------------------
        */
        if ($this->grado_no_obligatorio === 'todos') {
            /*
            * Si el concepto tiene una asignación general, puede aplicarse
            * a todos los grados reales.
            *
            * Si no tiene asignación general, únicamente se muestran estudiantes
            * pertenecientes a los grados donde sí fue asignado.
            */
            if (! $asignadoATodos) {
                $gradosReales = $gradosAsignados
                    ->reject(fn ($grado) => $grado === 'todos')
                    ->values()
                    ->toArray();

                if ($gradosReales === []) {
                    return;
                }

                $query->whereHas(
                    'course',
                    fn ($courseQuery) =>
                        $courseQuery->whereIn(
                            'grado',
                            $gradosReales
                        )
                );
            }
        } else {
            $gradoSeleccionado =
                $this->grado_no_obligatorio;

            /*
            * El concepto debe estar asignado específicamente al grado
            * o mediante la opción general "todos".
            */
            $conceptoDisponible =
                $asignadoATodos
                || $gradosAsignados->contains(
                    $gradoSeleccionado
                );

            if (! $conceptoDisponible) {
                return;
            }

            $query->whereHas(
                'course',
                fn ($courseQuery) =>
                    $courseQuery->where(
                        'grado',
                        $gradoSeleccionado
                    )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filtrar por curso
        |--------------------------------------------------------------------------
        */
        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        $estudiantes = $query
            ->orderBy('primer_apellido')
            ->orderBy('segundo_apellido')
            ->orderBy('primer_nombre')
            ->orderBy('segundo_nombre')
            ->get();

        $this->estudiantesNoObligatorios = $estudiantes
            ->map(function (Student $student) {
                $nombre = trim(
                    implode(' ', array_filter([
                        $student->primer_nombre,
                        $student->segundo_nombre,
                        $student->primer_apellido,
                        $student->segundo_apellido,
                    ]))
                );

                return [
                    'id' => (int) $student->id,
                    'nombre' => $nombre,
                    'documento' =>
                        $student->documento ?: '-',
                    'codigo' =>
                        $student->codigo ?: '-',
                    'grado' =>
                        $student->course?->grado ?: '-',
                    'curso' =>
                        $student->course?->curso
                        ?? $student->course?->descripcion
                        ?? '-',
                ];
            })
            ->values()
            ->toArray();

        /*
        * Todos quedan seleccionados inicialmente.
        */
        $this->estudiantesSeleccionadosNoObligatorios =
            collect($this->estudiantesNoObligatorios)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->toArray();
    }

    public function getEstudiantesNoObligatoriosVisiblesProperty(): array
    {
        $termino = mb_strtolower(
            trim($this->buscarEstudianteNoObligatorio)
        );

        if ($termino === '') {
            return $this->estudiantesNoObligatorios;
        }

        return collect($this->estudiantesNoObligatorios)
            ->filter(function (array $student) use ($termino) {
                $texto = mb_strtolower(
                    implode(' ', [
                        $student['nombre'] ?? '',
                        $student['documento'] ?? '',
                        $student['codigo'] ?? '',
                        $student['grado'] ?? '',
                        $student['curso'] ?? '',
                    ])
                );

                return str_contains($texto, $termino);
            })
            ->values()
            ->toArray();
    }

    private function convertirMonedaAFloat(
        string|int|float|null $valor
    ): float {
        if ($valor === null || $valor === '') {
            return 0;
        }

        if (is_int($valor) || is_float($valor)) {
            return round((float) $valor, 2);
        }

        $valor = trim((string) $valor);

        /*
        |--------------------------------------------------------------------------
        | Valor proveniente de MySQL
        |--------------------------------------------------------------------------
        |
        | Ejemplos:
        | 40000.00
        | 125000.50
        */
        if (preg_match('/^\d+\.\d{1,2}$/', $valor)) {
            return round((float) $valor, 2);
        }

        /*
        |--------------------------------------------------------------------------
        | Valor escrito o formateado visualmente
        |--------------------------------------------------------------------------
        |
        | Ejemplos:
        | 40.000
        | $ 40.000
        | 40000
        */
        $limpio = preg_replace(
            '/[^\d]/',
            '',
            $valor
        );

        return round(
            (float) ($limpio ?: 0),
            2
        );
    }

    private function formatearMonedaVisual(
        string|int|float|null $valor
    ): string {
        $numero = $this->convertirMonedaAFloat($valor);

        if ($numero <= 0) {
            return '';
        }

        return number_format(
            $numero,
            0,
            ',',
            '.'
        );
    }

    public function updatedValorNoObligatorio(
        $valor
    ): void {
        $this->valor_no_obligatorio =
            $this->formatearMonedaVisual($valor);

        $this->calcularResumenNoObligatorio();
    }

    public function updatedEstudiantesSeleccionadosNoObligatorios(): void
    {
        $this->calcularResumenNoObligatorio();
    }

    public function prepararReversionDesdeHistorial(
        string $referencia
    ): void {
        $movimientos = MovimientoCarteraEstudiante::query()
            ->where('referencia', $referencia)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->get();

        if ($movimientos->isEmpty()) {
            Notification::make()
                ->title('Esta causación ya no está activa.')
                ->body(
                    'Actualice el historial y revise nuevamente.'
                )
                ->warning()
                ->send();

            return;
        }

        $movimientosConPagos =
            $this->obtenerMovimientosConPagosConfirmadosPorIds(
                $movimientos->pluck('id')->all()
            );

        if ($movimientosConPagos->isNotEmpty()) {
            $concepto = ConceptoCobro::find(
                $movimientos->first()->concepto_cobro_id
            );

            $this->detalleReversionBloqueada = [
                'concepto' =>
                    ($concepto?->codigo ?? '')
                    . ' - '
                    . ($concepto?->descripcion ?? ''),

                'grado' =>
                    $movimientos
                        ->pluck('grado')
                        ->unique()
                        ->count() > 1
                            ? 'Varios grados'
                            : (
                                $movimientos->first()->grado === 'todos'
                                    ? 'Todos los grados'
                                    : $movimientos->first()->grado
                            ),

                'mes' =>
                    $movimientos->first()->mes_numero
                        ? (
                            $this->meses[
                                $movimientos->first()->mes_numero
                            ] ?? '-'
                        )
                        : '-',

                'estudiantes_con_pagos' =>
                    $movimientosConPagos
                        ->pluck('student_id')
                        ->unique()
                        ->count(),

                'movimientos_con_pagos' =>
                    $movimientosConPagos->count(),
            ];

            $this->mostrarModalReversionBloqueada = true;

            return;
        }

        $concepto = ConceptoCobro::find(
            $movimientos->first()->concepto_cobro_id
        );

        $grados = $movimientos
            ->pluck('grado')
            ->filter()
            ->unique()
            ->values();

        $this->movimientosSeleccionadosReversion =
            $movimientos
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->toArray();

        $this->confirmacionReversion = [
            'concepto' =>
                ($concepto?->codigo ?? '')
                . ' - '
                . ($concepto?->descripcion ?? ''),

            'grado' =>
                $grados->count() > 1
                    ? 'Varios grados'
                    : (
                        $grados->first() === 'todos'
                            ? 'Todos los grados'
                            : ($grados->first() ?? '-')
                    ),

            'mes' =>
                $movimientos->first()->mes_numero
                    ? (
                        $this->meses[
                            $movimientos->first()->mes_numero
                        ] ?? '-'
                    )
                    : '-',

            'estudiantes' =>
                $movimientos
                    ->pluck('student_id')
                    ->unique()
                    ->count(),

            'total' =>
                $movimientos->sum('valor'),
        ];

        $this->motivoReversion = '';
        $this->mostrarModalReversar = true;
    }

    private function obtenerMovimientosConPagosConfirmadosPorIds(
        array $movimientoIds
    ) {
        if ($movimientoIds === []) {
            return collect();
        }

        return MovimientoCarteraEstudiante::query()
            ->whereIn('id', $movimientoIds)
            ->whereHas(
                'aplicacionesPago',
                function ($query) {
                    $query->whereHas(
                        'reciboPago',
                        fn ($reciboQuery) =>
                            $reciboQuery->where(
                                'estado',
                                'confirmado'
                            )
                    );
                }
            )
            ->get();
    }

    private function limpiarFormularioCausacion(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Bloque obligatorio
        |--------------------------------------------------------------------------
        */
        $this->grado_obligatorio = null;
        $this->concepto_obligatorio_id = null;
        $this->mes_pension = null;

        /*
        |--------------------------------------------------------------------------
        | Bloque no obligatorio
        |--------------------------------------------------------------------------
        */
        $this->grado_no_obligatorio = null;
        $this->curso_no_obligatorio = 'todos';
        $this->concepto_no_obligatorio_id = null;
        $this->valor_no_obligatorio = '';

        $this->estudiantesSeleccionadosNoObligatorios = [];
        $this->buscarEstudianteNoObligatorio = '';

        /*
        |--------------------------------------------------------------------------
        | Datos temporales de la causación
        |--------------------------------------------------------------------------
        */
        $this->tipoCausacion = null;

        $this->confirmacionCausacion = [
            'concepto' => '',
            'grado' => '',
            'mes' => '',
            'estudiantes' => 0,
            'valor_unitario' => 0,
            'total' => 0,
        ];
    }
    
}