<?php

namespace App\Filament\Pages;

use App\Models\OrdenPago;
use App\Services\Pagos\OrdenPagoService;
use App\Services\Pagos\ZonaPagosService;

use Filament\Notifications\Notification;
use Filament\Pages\Page;

use Illuminate\Validation\ValidationException;

use Throwable;

class ZonaPagos extends Page
{
    protected static ?string $navigationIcon =
        'heroicon-o-credit-card';

    protected static string $view =
        'filament.pages.zona-pagos';

    protected static ?string $navigationGroup =
        'Portal del Acudiente';

    protected static ?string $navigationLabel =
        'Pagos';

    protected static ?string $title =
        'Pagos';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug =
        'zona-pagos';

    protected ?string $maxContentWidth =
        'full';

    /*
    |--------------------------------------------------------------------------
    | DATOS DE LA ZONA DE PAGOS
    |--------------------------------------------------------------------------
    */

    public array $estudiantes = [];

    public int $cantidadObligaciones = 0;

    public float $totalPendiente = 0;

    /*
    |--------------------------------------------------------------------------
    | SELECCIÓN
    |--------------------------------------------------------------------------
    */

    public array $seleccionados = [];

    public array $resumenSeleccionado = [];

    public int $cantidadSeleccionada = 0;

    public float $totalSeleccionado = 0;

    public string $metodoPago = 'pse';

    /*
    |--------------------------------------------------------------------------
    | HISTORIAL DIGITAL
    |--------------------------------------------------------------------------
    */

    public array $pagosRecientes = [];

    public array $historialPagos = [];

    public bool $mostrarHistorialPagos = false;

    /*
    |--------------------------------------------------------------------------
    | ÚLTIMA ORDEN
    |--------------------------------------------------------------------------
    */

    public ?int $ultimaOrdenId = null;

    public ?string $ultimaOrdenReferencia = null;

    /*
    |--------------------------------------------------------------------------
    | ACCESO
    |--------------------------------------------------------------------------
    */

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return false;
        }

        return $usuario->hasAnyRole([
            'Acudiente',
            'acudiente',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | INICIO
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->cargarZonaPagos();
        $this->cargarPagosRecientes();
    }

    /*
    |--------------------------------------------------------------------------
    | OBLIGACIONES
    |--------------------------------------------------------------------------
    */

    private function cargarZonaPagos(): void
    {
        $datos = app(
            ZonaPagosService::class
        )->obtenerZonaPagos(
            (int) auth()->id()
        );

        $this->estudiantes =
            $datos['estudiantes'] ?? [];

        $this->cantidadObligaciones = (int) (
            $datos['cantidad_obligaciones']
            ?? collect($this->estudiantes)
                ->sum('cantidad_obligaciones')
        );

        $this->totalPendiente = (float) (
            $datos['total_pendiente']
            ?? collect($this->estudiantes)
                ->sum('total_pendiente')
        );

        $this->limpiarSeleccionInvalida();

        $this->recalcularResumen();
    }

    /*
    |--------------------------------------------------------------------------
    | SELECCIÓN
    |--------------------------------------------------------------------------
    */

    public function updatedSeleccionados(): void
    {
        $this->limpiarSeleccionInvalida();

        $this->recalcularResumen();
    }

    private function limpiarSeleccionInvalida(): void
    {
        $permitidos = collect($this->estudiantes)
            ->flatMap(
                fn (array $estudiante) =>
                    collect(
                        $estudiante['obligaciones'] ?? []
                    )
            )
            ->filter(
                fn (array $obligacion) =>
                    (bool) (
                        $obligacion['seleccionable']
                        ?? false
                    )
            )
            ->pluck('movimiento_id')
            ->map(
                fn ($id) => (int) $id
            )
            ->values();

        $this->seleccionados =
            collect($this->seleccionados)
                ->map(
                    fn ($id) => (int) $id
                )
                ->filter(
                    fn ($id) =>
                        $permitidos->contains($id)
                )
                ->unique()
                ->values()
                ->toArray();
    }

    private function recalcularResumen(): void
    {
        $seleccionados =
            collect($this->seleccionados)
                ->map(
                    fn ($id) => (int) $id
                );

        $resumen = [];

        foreach ($this->estudiantes as $estudiante) {
            $obligacionesSeleccionadas =
                collect(
                    $estudiante['obligaciones']
                    ?? []
                )
                    ->filter(
                        fn (array $obligacion) =>
                            $seleccionados->contains(
                                (int) $obligacion[
                                    'movimiento_id'
                                ]
                            )
                    )
                    ->values()
                    ->toArray();

            if (
                empty(
                    $obligacionesSeleccionadas
                )
            ) {
                continue;
            }

            $resumen[] = [
                'student_id' =>
                    $estudiante['id'],

                'nombre_completo' =>
                    $estudiante[
                        'nombre_completo'
                    ]
                    ?? 'Estudiante',

                'curso' =>
                    $estudiante['curso']
                    ?? '-',

                'obligaciones' =>
                    $obligacionesSeleccionadas,

                'subtotal' =>
                    collect(
                        $obligacionesSeleccionadas
                    )
                        ->sum(
                            'saldo_pendiente'
                        ),
            ];
        }

        $this->resumenSeleccionado =
            $resumen;

        $this->cantidadSeleccionada =
            collect($resumen)
                ->sum(
                    fn (
                        array $estudiante
                    ) =>
                        count(
                            $estudiante[
                                'obligaciones'
                            ]
                        )
                );

        $this->totalSeleccionado =
            (float) collect($resumen)
                ->sum('subtotal');
    }

    /*
    |--------------------------------------------------------------------------
    | CONTINUAR CON EL PAGO
    |--------------------------------------------------------------------------
    */

    public function continuarPago(
    OrdenPagoService $ordenPagoService
    ) {
        if (
            empty(
                $this->seleccionados
            )
        ) {
            Notification::make()
                ->title(
                    'Seleccione una obligación'
                )
                ->body(
                    'Seleccione al menos una obligación para continuar con el pago.'
                )
                ->warning()
                ->send();

            return;
        }

        if (
            $this->metodoPago !== 'pse'
        ) {
            Notification::make()
                ->title(
                    'Método no disponible'
                )
                ->body(
                    'Por ahora el único método de pago disponible es PSE.'
                )
                ->warning()
                ->send();

            return;
        }

        try {
            /*
             * OrdenPagoService vuelve a validar todo.
             *
             * No confiamos en los valores enviados
             * por la pantalla.
             */
            $orden =
                $ordenPagoService->crearOrden([
                    'user_id' =>
                        (int) auth()->id(),

                    'movimiento_ids' =>
                        $this->seleccionados,

                    'metodo_pago' =>
                        $this->metodoPago,
                ]);

            $this->ultimaOrdenId =
                $orden->id;

            $this->ultimaOrdenReferencia =
                $orden->referencia;

            /*
             * Más adelante:
             *
             * OrdenPago
             * -> proveedor
             * -> checkout_url
             * -> redirección externa.
             *
             * En este punto todavía NO se aplica
             * ningún pago a cartera.
             */

            $this->seleccionados = [];

            $this->cargarZonaPagos();

            $this->cargarPagosRecientes();

            Notification::make()
                ->title('Orden de pago creada')
                ->body(
                    'Orden '
                    . $orden->referencia
                    . ' por $ '
                    . number_format(
                        (float) $orden->total,
                        0,
                        ',',
                        '.'
                    )
                    . '. Será dirigido al portal de pagos.'
                )
                ->success()
                ->send();

            return redirect()->away(
                'https://www.zonapagos.com/PagosN3/Vistas/Logins/LoginCliente.aspx'
            );

        } catch (
            ValidationException $exception
        ) {
            $mensaje =
                collect(
                    $exception->errors()
                )
                    ->flatten()
                    ->first();

            Notification::make()
                ->title(
                    'No fue posible iniciar el pago'
                )
                ->body(
                    $mensaje
                    ?: 'Revise las obligaciones seleccionadas.'
                )
                ->warning()
                ->send();

            $this->cargarZonaPagos();

        } catch (
            Throwable $exception
        ) {
            report($exception);

            Notification::make()
                ->title(
                    'No fue posible iniciar el pago'
                )
                ->body(
                    'Ocurrió un error al preparar la orden. Ninguna obligación fue marcada como pagada.'
                )
                ->danger()
                ->send();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PAGOS RECIENTES
    |--------------------------------------------------------------------------
    */

    private function cargarPagosRecientes(): void
    {
        $this->pagosRecientes =
            $this->consultaOrdenesUsuario()
                ->limit(3)
                ->get()
                ->map(
                    fn (OrdenPago $orden) =>
                        $this->transformarOrden(
                            $orden
                        )
                )
                ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | HISTORIAL COMPLETO
    |--------------------------------------------------------------------------
    */

    public function abrirHistorialPagos(): void
    {
        $this->historialPagos =
            $this->consultaOrdenesUsuario()
                ->get()
                ->map(
                    fn (OrdenPago $orden) =>
                        $this->transformarOrden(
                            $orden
                        )
                )
                ->toArray();

        $this->mostrarHistorialPagos =
            true;
    }

    public function cerrarHistorialPagos(): void
    {
        $this->mostrarHistorialPagos =
            false;
    }

    /*
    |--------------------------------------------------------------------------
    | CONSULTA COMÚN
    |--------------------------------------------------------------------------
    */

    private function consultaOrdenesUsuario()
    {
        return OrdenPago::query()
            ->where(
                'user_id',
                (int) auth()->id()
            )
            ->with([
                'detalles.estudiante',
            ])
            ->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | FORMATO PARA LA PANTALLA
    |--------------------------------------------------------------------------
    */

    private function transformarOrden(
        OrdenPago $orden
    ): array {
        $estudiantes =
            $orden->detalles
                ->map(
                    fn ($detalle) =>
                        $detalle->estudiante
                )
                ->filter()
                ->unique('id')
                ->map(
                    function ($student) {
                        return trim(
                            $student->primer_nombre
                            . ' '
                            . $student->segundo_nombre
                            . ' '
                            . $student->primer_apellido
                            . ' '
                            . $student->segundo_apellido
                        );
                    }
                )
                ->filter()
                ->values()
                ->implode(', ');

        return [
            'id' =>
                $orden->id,

            'fecha' =>
                $orden->created_at
                    ?->format(
                        'd/m/Y h:i a'
                    ),

            'referencia' =>
                $orden->referencia,

            'estudiantes' =>
                $estudiantes ?: '-',

            'metodo_pago' =>
                strtoupper(
                    $orden->metodo_pago
                ),

            'total' =>
                (float) $orden->total,

            'estado' =>
                $orden->estado,
        ];
    }
    public function getHeading(): string
    {
        return '';
    }
}