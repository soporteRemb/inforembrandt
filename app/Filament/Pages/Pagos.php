<?php

namespace App\Filament\Pages;

use App\Models\PeriodoLectivo;

use App\Services\Financiero\Pagos\PagosService;
use App\Services\Financiero\Pagos\RegistrarPagoService;
use App\Services\Financiero\Pagos\HistorialPagosService;
use App\Services\Financiero\Pagos\DetalleReciboService;
use App\Services\Financiero\Pagos\ImpresionReciboService;
use App\Services\Financiero\Pagos\AnularOperacionPagoService;
use App\Services\Financiero\Pagos\AcuerdosPagoService;
use App\Services\Financiero\Pagos\CrearAcuerdoPagoService;
use App\Services\Financiero\Pagos\ActualizarEstadoAcuerdoPagoService;
use App\Services\Financiero\Pagos\ActualizarAcuerdoPagoService;

use App\Services\Financiero\Pagos\Pdf\ReciboPdfService;

use Filament\Pages\Page;

use Filament\Notifications\Notification;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

use Throwable;


class Pagos extends Page
{
    use WithFileUploads;

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

    public array $obligaciones = [];


    public string $tipoObligacionActiva = 'obligatorio';

    public array $formasPago = [];

    public ?int $movimientoSeleccionadoId = null;

    public string $mesPeriodoPago = '';

    public string $valorPago = '';

    public string $descuentoPago = '0';

    public ?int $formaPagoId = null;

    public string $referenciaPago = '';

    public ?string $fechaConsignacion = null;

    public string $recibiDe = '';

    public string $detallePago = '';

    public array $colaPagos = [];

    public int $secuenciaCola = 0;

    public ?int $ultimoNumeroRecibo = null;

    public ?int $ultimaOperacionPagoId = null;

    public array $historialPagos = [];

    public array $resumenHistorial = [
        'pagos' => 0,
        'descuentos' => 0,
        'total_pagado' => 0,
        'cantidad_filas' => 0,
    ];

    public string $filtroHistorialRecibo = '';

    public ?string $filtroHistorialDesde = null;

    public ?string $filtroHistorialHasta = null;

    public string $filtroHistorialEstado = '';
    public string $filtroHistorialConcepto = '';

    public bool $mostrarDetalleRecibo = false;

    public ?int $operacionDetalleReciboId = null;

    public array $detalleRecibo = [];

    public bool $mostrarModalReimpresion = false;

    public string $motivoReimpresion = '';

    public bool $mostrarModalAnulacion = false;

    public ?int $operacionAnularId = null;

    public string $motivoAnulacion = '';

    public array $acuerdosPago = [];


    public bool $mostrarModalAcuerdoPago = false;

    public string $acuerdoPersona = '';

    public string $acuerdoParentesco = '';

    public ?string $acuerdoFechaCompromiso = null;

    

    public string $acuerdoTexto = '';

    public array $acuerdoEvidencias = [];

    public array $acuerdoEvidenciasNuevas = [];

    public ?string $mensajeEvidencias = null;

    public string $modoModalAcuerdoPago = 'crear';

    public ?int $acuerdoPagoSeleccionadoId = null;

    public array $acuerdoEvidenciasGuardadas = [];

    public string $acuerdoEstado = 'vigente';

    public ?string $acuerdoEstadoModificadoPor = null;

    public ?string $acuerdoEstadoModificadoEn = null;

    public string $filtroAcuerdoTexto = '';

    public string $filtroAcuerdoEstado = '';

    public ?string $filtroAcuerdoDesde = null;

    public ?string $filtroAcuerdoHasta = null;

    public array $acuerdosPagoOriginales = [];
    

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

        $this->formasPago = app(PagosService::class)
            ->obtenerFormasPagoActivas()
            ->map(fn ($forma) => [
                'id' => $forma->id,
                'nombre' => $forma->nombre,
                'requiere_referencia' => (bool) $forma->requiere_referencia,
                'requiere_fecha_consignacion' => (bool) $forma->requiere_fecha_consignacion,
            ])
            ->values()
            ->toArray();

        $this->formaPagoId = $this->formasPago[0]['id'] ?? null;
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

        $this->colaPagos = [];

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

        $this->limpiarFormularioPago();

        $this->resumenFinanciero = app(PagosService::class)
            ->cartera()
            ->obtenerResumen(
                studentId: $student->id,
                sedeId: $this->sede_id,
                periodoLectivoId: $this->periodo_lectivo_id,
            );

        $obligaciones = app(PagosService::class)
            ->cartera()
            ->obtenerObligaciones(
                studentId: $student->id,
                sedeId: $this->sede_id,
                periodoLectivoId: $this->periodo_lectivo_id,
            );

        $this->obligaciones = $obligaciones
            ->map(function (array $obligacion) {
                return [
                    ...$obligacion,

                    'fecha_vencimiento_formateada' => $obligacion['fecha_vencimiento']
                        ? \Carbon\Carbon::parse($obligacion['fecha_vencimiento'])->format('d/m/Y')
                        : 'Sin definir',

                    'mes_mostrado' => $obligacion['mes'] ?? '',
                ];
            })
            ->values()
            ->toArray();

        $this->tipoObligacionActiva = 'obligatorio';
        $this->buscarEstudiante = $nombreCompleto;

        $this->resultadosBusqueda = [];

        $this->cargarHistorialPagos(
            app(HistorialPagosService::class)
        );

        $this->cargarAcuerdosPago(
            app(AcuerdosPagoService::class)
        );
    }



    public function updatedMovimientoSeleccionadoId($movimientoId): void
    {
        if (! $movimientoId) {
            $this->limpiarFormularioPago();

            return;
        }

        $obligacion = collect($this->obligaciones)
            ->firstWhere('id', (int) $movimientoId);

        if (! $obligacion) {
            $this->limpiarFormularioPago();

            return;
        }

        $this->movimientoSeleccionadoId = (int) $movimientoId;

        $this->mesPeriodoPago = $obligacion['mes'] ?? '';

        $this->valorPago = $this->formatearMonedaVisual(
            $obligacion['saldo_pendiente'] ?? 0
        );

        $this->descuentoPago = '0';
    }

    public function getObligacionSeleccionadaProperty(): ?array
    {
        if (! $this->movimientoSeleccionadoId) {
            return null;
        }

        return collect($this->obligaciones)
            ->firstWhere('id', $this->movimientoSeleccionadoId);
    }

    public function getFormaPagoSeleccionadaProperty(): ?array
    {
        if (! $this->formaPagoId) {
            return null;
        }

        return collect($this->formasPago)
            ->firstWhere('id', $this->formaPagoId);
    }

    public function getTotalTransaccionProperty(): float
    {
        $valor = $this->convertirMonedaAFloat($this->valorPago);
        $descuento = $this->convertirMonedaAFloat($this->descuentoPago);

        return max(0, round($valor - $descuento, 2));
    }

    private function convertirMonedaAFloat(string|int|float|null $valor): float
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        $limpio = preg_replace('/[^\d]/', '', (string) $valor);

        return (float) ($limpio ?: 0);
    }

    private function limpiarFormularioPago(): void
    {
        $this->movimientoSeleccionadoId = null;
        $this->mesPeriodoPago = '';
        $this->valorPago = '';
        $this->descuentoPago = '0';
        $this->referenciaPago = '';
        $this->fechaConsignacion = null;
        $this->detallePago = '';
    }

    public function cambiarTipoObligacion(string $tipo): void
    {
        if (! in_array($tipo, ['obligatorio', 'no_obligatorio'], true)) {
            return;
        }

        $this->tipoObligacionActiva = $tipo;
    }

    public function getObligacionesObligatoriasProperty(): array
    {
        return collect($this->obligaciones)
            ->where('obligatorio', true)
            ->values()
            ->toArray();
    }

    public function getObligacionesNoObligatoriasProperty(): array
    {
        return collect($this->obligaciones)
            ->where('obligatorio', false)
            ->values()
            ->toArray();
    }

    public function getObligacionesVisiblesProperty(): array
    {
        return $this->tipoObligacionActiva === 'obligatorio'
            ? $this->obligacionesObligatorias
            : $this->obligacionesNoObligatorias;
    }

    public function getTotalObligacionesVisiblesProperty(): float
    {
        return (float) collect($this->obligacionesVisibles)
            ->sum('saldo_pendiente');
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
        $this->obligaciones = [];
        $this->tipoObligacionActiva = 'obligatorio';

        $this->colaPagos = [];
        $this->secuenciaCola = 0;

        $this->ultimoNumeroRecibo = null;
        $this->ultimaOperacionPagoId = null;
        $this->filtroHistorialConcepto = '';

        $this->limpiarFormularioPago();

        $this->resumenFinanciero = [
            'deuda_obligatoria' => 0,
            'penalizaciones' => 0,
            'otros_conceptos' => 0,
            'total_pendiente' => 0,
            'saldo_favor' => 0,
            'total_neto' => 0,
            'cantidad_obligaciones' => 0,
        ];

        $this->historialPagos = [];

        $this->resumenHistorial = [
            'pagos' => 0,
            'descuentos' => 0,
            'total_pagado' => 0,
            'cantidad_filas' => 0,
        ];

        $this->filtroHistorialRecibo = '';
        $this->filtroHistorialDesde = null;
        $this->filtroHistorialHasta = null;
        $this->filtroHistorialEstado = '';
        $this->acuerdosPago = [];
        $this->acuerdosPagoOriginales = [];

        $this->filtroAcuerdoTexto = '';
        $this->filtroAcuerdoEstado = '';
        $this->filtroAcuerdoDesde = null;
        $this->filtroAcuerdoHasta = null;
    }

    public function getSubtotalColaProperty(): float
    {
        return (float) collect($this->colaPagos)
            ->sum('valor_recibido');
    }

    public function getDescuentosColaProperty(): float
    {
        return (float) collect($this->colaPagos)
            ->sum('descuento');
    }

    public function getTotalAplicadoColaProperty(): float
    {
        return (float) collect($this->colaPagos)
            ->sum('valor_aplicado');
    }

    public function getSaldoFavorGeneradoColaProperty(): float
    {
        return (float) collect($this->colaPagos)
            ->sum('saldo_favor_generado');
    }

    public function getPuedeConfirmarColaProperty(): bool
    {
        return $this->student_id !== null
            && count($this->colaPagos) > 0
            && $this->subtotalCola > 0;
    }

    public function getHeading(): string
    {
        return '';
    }

    public function getValorPagoNumericoProperty(): float
    {
        return $this->convertirMonedaAFloat($this->valorPago);
    }

    public function getDescuentoPagoNumericoProperty(): float
    {
        return $this->convertirMonedaAFloat($this->descuentoPago);
    }

    public function updatedValorPago($valor): void
    {
        $this->valorPago = $this->formatearMonedaVisual($valor);
    }

    public function updatedDescuentoPago($valor): void
    {
        $this->descuentoPago = $this->formatearMonedaVisual($valor);
    }

    private function formatearMonedaVisual(string|int|float|null $valor): string
    {
        $numero = $this->convertirMonedaAFloat($valor);

        return number_format($numero, 0, ',', '.');
    }

    public function getPuedeAdicionarPagoProperty(): bool
    {
        if (! $this->student_id) {
            return false;
        }

        if (! $this->movimientoSeleccionadoId) {
            return false;
        }

        if (! $this->formaPagoId) {
            return false;
        }

        if (trim($this->recibiDe) === '') {
            return false;
        }

        if ($this->valorPagoNumerico <= 0) {
            return false;
        }

        if ($this->descuentoPagoNumerico < 0) {
            return false;
        }

        if ($this->totalTransaccion <= 0) {
            return false;
        }

        $formaPago = $this->formaPagoSeleccionada;

        if (! $formaPago) {
            return false;
        }

        /*
        Por decisión funcional de la primera versión, la referencia de
        consignación y la fecha NO bloquean el registro del pago.
    
        
        if (
            ($formaPago['requiere_referencia'] ?? false)
            && trim($this->referenciaPago) === ''
        ) {
            return false;
        }

        if (
            ($formaPago['requiere_fecha_consignacion'] ?? false)
            && empty($this->fechaConsignacion)
        ) {
            return false;
        }*/

        return true;
    }

    public function adicionarPagoCola(): void
    {
        if (! $this->puedeAdicionarPago) {
            Notification::make()
                ->title('Información incompleta.')
                ->body('Complete los datos requeridos antes de adicionar el pago.')
                ->warning()
                ->send();

            return;
        }

        $obligacion = $this->obligacionSeleccionada;

        if (! $obligacion) {
            Notification::make()
                ->title('Seleccione una obligación válida.')
                ->warning()
                ->send();

            return;
        }

        $saldoOriginal = (float) ($obligacion['saldo_pendiente'] ?? 0);

        /*
        * Descontamos lo que ya se haya aplicado a esta misma obligación
        * dentro de la cola actual.
        */
        $cubiertoEnCola = (float) collect($this->colaPagos)
            ->where('movimiento_id', (int) $obligacion['id'])
            ->sum('valor_aplicado');

        $saldoDisponible = max(
            0,
            round($saldoOriginal - $cubiertoEnCola, 2)
        );

        if ($saldoDisponible <= 0) {
            Notification::make()
                ->title('La obligación ya está cubierta en la cola.')
                ->body('Elimine o edite el pago existente antes de adicionar otro.')
                ->warning()
                ->send();

            return;
        }

        $valorRecibido = $this->totalTransaccion;
        $descuento = $this->descuentoPagoNumerico;

        if ($descuento > $saldoDisponible) {
            Notification::make()
                ->title('Descuento no válido.')
                ->body('El descuento no puede superar el saldo pendiente de la obligación.')
                ->warning()
                ->send();

            return;
        }

        /*
        * El descuento también reduce la deuda, aunque no es dinero recibido.
        */
        $valorCubierto = $valorRecibido + $descuento;

        $valorAplicado = min(
            $saldoDisponible,
            $valorCubierto
        );

        /*
        * El dinero que exceda lo necesario queda como saldo a favor general.
        */
        $dineroNecesario = max(
            0,
            $saldoDisponible - $descuento
        );

        $saldoFavorGenerado = max(
            0,
            round($valorRecibido - $dineroNecesario, 2)
        );

        $formaPago = $this->formaPagoSeleccionada;

        $this->secuenciaCola++;

        $this->colaPagos[] = [
            'fila_id' => $this->secuenciaCola,
            'movimiento_id' => (int) $obligacion['id'],
            'concepto_cobro_id' => $obligacion['concepto_cobro_id'] ?? null,

            'concepto' => $obligacion['concepto'],
            'mes' => $obligacion['mes'] ?? '',
            'mes_numero' => $obligacion['mes_numero'] ?? null,

            'saldo_anterior' => $saldoDisponible,
            'descuento' => $descuento,
            'valor_recibido' => $valorRecibido,
            'valor_aplicado' => $valorAplicado,
            'saldo_posterior' => max(
                0,
                round($saldoDisponible - $valorAplicado, 2)
            ),
            'saldo_favor_generado' => $saldoFavorGenerado,

            'forma_pago_id' => $this->formaPagoId,
            'forma_pago' => $formaPago['nombre'] ?? 'Sin definir',
            'numero_referencia' => trim($this->referenciaPago),
            'fecha_consignacion' => $this->fechaConsignacion,

            'recibido_de' => trim($this->recibiDe),
            'detalle' => trim($this->detallePago),
        ];

        $this->limpiarTransaccionDespuesDeAdicionar();

        Notification::make()
            ->title('Pago adicionado a la cola.')
            ->success()
            ->send();
    }

    private function limpiarTransaccionDespuesDeAdicionar(): void
    {
        $this->movimientoSeleccionadoId = null;
        $this->mesPeriodoPago = '';
        $this->valorPago = '';
        $this->descuentoPago = '0';
        $this->referenciaPago = '';
        $this->fechaConsignacion = null;
        $this->detallePago = '';
    }

    public function eliminarPagoCola(int $filaId): void
    {
        $this->colaPagos = collect($this->colaPagos)
            ->reject(fn (array $fila) => $fila['fila_id'] === $filaId)
            ->values()
            ->toArray();
    }

    public function limpiarColaPagos(): void
    {
        $this->colaPagos = [];
    }

    public function confirmarPagos(
        RegistrarPagoService $registrarPagoService
    ): void {
        if (! $this->puedeConfirmarCola) {
            Notification::make()
                ->title('No hay pagos para confirmar.')
                ->body('Adicione al menos un pago válido a la cola.')
                ->warning()
                ->send();

            return;
        }

        if (! $this->student_id) {
            Notification::make()
                ->title('Seleccione un estudiante.')
                ->warning()
                ->send();

            return;
        }

        $usuarioId = auth()->id();

        if (! $usuarioId) {
            Notification::make()
                ->title('No se encontró el usuario de la sesión.')
                ->danger()
                ->send();

            return;
        }

        try {
            $resultado = $registrarPagoService->registrar(
                sedeId: (int) $this->sede_id,
                periodoLectivoId: (int) $this->periodo_lectivo_id,
                studentId: (int) $this->student_id,
                colaPagos: $this->colaPagos,
                recibidoDe: $this->recibiDe,
                registradoPor: (int) $usuarioId,
                fechaPago: now(),
            );

            /*
            |--------------------------------------------------------------------------
            | Guardar referencias antes de limpiar la atención
            |--------------------------------------------------------------------------
            */
            $operacionPagoId = (int) $resultado['operacion_id'];
            $numeroRecibo = (int) $resultado['numero_recibo'];
            $studentId = (int) $this->student_id;

            $this->ultimoNumeroRecibo = $numeroRecibo;
            $this->ultimaOperacionPagoId = $operacionPagoId;

            /*
            |--------------------------------------------------------------------------
            | Limpiar la atención confirmada
            |--------------------------------------------------------------------------
            */
            $this->colaPagos = [];
            $this->secuenciaCola = 0;

            $this->resetValidation();

            unset(
                $this->subtotalCola,
                $this->descuentosCola,
                $this->totalAplicadoCola,
                $this->saldoFavorGeneradoCola,
                $this->puedeConfirmarCola,
            );

            $this->limpiarFormularioPago();

            /*
            * Recibí de también se limpia porque la operación ya fue confirmada.
            */
            $this->recibiDe = '';

            /*
            |--------------------------------------------------------------------------
            | Recargar cartera del estudiante
            |--------------------------------------------------------------------------
            | Al volver a consultar:
            | - las obligaciones pagadas completamente desaparecen;
            | - los abonos parciales permanecen con su nuevo saldo;
            | - los saldos a favor generados aparecen en el resumen;
            | - el historial incluye el recibo recién registrado.
            */
            $this->seleccionarEstudiante($studentId);

            /*
            * seleccionarEstudiante() puede limpiar propiedades de la atención.
            * Restauramos la referencia del recibo recién confirmado.
            */
            $this->ultimoNumeroRecibo = $numeroRecibo;
            $this->ultimaOperacionPagoId = $operacionPagoId;

            /*
            |--------------------------------------------------------------------------
            | Abrir automáticamente el detalle del recibo recién generado
            |--------------------------------------------------------------------------
            */
            $this->abrirDetalleRecibo(
                $operacionPagoId,
                app(DetalleReciboService::class),
            );

            Notification::make()
                ->title('Pagos registrados correctamente.')
                ->body(
                    'Recibo N.º '
                    . $numeroRecibo
                    . ' · Total recibido: $'
                    . number_format(
                        $resultado['total_recibido'],
                        0,
                        ',',
                        '.'
                    )
                )
                ->success()
                ->send();
        } catch (ValidationException $exception) {
            $mensaje = collect($exception->errors())
                ->flatten()
                ->first();

            Notification::make()
                ->title('No fue posible registrar los pagos.')
                ->body($mensaje ?: 'Revise la información de la cola.')
                ->warning()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Ocurrió un error al registrar los pagos.')
                ->body(
                    'No se guardó ninguna parte de la operación. '
                    . 'Revise el registro del sistema.'
                )
                ->danger()
                ->send();
        }
    }

    public function pendienteObligacionEnCola(int $movimientoId): float
    {
        $obligacion = collect($this->obligaciones)
            ->firstWhere('id', $movimientoId);

        if (! $obligacion) {
            return 0;
        }

        $saldoOriginal = (float) ($obligacion['saldo_pendiente'] ?? 0);

        $totalCubiertoEnCola = (float) collect($this->colaPagos)
            ->where('movimiento_id', $movimientoId)
            ->sum('valor_aplicado');

        return max(
            0,
            round($saldoOriginal - $totalCubiertoEnCola, 2)
        );
    }

    public function esUltimaFilaDeObligacion(int $filaId, int $movimientoId): bool
    {
        $ultimaFila = collect($this->colaPagos)
            ->where('movimiento_id', $movimientoId)
            ->last();

        return (int) ($ultimaFila['fila_id'] ?? 0) === $filaId;
    }

    public function cargarHistorialPagos(
        HistorialPagosService $historialPagosService
    ): void {
        if (
            ! $this->student_id
            || ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            $this->historialPagos = [];

            $this->resumenHistorial = [
                'pagos' => 0,
                'descuentos' => 0,
                'total_pagado' => 0,
                'cantidad_filas' => 0,
            ];

            return;
        }

        $resultado = $historialPagosService->consultar(
            studentId: (int) $this->student_id,
            sedeId: (int) $this->sede_id,
            periodoLectivoId: (int) $this->periodo_lectivo_id,
            numeroRecibo: $this->filtroHistorialRecibo,
            concepto: $this->filtroHistorialConcepto,
            fechaDesde: $this->filtroHistorialDesde,
            fechaHasta: $this->filtroHistorialHasta,
            estado: $this->filtroHistorialEstado,
        );

        $this->historialPagos = $resultado['filas'];

        $this->resumenHistorial = $resultado['resumen'];
    }

    public function updatedFiltroHistorialRecibo(): void
    {
        $this->cargarHistorialPagos(
            app(HistorialPagosService::class)
        );
    }

    public function updatedFiltroHistorialDesde(): void
    {
        $this->cargarHistorialPagos(
            app(HistorialPagosService::class)
        );
    }

    public function updatedFiltroHistorialHasta(): void
    {
        $this->cargarHistorialPagos(
            app(HistorialPagosService::class)
        );
    }

    public function updatedFiltroHistorialEstado(): void
    {
        $this->cargarHistorialPagos(
            app(HistorialPagosService::class)
        );
    }

    public function updatedFiltroHistorialConcepto(): void
    {
        $this->cargarHistorialPagos(
            app(HistorialPagosService::class)
        );
    }

    public function abrirDetalleRecibo(
        int $operacionPagoId,
        DetalleReciboService $detalleReciboService,
    ): void {
        if (
            ! $this->student_id
            || ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            return;
        }

        try {
            $this->detalleRecibo = $detalleReciboService->consultar(
                operacionPagoId: $operacionPagoId,
                studentId: (int) $this->student_id,
                sedeId: (int) $this->sede_id,
                periodoLectivoId: (int) $this->periodo_lectivo_id,
            );

            $this->operacionDetalleReciboId = $operacionPagoId;
            $this->mostrarDetalleRecibo = true;
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('No fue posible abrir el recibo.')
                ->body('La información del recibo no está disponible.')
                ->danger()
                ->send();
        }
    }

    public function cerrarDetalleRecibo(): void
    {
        $this->mostrarDetalleRecibo = false;
        $this->operacionDetalleReciboId = null;
        $this->detalleRecibo = [];
    }


    public function imprimirRecibo(
        ReciboPdfService $reciboPdfService,
        DetalleReciboService $detalleReciboService,
    ): void {
        if (
            ! $this->operacionDetalleReciboId
            || ! $this->student_id
            || ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            Notification::make()
                ->title('No se encontró el recibo.')
                ->warning()
                ->send();

            return;
        }

        $usuarioId = auth()->id();

        if (! $usuarioId) {
            Notification::make()
                ->title('No se encontró el usuario de la sesión.')
                ->danger()
                ->send();

            return;
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | Generar PDF y registrar impresión original
            |--------------------------------------------------------------------------
            */
            $resultado = $reciboPdfService->generarOriginal(
                operacionPagoId: (int) $this->operacionDetalleReciboId,
                studentId: (int) $this->student_id,
                sedeId: (int) $this->sede_id,
                periodoLectivoId: (int) $this->periodo_lectivo_id,
                generadoPor: (int) $usuarioId,
            );

            /*
            |--------------------------------------------------------------------------
            | Recargar el detalle
            |--------------------------------------------------------------------------
            | Esto actualiza:
            | - historial de impresión;
            | - usuario;
            | - fecha y hora;
            | - botón Imprimir → Reimprimir.
            */
            $this->detalleRecibo = $detalleReciboService->consultar(
                operacionPagoId: (int) $this->operacionDetalleReciboId,
                studentId: (int) $this->student_id,
                sedeId: (int) $this->sede_id,
                periodoLectivoId: (int) $this->periodo_lectivo_id,
            );

            /*
            |--------------------------------------------------------------------------
            | Abrir PDF en una pestaña nueva
            |--------------------------------------------------------------------------
            */
            $this->dispatch(
                'abrir-pdf-recibo',
                url: $resultado['url'],
            );

            Notification::make()
                ->title('Recibo generado correctamente.')
                ->body(
                    'Impresión original del recibo N.º '
                    . $resultado['identificador']
                )
                ->success()
                ->send();
        } catch (ValidationException $exception) {
            $mensaje = collect($exception->errors())
                ->flatten()
                ->first();

            Notification::make()
                ->title('No fue posible imprimir el recibo.')
                ->body(
                    $mensaje
                        ?: 'Revise la información del recibo.'
                )
                ->warning()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Ocurrió un error al generar el recibo.')
                ->body(
                    'No se registró la impresión. '
                    . 'Revise el registro del sistema.'
                )
                ->danger()
                ->send();
        }
    }

    public function abrirModalReimpresion(): void
    {
        $this->motivoReimpresion = '';
        $this->mostrarModalReimpresion = true;
    }

    public function cerrarModalReimpresion(): void
    {
        $this->mostrarModalReimpresion = false;
        $this->motivoReimpresion = '';
    }

    public function reimprimirRecibo(
        ReciboPdfService $reciboPdfService,
        DetalleReciboService $detalleReciboService,
    ): void {
        if (
            ! $this->operacionDetalleReciboId
            || ! $this->student_id
            || ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            Notification::make()
                ->title('No se encontró el recibo.')
                ->warning()
                ->send();

            return;
        }

        $usuarioId = auth()->id();

        if (! $usuarioId) {
            Notification::make()
                ->title('No se encontró el usuario de la sesión.')
                ->danger()
                ->send();

            return;
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | Generar PDF de reimpresión y registrar R#
            |--------------------------------------------------------------------------
            */
            $resultado = $reciboPdfService->generarReimpresion(
                operacionPagoId: (int) $this->operacionDetalleReciboId,
                studentId: (int) $this->student_id,
                sedeId: (int) $this->sede_id,
                periodoLectivoId: (int) $this->periodo_lectivo_id,
                generadoPor: (int) $usuarioId,
                motivo: $this->motivoReimpresion,
            );

            /*
            |--------------------------------------------------------------------------
            | Recargar detalle e historial
            |--------------------------------------------------------------------------
            */
            $this->detalleRecibo = $detalleReciboService->consultar(
                operacionPagoId: (int) $this->operacionDetalleReciboId,
                studentId: (int) $this->student_id,
                sedeId: (int) $this->sede_id,
                periodoLectivoId: (int) $this->periodo_lectivo_id,
            );

            /*
            |--------------------------------------------------------------------------
            | Cerrar modal
            |--------------------------------------------------------------------------
            */
            $this->cerrarModalReimpresion();

            /*
            |--------------------------------------------------------------------------
            | Abrir PDF generado
            |--------------------------------------------------------------------------
            */
            $this->dispatch(
                'abrir-pdf-recibo',
                url: $resultado['url'],
            );

            Notification::make()
                ->title('Reimpresión generada correctamente.')
                ->body(
                    'Documento '
                    . $resultado['identificador']
                )
                ->success()
                ->send();
        } catch (ValidationException $exception) {
            $mensaje = collect($exception->errors())
                ->flatten()
                ->first();

            Notification::make()
                ->title('No fue posible reimprimir el recibo.')
                ->body(
                    $mensaje
                        ?: 'Revise la información del recibo.'
                )
                ->warning()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Ocurrió un error al generar la reimpresión.')
                ->body(
                    'No se registró la reimpresión. '
                    . 'Revise el registro del sistema.'
                )
                ->danger()
                ->send();
        }
    }


    public function actualizarDetalleReciboDespuesDeImpresion(
        DetalleReciboService $detalleReciboService,
    ): void {
        if (
            ! $this->mostrarDetalleRecibo
            || ! $this->operacionDetalleReciboId
            || ! $this->student_id
            || ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            return;
        }

        try {
            $this->detalleRecibo = $detalleReciboService->consultar(
                operacionPagoId: (int) $this->operacionDetalleReciboId,
                studentId: (int) $this->student_id,
                sedeId: (int) $this->sede_id,
                periodoLectivoId: (int) $this->periodo_lectivo_id,
            );

            /*
            * Si la reimpresión fue generada desde el modal,
            * lo cerramos al regresar desde el PDF.
            */
            $this->mostrarModalReimpresion = false;
            $this->motivoReimpresion = '';
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

   


    public function abrirModalAnulacion(
        int $operacionPagoId
    ): void {
        if ($operacionPagoId <= 0) {
            return;
        }

        $this->operacionAnularId = $operacionPagoId;
        $this->motivoAnulacion = '';
        $this->mostrarModalAnulacion = true;
    }



    public function cerrarModalAnulacion(): void
    {
        $this->mostrarModalAnulacion = false;
        $this->operacionAnularId = null;
        $this->motivoAnulacion = '';

        $this->resetValidation([
            'motivoAnulacion',
        ]);
    }



    public function anularOperacionPago(
        AnularOperacionPagoService $anularOperacionPagoService,
        DetalleReciboService $detalleReciboService,
    ): void {
        if (
            ! $this->operacionAnularId
            || ! $this->student_id
            || ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            Notification::make()
                ->title('No se encontró la operación.')
                ->warning()
                ->send();

            return;
        }

        $usuarioId = auth()->id();

        if (! $usuarioId) {
            Notification::make()
                ->title('No se encontró el usuario de la sesión.')
                ->danger()
                ->send();

            return;
        }

        try {
            $resultado = $anularOperacionPagoService->anular(
                operacionPagoId: (int) $this->operacionAnularId,
                studentId: (int) $this->student_id,
                sedeId: (int) $this->sede_id,
                periodoLectivoId: (int) $this->periodo_lectivo_id,
                anuladoPor: (int) $usuarioId,
                motivo: $this->motivoAnulacion,
            );

            $operacionAnuladaId =
                (int) $resultado['operacion_id'];

            /*
            |--------------------------------------------------------------------------
            | Recordar si el detalle de esta operación estaba abierto
            |--------------------------------------------------------------------------
            */
            $detalleEstabaAbierto =
                $this->mostrarDetalleRecibo
                && (int) $this->operacionDetalleReciboId
                    === $operacionAnuladaId;

            $this->cerrarModalAnulacion();

            /*
            |--------------------------------------------------------------------------
            | Recargar cartera, obligaciones e historial
            |--------------------------------------------------------------------------
            */
            $studentId = (int) $this->student_id;

            $this->seleccionarEstudiante($studentId);

            /*
            |--------------------------------------------------------------------------
            | Restaurar y actualizar el detalle anulado
            |--------------------------------------------------------------------------
            */
            if ($detalleEstabaAbierto) {
                $this->operacionDetalleReciboId =
                    $operacionAnuladaId;

                $this->detalleRecibo =
                    $detalleReciboService->consultar(
                        operacionPagoId:
                            $operacionAnuladaId,

                        studentId:
                            (int) $this->student_id,

                        sedeId:
                            (int) $this->sede_id,

                        periodoLectivoId:
                            (int) $this->periodo_lectivo_id,
                    );

                $this->mostrarDetalleRecibo = true;
            }

            Notification::make()
                ->title('Pago anulado correctamente.')
                ->body(
                    'Recibo N.º '
                    . $resultado['numero_recibo']
                    . '. La cartera del estudiante fue actualizada.'
                )
                ->success()
                ->send();
        } catch (ValidationException $exception) {
            $mensaje = collect($exception->errors())
                ->flatten()
                ->first();

            Notification::make()
                ->title('No fue posible anular el pago.')
                ->body(
                    $mensaje
                        ?: 'Revise la información de la operación.'
                )
                ->warning()
                ->persistent()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Ocurrió un error al anular el pago.')
                ->body(
                    'No se modificó ninguna parte de la operación. '
                    . 'Revise el registro del sistema.'
                )
                ->danger()
                ->send();
        }
    }



    public function cargarAcuerdosPago(
        AcuerdosPagoService $acuerdosPagoService
    ): void {
        if (
            ! $this->student_id
            || ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            $this->acuerdosPagoOriginales = [];
            $this->acuerdosPago = [];

            return;
        }

        $this->acuerdosPagoOriginales =
            $acuerdosPagoService
                ->listar(
                    studentId:
                        (int) $this->student_id,

                    sedeId:
                        (int) $this->sede_id,

                    periodoLectivoId:
                        (int) $this->periodo_lectivo_id,
                )
                ->values()
                ->toArray();

        $this->aplicarFiltrosAcuerdos();
    }


    public function abrirModalAcuerdoPago(): void
    {
        if (! $this->student_id) {
            Notification::make()
                ->title('Seleccione un estudiante.')
                ->warning()
                ->send();

            return;
        }

        $this->limpiarFormularioAcuerdo();

        $this->modoModalAcuerdoPago = 'crear';
        $this->acuerdoPagoSeleccionadoId = null;
        $this->acuerdoEvidenciasGuardadas = [];

        $this->acuerdoPersona = trim(
            (string) (
                $this->estudianteSeleccionado['acudiente']
                ?? ''
            )
        );

        $this->acuerdoParentesco = trim(
            (string) (
                $this->estudianteSeleccionado['parentesco']
                ?? ''
            )
        );

        $this->mostrarModalAcuerdoPago = true;
        $this->acuerdoEstado = 'vigente';
        $this->acuerdoEstadoModificadoPor = null;
        $this->acuerdoEstadoModificadoEn = null;
    }

    public function cerrarModalAcuerdoPago(): void
    {
        $this->mostrarModalAcuerdoPago = false;

        $this->limpiarFormularioAcuerdo();
    }

    private function limpiarFormularioAcuerdo(): void
    {
        $this->acuerdoPersona = '';
        $this->acuerdoParentesco = '';
        $this->acuerdoFechaCompromiso = null;
        
        $this->acuerdoTexto = '';
        $this->acuerdoEvidencias = [];
        $this->acuerdoEvidenciasNuevas = [];

        $this->modoModalAcuerdoPago = 'crear';
        $this->acuerdoPagoSeleccionadoId = null;
        $this->acuerdoEvidenciasGuardadas = [];
        $this->mensajeEvidencias = null;
        $this->acuerdoEstado = 'vigente';

        $this->resetValidation([
            'acuerdoPersona',
            'acuerdoParentesco',
            'acuerdoFechaCompromiso',
            'acuerdoValorComprometido',
            'acuerdoTexto',
            'acuerdoEvidencias',
            'acuerdoEvidencias.*',
        ]);
    }

    public function guardarAcuerdoPago(
        CrearAcuerdoPagoService $crearAcuerdoPagoService,
        AcuerdosPagoService $acuerdosPagoService,
    ): void {
        if (
            ! $this->student_id
            || ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            Notification::make()
                ->title('No se encontró el contexto del estudiante.')
                ->warning()
                ->send();

            return;
        }

        $datos = $this->validate([
            'acuerdoPersona' => [
                'required',
                'string',
                'max:255',
            ],

            'acuerdoParentesco' => [
                'nullable',
                'string',
                'max:100',
            ],

            'acuerdoFechaCompromiso' => [
                'required',
                'date',
            ],

            

            'acuerdoTexto' => [
                'required',
                'string',
                'max:2000',
            ],

            'acuerdoEvidencias' => [
                'array',
                'max:3',
            ],

            'acuerdoEvidencias.*' => [
                'file',
                'max:10240',
                'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx',
            ],
        ], [
            'acuerdoPersona.required' =>
                'Indique la persona que realiza el acuerdo.',

            'acuerdoFechaCompromiso.required' =>
                'Seleccione la fecha de compromiso.',

            'acuerdoTexto.required' =>
                'Escriba el contenido del acuerdo.',

            'acuerdoEvidencias.max' =>
                'Puede adjuntar máximo 3 evidencias.',

            'acuerdoEvidencias.*.max' =>
                'Cada archivo puede pesar máximo 10 MB.',

            'acuerdoEvidencias.*.mimes' =>
                'Formato de evidencia no permitido.',
        ]);

        $usuarioId = auth()->id();

        if (! $usuarioId) {
            Notification::make()
                ->title('No se encontró el usuario de la sesión.')
                ->danger()
                ->send();

            return;
        }

        try {
            $crearAcuerdoPagoService->crear(
                studentId: (int) $this->student_id,
                sedeId: (int) $this->sede_id,
                periodoLectivoId:
                    (int) $this->periodo_lectivo_id,

                personaAcuerdo:
                    $datos['acuerdoPersona'],

                parentesco:
                    $datos['acuerdoParentesco'] ?: null,

                fechaCompromiso:
                    $datos['acuerdoFechaCompromiso'],

                valorComprometido: null,

                textoAcuerdo:
                    $datos['acuerdoTexto'],

                registradoPor:
                    (int) $usuarioId,

                evidencias:
                    $datos['acuerdoEvidencias'] ?? [],
            );

            $this->cerrarModalAcuerdoPago();

            $this->cargarAcuerdosPago(
                $acuerdosPagoService
            );

            Notification::make()
                ->title('Acuerdo registrado correctamente.')
                ->success()
                ->send();
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('No fue posible registrar el acuerdo.')
                ->body(
                    'No se guardó ninguna información. '
                    . 'Revise el registro del sistema.'
                )
                ->danger()
                ->send();
        }
    }


    public function agregarEvidenciasAcuerdo(
        array $archivos
    ): void {
        $actuales = collect($this->acuerdoEvidencias);

        $nuevos = collect($archivos)
            ->filter()
            ->reject(function ($archivo) use ($actuales) {
                return $actuales->contains(function ($actual) use ($archivo) {
                    return $actual->getClientOriginalName()
                        === $archivo->getClientOriginalName()
                        && $actual->getSize()
                        === $archivo->getSize();
                });
            });

        $this->acuerdoEvidencias = $actuales
            ->concat($nuevos)
            ->take(3)
            ->values()
            ->all();
    }

    public function updatedAcuerdoEvidenciasNuevas(): void
    {
        $this->mensajeEvidencias = null;

        $actuales = collect($this->acuerdoEvidencias);

        $nuevos = collect($this->acuerdoEvidenciasNuevas)
            ->filter()
            ->reject(function ($archivo) use ($actuales) {
                return $actuales->contains(function ($actual) use ($archivo) {
                    return $actual->getClientOriginalName()
                            === $archivo->getClientOriginalName()
                        && $actual->getSize()
                            === $archivo->getSize();
                });
            });

        $cantidadDisponible = max(
            0,
            3 - $actuales->count()
        );

        $this->mensajeEvidencias = null;

        if ($nuevos->count() > $cantidadDisponible) {

            $this->mensajeEvidencias =
                'Máximo 3 evidencias por acuerdo.';

            $nuevos = $nuevos->take(
                $cantidadDisponible
            );

        }

        $this->acuerdoEvidencias = $actuales
            ->concat($nuevos)
            ->take(3)
            ->values()
            ->all();

        $this->acuerdoEvidenciasNuevas = [];
    }

    public function eliminarEvidenciaTemporal(int $indice): void
    {
        unset($this->acuerdoEvidencias[$indice]);

        $this->acuerdoEvidencias = array_values(
            $this->acuerdoEvidencias
        );

        // Si vuelve a haber espacio disponible, desaparece el aviso.
        if (count($this->acuerdoEvidencias) < 3) {
            $this->mensajeEvidencias = null;
        }
    }

    public function verAcuerdoPago(int $acuerdoId): void
    {
        $acuerdo = collect($this->acuerdosPago)
            ->firstWhere('id', $acuerdoId);

        if (! $acuerdo) {
            Notification::make()
                ->title('No se encontró el acuerdo.')
                ->warning()
                ->send();

            return;
        }

        $this->limpiarFormularioAcuerdo();

        $this->modoModalAcuerdoPago = 'ver';
        $this->acuerdoPagoSeleccionadoId = $acuerdoId;

        $this->acuerdoPersona =
            (string) ($acuerdo['persona_acuerdo'] ?? '');

        $this->acuerdoParentesco =
            (string) ($acuerdo['parentesco'] ?? '');

        $this->acuerdoFechaCompromiso =
            $acuerdo['fecha_iso'] ?? null;

        $this->acuerdoTexto =
            (string) ($acuerdo['texto_acuerdo'] ?? '');

        $this->acuerdoEvidenciasGuardadas =
            $acuerdo['evidencias'] ?? [];

        

        $this->acuerdoEstado =
            (string) ($acuerdo['estado'] ?? 'vigente');

        $this->acuerdoEstadoModificadoPor =
            $acuerdo['estado_modificado_por'] ?? null;

        $this->acuerdoEstadoModificadoEn =
            $acuerdo['estado_modificado_en'] ?? null;


        $this->mostrarModalAcuerdoPago = true;
    }


    
    public function guardarEstadoAcuerdoPago(
        ActualizarEstadoAcuerdoPagoService $servicio,
        AcuerdosPagoService $acuerdosPagoService,
    ): void {
        if (
            ! $this->acuerdoPagoSeleccionadoId
            || ! $this->student_id
            || ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            Notification::make()
                ->title('No se encontró el acuerdo.')
                ->warning()
                ->send();

            return;
        }

        $estadoValido = in_array(
            $this->acuerdoEstado,
            [
                'vigente',
                'cumplido',
                'incumplido',
                'vencido',
                'anulado',
            ],
            true
        );

        if (! $estadoValido) {
            Notification::make()
                ->title('Seleccione un estado válido.')
                ->warning()
                ->send();

            return;
        }

        $usuarioId = auth()->id();

        if (! $usuarioId) {
            Notification::make()
                ->title('No se encontró el usuario de la sesión.')
                ->danger()
                ->send();

            return;
        }

        try {
            $servicio->actualizar(
                acuerdoId:
                    (int) $this->acuerdoPagoSeleccionadoId,

                studentId:
                    (int) $this->student_id,

                sedeId:
                    (int) $this->sede_id,

                periodoLectivoId:
                    (int) $this->periodo_lectivo_id,

                estado:
                    $this->acuerdoEstado,

                modificadoPor:
                    (int) $usuarioId,
            );

            $acuerdoId =
                (int) $this->acuerdoPagoSeleccionadoId;

            $this->cargarAcuerdosPago(
                $acuerdosPagoService
            );

            /*
            |--------------------------------------------------------------------------
            | Volver a cargar el acuerdo actualizado en el mismo modal
            |--------------------------------------------------------------------------
            */
            $this->verAcuerdoPago($acuerdoId);

            Notification::make()
                ->title('Estado actualizado correctamente.')
                ->success()
                ->send();
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $mensaje = collect($exception->errors())
                ->flatten()
                ->first();

            Notification::make()
                ->title('No fue posible actualizar el estado.')
                ->body(
                    $mensaje
                        ?: 'Revise la información del acuerdo.'
                )
                ->warning()
                ->send();
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Ocurrió un error al actualizar el estado.')
                ->danger()
                ->send();
        }
    }




    public function abrirModalEditarAcuerdoPago(
        int $acuerdoId
    ): void {
        $acuerdo = collect($this->acuerdosPago)
            ->firstWhere('id', $acuerdoId);

        if (! $acuerdo) {
            Notification::make()
                ->title('No se encontró el acuerdo.')
                ->warning()
                ->send();

            return;
        }

        if (($acuerdo['estado'] ?? '') !== 'vigente') {
            Notification::make()
                ->title('El acuerdo ya no se puede editar.')
                ->body(
                    'Solo los acuerdos vigentes permiten modificaciones.'
                )
                ->warning()
                ->send();

            return;
        }

        $this->limpiarFormularioAcuerdo();

        $this->modoModalAcuerdoPago = 'editar';
        $this->acuerdoPagoSeleccionadoId = $acuerdoId;

        $this->acuerdoPersona =
            (string) ($acuerdo['persona_acuerdo'] ?? '');

        $this->acuerdoParentesco =
            (string) ($acuerdo['parentesco'] ?? '');

        $this->acuerdoFechaCompromiso =
            $acuerdo['fecha_iso'] ?? null;

        $this->acuerdoTexto =
            (string) ($acuerdo['texto_acuerdo'] ?? '');

        $this->acuerdoEstado =
            (string) ($acuerdo['estado'] ?? 'vigente');

        $this->acuerdoEstadoModificadoPor =
            $acuerdo['estado_modificado_por'] ?? null;

        $this->acuerdoEstadoModificadoEn =
            $acuerdo['estado_modificado_en'] ?? null;

        $this->acuerdoEvidenciasGuardadas =
            $acuerdo['evidencias'] ?? [];

        $this->mostrarModalAcuerdoPago = true;
    }



    public function actualizarAcuerdoPago(
        ActualizarAcuerdoPagoService $servicio,
        AcuerdosPagoService $acuerdosPagoService,
    ): void {
        if (
            ! $this->acuerdoPagoSeleccionadoId
            || ! $this->student_id
            || ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            return;
        }

        $datos = $this->validate([
            'acuerdoPersona' => [
                'required',
                'string',
                'max:255',
            ],

            'acuerdoParentesco' => [
                'nullable',
                'string',
                'max:100',
            ],

            'acuerdoFechaCompromiso' => [
                'required',
                'date',
            ],

            'acuerdoTexto' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        try {
            $servicio->actualizar(
                acuerdoId:
                    (int) $this->acuerdoPagoSeleccionadoId,

                studentId:
                    (int) $this->student_id,

                sedeId:
                    (int) $this->sede_id,

                periodoLectivoId:
                    (int) $this->periodo_lectivo_id,

                personaAcuerdo:
                    $datos['acuerdoPersona'],

                parentesco:
                    $datos['acuerdoParentesco'] ?: null,

                fechaCompromiso:
                    $datos['acuerdoFechaCompromiso'],

                textoAcuerdo:
                    $datos['acuerdoTexto'],
            );

            $this->cargarAcuerdosPago(
                $acuerdosPagoService
            );

            $this->cerrarModalAcuerdoPago();

            Notification::make()
                ->title('Acuerdo actualizado correctamente.')
                ->success()
                ->send();
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('No fue posible actualizar el acuerdo.')
                ->danger()
                ->send();
        }
    }

    public function aplicarFiltrosAcuerdos(): void
    {
        $texto = mb_strtolower(
            trim($this->filtroAcuerdoTexto)
        );

        $this->acuerdosPago = collect(
            $this->acuerdosPagoOriginales
        )
            ->filter(function (array $acuerdo) use ($texto) {
                if ($texto === '') {
                    return true;
                }

                $contenido = mb_strtolower(
                    implode(' ', [
                        $acuerdo['persona_acuerdo'] ?? '',
                        $acuerdo['texto_acuerdo'] ?? '',
                        $acuerdo['parentesco'] ?? '',
                    ])
                );

                return str_contains(
                    $contenido,
                    $texto
                );
            })
            ->when(
                $this->filtroAcuerdoEstado !== '',
                fn ($coleccion) =>
                    $coleccion->where(
                        'estado',
                        $this->filtroAcuerdoEstado
                    )
            )
            ->filter(function (array $acuerdo) {
                $fecha = $acuerdo['fecha_iso'] ?? null;

                if (! $fecha) {
                    return true;
                }

                if (
                    $this->filtroAcuerdoDesde
                    && $fecha < $this->filtroAcuerdoDesde
                ) {
                    return false;
                }

                if (
                    $this->filtroAcuerdoHasta
                    && $fecha > $this->filtroAcuerdoHasta
                ) {
                    return false;
                }

                return true;
            })
            ->sortByDesc(
                fn (array $acuerdo) =>
                    sprintf(
                        '%s-%010d',
                        $acuerdo['fecha_iso'] ?? '0000-00-00',
                        $acuerdo['id'] ?? 0
                    )
            )
            ->values()
            ->all();
    }
    public function updatedFiltroAcuerdoTexto(): void
    {
        $this->aplicarFiltrosAcuerdos();
    }

    public function updatedFiltroAcuerdoEstado(): void
    {
        $this->aplicarFiltrosAcuerdos();
    }

    public function updatedFiltroAcuerdoDesde(): void
    {
        $this->aplicarFiltrosAcuerdos();
    }

    public function updatedFiltroAcuerdoHasta(): void
    {
        $this->aplicarFiltrosAcuerdos();
    }


}