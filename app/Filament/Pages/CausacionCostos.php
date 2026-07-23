<?php

namespace App\Filament\Pages;

use App\Models\AsignacionConcepto;
use App\Models\ConceptoCobro;
use App\Models\PeriodoLectivo;
use App\Models\Sede;
use App\Models\MovimientoCarteraEstudiante;
use App\Models\ReciboPago;


use App\Services\Financiero\CausacionCostosService;

use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Notifications\Notification;
use App\Services\Financiero\CausacionMasivaService;

use Illuminate\Support\Facades\Auth;

class CausacionCostos extends Page
{
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
            ->orderBy('grado')
            ->pluck('grado', 'grado')
            ->unique();
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
        return ConceptoCobro::query()
            ->where('sede_id', $this->sede_id)
            ->where('periodo_lectivo_id', $this->periodo_lectivo_id)
            ->where('obligatorio', false)
            ->where('activo', true)
            ->orderBy('codigo')
            ->get()
            ->mapWithKeys(fn ($c) => [
                $c->id => $c->codigo . ' - ' . $c->descripcion,
            ]);
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
        $this->calcularResumenNoObligatorio();
    }

    public function updatedConceptoNoObligatorioId(): void
    {
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
        $this->resumenNoObligatorio = app(CausacionCostosService::class)->calcularResumen(
            sedeId: $this->sede_id,
            periodoLectivoId: $this->periodo_lectivo_id,
            grado: $this->grado_no_obligatorio,
            conceptoCobroId: $this->concepto_no_obligatorio_id,
            mesNumero: null,
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
        $this->concepto_no_obligatorio_id = null;

        $this->resetResumenes();
    }

    public function updatedPeriodoLectivoId(): void
    {
        $this->grado_obligatorio = null;
        $this->concepto_obligatorio_id = null;
        $this->mes_pension = null;
        $this->grado_no_obligatorio = null;
        $this->concepto_no_obligatorio_id = null;

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

    public function validarOrdenPension(int $grado, int $conceptoId, int $mesNumero): bool
    {
        if ($mesNumero <= 2) {
            return true;
        }

        $mesesAnteriores = range(2, $mesNumero - 1);

        $mesesCausados = MovimientoCarteraEstudiante::query()
            ->where('sede_id', $this->sede_id)
            ->where('periodo_lectivo_id', $this->periodo_lectivo_id)
            ->where('grado', $grado)
            ->where('concepto_cobro_id', $conceptoId)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->whereIn('mes_numero', $mesesAnteriores)
            ->distinct()
            ->pluck('mes_numero')
            ->map(fn ($mes) => (int) $mes)
            ->toArray();

        return count(array_diff($mesesAnteriores, $mesesCausados)) === 0;
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
        $obligatorioListo = $this->grado_obligatorio && $this->concepto_obligatorio_id;
        $noObligatorioListo = $this->grado_no_obligatorio && $this->concepto_no_obligatorio_id;

        if ($obligatorioListo && $this->conceptoObligatorioEsPension() && ! $this->mes_pension) {
            Notification::make()
                ->title('Seleccione el mes de pensión.')
                ->warning()
                ->send();

            return;
        }

        if ($obligatorioListo && $this->conceptoObligatorioEsPension() && $this->mes_pension) {
            $ordenValido = $this->validarOrdenPension(
                (int) $this->grado_obligatorio,
                (int) $this->concepto_obligatorio_id,
                (int) $this->mes_pension,
            );

            if (! $ordenValido) {
                Notification::make()
                    ->title('No puede saltar meses de pensión.')
                    ->body('Debe causar primero los meses anteriores antes de continuar.')
                    ->warning()
                    ->send();

                return;
            }
        }

        if ($obligatorioListo && $noObligatorioListo) {
            Notification::make()
                ->title('Seleccione solo un bloque para causar.')
                ->body('Use los filtros de obligatorios o no obligatorios, pero no ambos al mismo tiempo.')
                ->warning()
                ->send();

            return;
        }

        if (! $obligatorioListo && ! $noObligatorioListo) {
            Notification::make()
                ->title('Filtros incompletos.')
                ->body('Seleccione grado y concepto antes de causar.')
                ->warning()
                ->send();

            return;
        }
        $conceptoIdValidar = $obligatorioListo
            ? $this->concepto_obligatorio_id
            : $this->concepto_no_obligatorio_id;

        $gradoValidar = $obligatorioListo
            ? $this->grado_obligatorio
            : $this->grado_no_obligatorio;

        $mesNumeroValidar = $obligatorioListo && $this->conceptoObligatorioEsPension() && $this->mes_pension
            ? (int) $this->mes_pension
            : null;

        $yaExisteCausacionActiva = MovimientoCarteraEstudiante::query()
            ->where('sede_id', $this->sede_id)
            ->where('periodo_lectivo_id', $this->periodo_lectivo_id)
            ->where('grado', $gradoValidar)
            ->where('concepto_cobro_id', $conceptoIdValidar)
            ->where('mes_numero', $mesNumeroValidar)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->exists();

        if ($yaExisteCausacionActiva) {
            Notification::make()
                ->title('Esta causación ya existe.')
                ->body('Para volver a causarla, primero debe reversar la causación activa.')
                ->warning()
                ->send();

            return;
        }

        $this->tipoCausacion = $obligatorioListo ? 'obligatorio' : 'no_obligatorio';

        $conceptoId = $obligatorioListo
            ? $this->concepto_obligatorio_id
            : $this->concepto_no_obligatorio_id;

        $concepto = ConceptoCobro::find($conceptoId);

        $resumen = $obligatorioListo
            ? $this->resumenObligatorio
            : $this->resumenNoObligatorio;

        $this->confirmacionCausacion = [
            'concepto' => $concepto?->codigo . ' - ' . $concepto?->descripcion,
            'grado' => $obligatorioListo ? $this->grado_obligatorio : $this->grado_no_obligatorio,
            'mes' => $obligatorioListo && $this->mes_pension ? ($this->meses[(int) $this->mes_pension] ?? '') : '-',
            'estudiantes' => $resumen['estudiantes'],
            'total' => $resumen['total_causar'],
        ];

        $this->mostrarModalCausar = true;
    }

    public function confirmarCausacion(): void
    {
        $esObligatorio = $this->tipoCausacion === 'obligatorio';

        $resultado = app(CausacionMasivaService::class)->causar(
            sedeId: $this->sede_id,
            periodoLectivoId: $this->periodo_lectivo_id,
            grado: $esObligatorio ? $this->grado_obligatorio : $this->grado_no_obligatorio,
            conceptoCobroId: $esObligatorio ? $this->concepto_obligatorio_id : $this->concepto_no_obligatorio_id,
            mesNumero: $esObligatorio && $this->conceptoObligatorioEsPension() && $this->mes_pension
                ? (int) $this->mes_pension
                : null,
            userId: Auth::id(),
        );

        $this->mostrarModalCausar = false;

        $this->calcularResumenObligatorio();
        $this->calcularResumenNoObligatorio();
        $this->cargarHistorialCausaciones();

        Notification::make()
            ->title('Causación realizada correctamente.')
            ->body(
                'Creados: ' . $resultado['creados'] .
                ' | Omitidos: ' . $resultado['omitidos'] .
                ' | Total: $' . number_format($resultado['total_causado'], 0, ',', '.')
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
        $obligatorioListo = $this->grado_obligatorio && $this->concepto_obligatorio_id;
        $noObligatorioListo = $this->grado_no_obligatorio && $this->concepto_no_obligatorio_id;

        if ($obligatorioListo && $this->conceptoObligatorioEsPension() && ! $this->mes_pension) {
            Notification::make()
                ->title('Seleccione el mes de pensión.')
                ->warning()
                ->send();

            return;
        }

        if ($obligatorioListo && $noObligatorioListo) {
            Notification::make()
                ->title('Seleccione solo un bloque para reversar.')
                ->body('Use los filtros de obligatorios o no obligatorios, pero no ambos al mismo tiempo.')
                ->warning()
                ->send();

            return;
        }

        if (! $obligatorioListo && ! $noObligatorioListo) {
            Notification::make()
                ->title('Filtros incompletos.')
                ->body('Seleccione grado y concepto antes de reversar.')
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

        $mesNumero = $obligatorioListo && $this->conceptoObligatorioEsPension() && $this->mes_pension
            ? (int) $this->mes_pension
            : null;

        $movimientos = MovimientoCarteraEstudiante::query()
            ->where('sede_id', $this->sede_id)
            ->where('periodo_lectivo_id', $this->periodo_lectivo_id)
            ->when($grado !== 'todos', function ($query) use ($grado) {
                $query->where('grado', $grado);
            })
            ->where('concepto_cobro_id', $conceptoId)
            ->where('mes_numero', $mesNumero)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->get();

        if ($movimientos->isEmpty()) {
            Notification::make()
                ->title('No hay causaciones activas para reversar.')
                ->body('Revise los filtros seleccionados.')
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
                        ? ($this->meses[$mesNumero] ?? '-')
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
            'concepto' => $concepto?->codigo . ' - ' . $concepto?->descripcion,
            'grado' => $grado === 'todos' ? 'Todos los grados' : $grado,
            'mes' => $mesNumero ? ($this->meses[$mesNumero] ?? '-') : '-',
            'estudiantes' => $movimientos->count(),
            'total' => $movimientos->sum('valor'),
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
            'motivoReversion.required' => 'Debe indicar el motivo de la reversión.',
            
        ]);
        $obligatorioListo = $this->grado_obligatorio && $this->concepto_obligatorio_id;

        $conceptoId = $obligatorioListo
            ? $this->concepto_obligatorio_id
            : $this->concepto_no_obligatorio_id;

        $grado = $obligatorioListo
            ? $this->grado_obligatorio
            : $this->grado_no_obligatorio;

        $mesNumero = $obligatorioListo && $this->conceptoObligatorioEsPension() && $this->mes_pension
            ? (int) $this->mes_pension
            : null;

        /*
        |--------------------------------------------------------------------------
        | Segunda validación de seguridad
        |--------------------------------------------------------------------------
        |
        | Se repite dentro de la confirmación porque un pago pudo registrarse
        | después de abrir el modal.
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
                        ? ($this->meses[$mesNumero] ?? '-')
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

        $mesesPosterioresTexto = null;

        if ($mesNumero) {
            $mesesPosteriores = MovimientoCarteraEstudiante::query()
                ->where('sede_id', $this->sede_id)
                ->where('periodo_lectivo_id', $this->periodo_lectivo_id)
                ->when($grado !== 'todos', function ($query) use ($grado) {
                    $query->where('grado', $grado);
                })
                ->where('concepto_cobro_id', $conceptoId)
                ->where('tipo_movimiento', 'causacion')
                ->where('estado', 'activo')
                ->where('mes_numero', '>', $mesNumero)
                ->distinct()
                ->pluck('mes_numero')
                ->map(fn ($mes) => (int) $mes)
                ->sort()
                ->values()
                ->toArray();

            if (count($mesesPosteriores) > 0) {
                $nombres = collect($mesesPosteriores)
                    ->map(fn ($mes) => $this->meses[$mes] ?? $mes)
                    ->implode(', ');

                $mesReversado = $this->meses[$mesNumero] ?? 'este mes';

                $mesesPosterioresTexto = "Existen meses posteriores ya causados: {$nombres}. Se recomienda volver a causar {$mesReversado} antes de continuar.";
            }
        }

        $actualizados = MovimientoCarteraEstudiante::query()
            ->where('sede_id', $this->sede_id)
            ->where('periodo_lectivo_id', $this->periodo_lectivo_id)
            ->when($grado !== 'todos', function ($query) use ($grado) {
                $query->where('grado', $grado);
            })
            ->where('concepto_cobro_id', $conceptoId)
            ->where('mes_numero', $mesNumero)
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->update([
                'estado' => 'reversado',
                'reversado_por' => Auth::id(),
                'reversado_en' => now(),
                'motivo_reversion' => trim($this->motivoReversion),
            ]);

        $this->mostrarModalReversar = false;
        $this->motivoReversion = '';

        $this->calcularResumenObligatorio();
        $this->calcularResumenNoObligatorio();
        $this->cargarHistorialCausaciones();

        Notification::make()
            ->title('Causación reversada correctamente.')
            ->body(
                $mesesPosterioresTexto
                    ? 'Movimientos reversados: ' . $actualizados . '. ' . $mesesPosterioresTexto
                    : 'Movimientos reversados: ' . $actualizados
            )
            ->{$mesesPosterioresTexto ? 'warning' : 'success'}()
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
    
}