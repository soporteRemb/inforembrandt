<?php

namespace App\Filament\Pages;

use App\Models\PeriodoLectivo;
use App\Models\PreMatricula;

use App\Services\PreMatriculas\PreMatriculaAdministracionService;
use App\Services\PreMatriculas\PreMatriculaDashboardService;
use App\Services\PreMatriculas\PreMatriculaFormularioService;
use App\Services\PreMatriculas\PreMatriculaExportacionService;
use App\Traits\HasPagePermissions;

use Filament\Notifications\Notification;
use Filament\Pages\Page;

use Illuminate\Validation\ValidationException;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

    /*
    |--------------------------------------------------------------------------
    | esta es la pantalla que ven los administrativos con la lista de las pre-matriculas
    |--------------------------------------------------------------------------
    |
    */

class PreMatriculas extends Page
{

        use HasPagePermissions;
       

    /*
    |--------------------------------------------------------------------------
    | Permisos
    |--------------------------------------------------------------------------
    */

    protected static ?string $viewPermission =
        'ver_pre_matriculas';


    protected static ?string $navigationIcon =
        'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Admisiones';

    protected static ?string $navigationLabel = 'Pre-matrículas';

    protected static ?string $title = 'Pre-matrículas';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'pre-matriculas';

    protected static string $view =
        'filament.pages.pre-matriculas';

    /*
    |--------------------------------------------------------------------------
    | Contexto activo
    |--------------------------------------------------------------------------
    */

    public ?int $sede_id = null;

    public ?int $periodo_lectivo_id = null;

    /*
    |--------------------------------------------------------------------------
    | Filtros
    |--------------------------------------------------------------------------
    */

    public string $buscar = '';

    public string $filtroEstado = '';

    public string $filtroGrado = '';

    public ?string $fechaDesde = null;

    public ?string $fechaHasta = null;

    /*
    |--------------------------------------------------------------------------
    | Información de pantalla
    |--------------------------------------------------------------------------
    */

    public array $resumen = [
        'total' => 0,
        'completadas' => 0,
        'pendientes' => 0,
        'vencidas' => 0,
        'hombres' => 0,
        'mujeres' => 0,

        'grado_mas_solicitado' => [
            'grado' => 'Sin información',
            'total' => 0,
        ],

        'grado_menos_solicitado' => [
            'grado' => 'Sin información',
            'total' => 0,
        ],

        'completadas_hoy' => 0,
        'completadas_semana' => 0,
    ];

    public array $formularios = [];

    public array $grados = [];

    public array $eps = [];

    /*
    |--------------------------------------------------------------------------
    | Modal de edición
    |--------------------------------------------------------------------------
    */

    public ?int $preMatriculaSeleccionadaId = null;

    public bool $mostrarModalDetalle = false;

    public array $formularioEdicion = [];

    /*
    |--------------------------------------------------------------------------
    | Carga inicial
    |--------------------------------------------------------------------------
    */

    public function mount(
        PreMatriculaDashboardService $dashboardService,
        PreMatriculaFormularioService $formularioService
    ): void {
        $this->sede_id = filled(session('sede_id'))
            ? (int) session('sede_id')
            : null;

        $anio = (string) session('anio');

        if ($this->sede_id && filled($anio)) {
            $this->periodo_lectivo_id =
                PeriodoLectivo::query()
                    ->where('sede_id', $this->sede_id)
                    ->where('nombre', $anio)
                    ->value('id');
        }

        $this->grados =
            $formularioService->obtenerGrados();

        $this->eps = $formularioService
            ->obtenerEpsActivas()
            ->pluck('nombre', 'id')
            ->toArray();

        $this->cargarPantalla($dashboardService);
    }

    /*
    |--------------------------------------------------------------------------
    | Cargar tarjetas y listado
    |--------------------------------------------------------------------------
    */

    private function cargarPantalla(
        ?PreMatriculaDashboardService $dashboardService = null
    ): void {
        if (
            ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            $this->formularios = [];

            return;
        }

        $dashboardService ??=
            app(PreMatriculaDashboardService::class);

        $this->resumen = $dashboardService->obtenerResumen(
            $this->sede_id,
            $this->periodo_lectivo_id
        );

        $registros = $dashboardService->obtenerListado(
            sedeId: $this->sede_id,
            periodoLectivoId: $this->periodo_lectivo_id,
            buscar: $this->buscar,
            estado: $this->filtroEstado,
            grado: $this->filtroGrado,
            fechaDesde: $this->fechaDesde,
            fechaHasta: $this->fechaHasta,
        );

        $this->formularios = $registros
            ->map(fn (PreMatricula $preMatricula): array => [
                'id' => $preMatricula->id,

                'numero_formulario' =>
                    $preMatricula->numero_formulario,

                'estudiante' => trim(
                    ($preMatricula->nombres ?? '')
                    . ' '
                    . ($preMatricula->apellidos ?? '')
                ) ?: 'Sin diligenciar',

                'documento' =>
                    $preMatricula->documento ?: '-',

                'grado' =>
                    $preMatricula->grado_aspira
                    ?: 'Sin diligenciar',

                'genero' => filled($preMatricula->genero)
                    ? ucfirst(
                        mb_strtolower(
                            $preMatricula->genero,
                            'UTF-8'
                        )
                    )
                    : 'Sin diligenciar',

                'estado' =>
                    $preMatricula->estado,

                'fecha_envio' =>
                    $preMatricula->fecha_envio
                        ? $preMatricula->fecha_envio
                            ->format('d/m/Y H:i')
                        : null,

                'acudiente' =>
                    $preMatricula->acudiente_nombre
                    ?: 'Sin diligenciar',
            ])
            ->values()
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Actualización reactiva de filtros
    |--------------------------------------------------------------------------
    */

    public function updatedBuscar(): void
    {
        $this->cargarPantalla();
    }

    public function updatedFiltroEstado(): void
    {
        $this->cargarPantalla();
    }

    public function updatedFiltroGrado(): void
    {
        $this->cargarPantalla();
    }

    public function updatedFechaDesde(): void
    {
        $this->cargarPantalla();
    }

    public function updatedFechaHasta(): void
    {
        $this->cargarPantalla();
    }

    public function limpiarFiltros(): void
    {
        $this->buscar = '';
        $this->filtroEstado = '';
        $this->filtroGrado = '';
        $this->fechaDesde = null;
        $this->fechaHasta = null;

        $this->cargarPantalla();
    }

    /*
    |--------------------------------------------------------------------------
    | Abrir modal con información real
    |--------------------------------------------------------------------------
    */

    public function seleccionarPreMatricula(
        int $id,
        PreMatriculaAdministracionService $administracionService
    ): void {
        if (
            ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            Notification::make()
                ->title('Contexto académico incompleto.')
                ->body(
                    'No se encontró la sede o el período lectivo activo.'
                )
                ->warning()
                ->send();

            return;
        }

        $preMatricula = $administracionService
            ->obtenerFormulario(
                preMatriculaId: $id,
                sedeId: $this->sede_id,
                periodoLectivoId:
                    $this->periodo_lectivo_id,
            );

        $this->preMatriculaSeleccionadaId =
            $preMatricula->id;

        $this->formularioEdicion =
            $administracionService
                ->prepararFormularioEdicion(
                    $preMatricula
                );

        $this->mostrarModalDetalle = true;
    }

    public function cerrarModalDetalle(): void
    {
        $this->mostrarModalDetalle = false;
        $this->formularioEdicion = [];
        $this->preMatriculaSeleccionadaId = null;

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar cambios reales
    |--------------------------------------------------------------------------
    */

    public function guardarCambios(
        PreMatriculaAdministracionService $administracionService
    ): void {
        $usuario = auth()->user();

        if (! $usuario) {
            return;
        }

        if (
            ! $this->preMatriculaSeleccionadaId
            || ! $this->sede_id
            || ! $this->periodo_lectivo_id
        ) {
            Notification::make()
                ->title('No se pudo identificar el formulario.')
                ->warning()
                ->send();

            return;
        }

        $datos = $this->validate(
            $this->reglasEdicion(),
            $this->mensajesEdicion()
        )['formularioEdicion'];

        $preMatricula = $administracionService
            ->obtenerFormulario(
                preMatriculaId:
                    $this->preMatriculaSeleccionadaId,
                sedeId: $this->sede_id,
                periodoLectivoId:
                    $this->periodo_lectivo_id,
            );

        $administracionService->guardarCambios(
            $preMatricula,
            $datos,
            $usuario
        );

        $this->cerrarModalDetalle();
        $this->cargarPantalla();

        Notification::make()
            ->title('Pre-matrícula actualizada.')
            ->body(
                'Los cambios fueron guardados y registrados en el historial.'
            )
            ->success()
            ->send();
    }

    private function reglasEdicion(): array
    {
        return [
            'formularioEdicion.nombres' =>
                ['required', 'string', 'max:120'],

            'formularioEdicion.apellidos' =>
                ['required', 'string', 'max:120'],

            'formularioEdicion.tipo_documento' =>
                ['required', 'string', 'max:10'],

            'formularioEdicion.documento' =>
                ['required', 'string', 'max:30'],

            'formularioEdicion.ciudad_expedicion' =>
                ['required', 'string', 'max:100'],

            'formularioEdicion.fecha_nacimiento' =>
                ['required', 'date', 'before:today'],

            'formularioEdicion.ciudad_nacimiento' =>
                ['required', 'string', 'max:100'],

            'formularioEdicion.genero' =>
                ['required', 'in:masculino,femenino'],

            'formularioEdicion.numero_hermanos' =>
                ['nullable', 'integer', 'min:0'],

            'formularioEdicion.telefono' =>
                ['nullable', 'string', 'max:30'],

            'formularioEdicion.correo' =>
                ['required', 'email', 'max:150'],

            'formularioEdicion.direccion' =>
                ['required', 'string', 'max:180'],

            'formularioEdicion.rh' =>
                ['required', 'string', 'max:5'],

            'formularioEdicion.eps_id' =>
                ['required', 'exists:eps,id'],

            'formularioEdicion.telefono_emergencia' =>
                ['required', 'string', 'max:30'],

            'formularioEdicion.grado' =>
                ['required', 'string', 'max:30'],

            'formularioEdicion.institucion_anterior' =>
                ['required', 'string', 'max:180'],

            'formularioEdicion.condicion_ingreso' =>
                ['required', 'string', 'max:30'],

            'formularioEdicion.padre_nombre' =>
                ['nullable', 'string', 'max:180'],

            'formularioEdicion.padre_telefono' =>
                ['nullable', 'string', 'max:40'],

            'formularioEdicion.padre_tipo_documento' =>
                ['nullable', 'string', 'max:30'],

            'formularioEdicion.padre_documento' =>
                ['nullable', 'string', 'max:50'],

            'formularioEdicion.padre_lugar_trabajo' =>
                ['nullable', 'string', 'max:180'],

            'formularioEdicion.padre_correo' =>
                ['nullable', 'email', 'max:180'],

            'formularioEdicion.padre_direccion' =>
                ['nullable', 'string', 'max:255'],

            'formularioEdicion.madre_nombre' =>
                ['nullable', 'string', 'max:180'],

            'formularioEdicion.madre_telefono' =>
                ['nullable', 'string', 'max:40'],

            'formularioEdicion.madre_tipo_documento' =>
                ['nullable', 'string', 'max:30'],

            'formularioEdicion.madre_documento' =>
                ['nullable', 'string', 'max:50'],

            'formularioEdicion.madre_lugar_trabajo' =>
                ['nullable', 'string', 'max:180'],

            'formularioEdicion.madre_correo' =>
                ['nullable', 'email', 'max:180'],

            'formularioEdicion.madre_direccion' =>
                ['nullable', 'string', 'max:255'],

            'formularioEdicion.acudiente_origen' =>
                ['nullable', 'in:padre,madre,otro'],

            'formularioEdicion.acudiente_parentesco' =>
                ['nullable', 'string', 'max:60'],

            'formularioEdicion.acudiente_nombre' =>
                ['nullable', 'string', 'max:150'],

            'formularioEdicion.acudiente_telefono' =>
                ['nullable', 'string', 'max:30'],

            'formularioEdicion.acudiente_tipo_documento' =>
                ['nullable', 'string', 'max:30'],

            'formularioEdicion.acudiente_documento' =>
                ['nullable', 'string', 'max:30'],

            'formularioEdicion.acudiente_lugar_trabajo' =>
                ['nullable', 'string', 'max:150'],

            'formularioEdicion.acudiente_correo' =>
                ['nullable', 'email', 'max:150'],

            'formularioEdicion.acudiente_direccion' =>
                ['nullable', 'string', 'max:255'],
        ];
    }

    private function mensajesEdicion(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'email' =>
                'Ingrese un correo electrónico válido.',
            'date' => 'Ingrese una fecha válida.',
            'before' =>
                'La fecha debe ser anterior a hoy.',
            'integer' =>
                'Ingrese un número válido.',
            'min' =>
                'El valor ingresado no es válido.',
            'exists' =>
                'La opción seleccionada no es válida.',
            'in' =>
                'La opción seleccionada no es válida.',
        ];
    }

    public function getHeading(): string
    {
        return '';
    }

    

    public function exportarEstudiantes(): ?BinaryFileResponse
    {
        [$sedeId, $periodoLectivoId] =
            $this->obtenerContextoAcademicoActivo();

        if (! $sedeId || ! $periodoLectivoId) {
            Notification::make()
                ->title('No fue posible exportar')
                ->body(
                    'No se encontró la sede o el periodo lectivo activos.'
                )
                ->warning()
                ->send();

            return null;
        }

        return app(
            PreMatriculaExportacionService::class
        )->exportarEstudiantes(
            $sedeId,
            $periodoLectivoId
        );
    }

    public function exportarAcudientes(): ?BinaryFileResponse
    {
        [$sedeId, $periodoLectivoId] =
            $this->obtenerContextoAcademicoActivo();

        if (! $sedeId || ! $periodoLectivoId) {
            Notification::make()
                ->title('No fue posible exportar')
                ->body(
                    'No se encontró la sede o el periodo lectivo activos.'
                )
                ->warning()
                ->send();

            return null;
        }

        return app(
            PreMatriculaExportacionService::class
        )->exportarAcudientes(
            $sedeId,
            $periodoLectivoId
        );
    }

    private function obtenerContextoAcademicoActivo(): array
    {
        $sedeId = (int) session('sede_id');

        $periodoLectivoId = (int) (
            session('periodo_lectivo_id')
            ?: session('periodo_id')
            ?: session('periodoLectivoId')
        );

        /*
        |--------------------------------------------------------------------------
        | Recuperar el periodo desde la sede activa
        |--------------------------------------------------------------------------
        |
        | Si la sesión no guarda directamente el ID del periodo, usamos el periodo
        | abierto correspondiente a la sede activa.
        |
        */

        if ($sedeId && ! $periodoLectivoId) {
            $periodoLectivoId = (int) PeriodoLectivo::query()
                ->where('sede_id', $sedeId)
                ->where('estado', 'abierto')
                ->orderByDesc('nombre')
                ->value('id');
        }

        return [
            $sedeId,
            $periodoLectivoId,
        ];
    }


}