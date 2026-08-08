<?php

namespace App\Filament\Pages;

use App\Models\PeriodoLectivo;
use App\Models\Sede;

use App\Services\Importacion\DTO\ResultadoImportacion;
use App\Services\Importacion\Estudiantes\EstudiantesImportService;
use App\Services\Importacion\Pensum\PensumImportService;
use App\Services\Importacion\Docentes\DocentesImportService;
use App\Services\Importacion\Asignaciones\AsignacionesImportService;
use App\Services\Importacion\Acudientes\AcudientesImportService;

use App\Traits\HasPagePermissions;


use Filament\Notifications\Notification;
use Filament\Pages\Page;

use Livewire\WithFileUploads;


use Illuminate\Support\Facades\Cache;

class ImportacionDatos extends Page
{

    use HasPagePermissions;

    protected static ?string $viewPermission =
        'ver_importacion_datos';



    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Importación de Datos';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.pages.importacion-datos';

    protected static ?string $title = 'Importación de Datos';

    public $archivoEstudiantes = null;

    public ?int $sedeId = null;

    public ?int $periodoLectivoId = null;

    public array $erroresEstudiantes = [];

    public ?array $resultadoEstudiantes = null;


    public bool $mostrarModalErroresEstudiantes = false;


    public $archivoPensum = null;

    public array $erroresPensum = [];

    public ?array $resultadoPensum = null;

    public bool $mostrarModalErroresPensum = false;

    public $archivoDocentes = null;

    public array $erroresDocentes = [];

    public ?array $resultadoDocentes = null;

    public bool $mostrarModalErroresDocentes = false;



    public $archivoAsignaciones = null;

    public array $erroresAsignaciones = [];

    public ?array $resultadoAsignaciones = null;

    public bool $mostrarModalErroresAsignaciones = false;


    public $archivoAcudientes = null;

    public array $erroresAcudientes = [];

    public ?array $resultadoAcudientes = null;

    public bool $mostrarModalErroresAcudientes = false;

    public string $buscarImportador = '';








    public function mount(): void
    {
        $this->sedeId = session('sede_id');

        $this->periodoLectivoId = PeriodoLectivo::query()
            ->where('sede_id', session('sede_id'))
            ->where('estado', 'abierto')
            ->value('id');
    }

    private function guardarHistorialImportacion(string $clave, array $resultado): void
    {
        Cache::forever("importacion_datos_{$clave}", $resultado);
    }

    public function historialImportacion(string $clave): ?array
    {
        return Cache::get("importacion_datos_{$clave}");
    }

    public function importarEstudiantes(): void
    {
        if (!$this->archivoEstudiantes) {

            Notification::make()
                ->title('Seleccione un archivo Excel.')
                ->warning()
                ->send();

            return;
        }

        $service = new EstudiantesImportService();

        $resultado = $service->importar(
            $this->archivoEstudiantes->getRealPath(),
            [
                'sede_id' => $this->sedeId,
                'periodo_lectivo_id' => $this->periodoLectivoId,
            ]
        );

        $this->resultadoEstudiantes = $resultado->toArray();
        $this->erroresEstudiantes = $resultado->errores;
        $this->mostrarModalErroresEstudiantes = $resultado->tieneErrores();
        $this->guardarHistorialImportacion('estudiantes', $this->resultadoEstudiantes);

        Notification::make()
            ->title($resultado->tieneErrores() ? 'Importación finalizada con inconsistencias' : 'Importación completada')
            ->body(
                'Filas leídas: '.$resultado->filasLeidas.
                ' | Importados: '.$resultado->totalImportados().
                ' | Inconsistencias: '.$resultado->totalErrores()
            )
            ->{$resultado->tieneErrores() ? 'warning' : 'success'}()
            ->send();
    }


    public function importarPensum(): void
    {
        if (! $this->archivoPensum) {
            Notification::make()
                ->title('Seleccione un archivo Excel.')
                ->warning()
                ->send();

            return;
        }

        if (! $this->sedeId || ! $this->periodoLectivoId) {
            Notification::make()
                ->title('Filtros incompletos')
                ->body('Seleccione sede y periodo lectivo antes de importar.')
                ->warning()
                ->send();

            return;
        }

        $service = new PensumImportService();

        $resultado = $service->importar(
            $this->archivoPensum->getRealPath(),
            [
                'sede_id' => $this->sedeId,
                'periodo_lectivo_id' => $this->periodoLectivoId,
            ]
        );

        $this->resultadoPensum = $resultado->toArray();
        $this->erroresPensum = $resultado->errores;
        $this->mostrarModalErroresPensum = $resultado->tieneErrores();
        $this->guardarHistorialImportacion('pensum', $this->resultadoPensum);

        Notification::make()
            ->title($resultado->tieneErrores() ? 'Importación finalizada con inconsistencias' : 'Importación completada')
            ->body(
                'Filas leídas: '.$resultado->filasLeidas.
                ' | Importados: '.$resultado->totalImportados().
                ' | Inconsistencias: '.$resultado->totalErrores()
            )
            ->{$resultado->tieneErrores() ? 'warning' : 'success'}()
            ->send();
    }


    public function importarDocentes(): void
    {
        if (! $this->archivoDocentes) {
            Notification::make()
                ->title('Seleccione un archivo Excel.')
                ->warning()
                ->send();

            return;
        }

        if (! $this->sedeId || ! $this->periodoLectivoId) {
            Notification::make()
                ->title('Filtros incompletos')
                ->body('Seleccione sede y periodo lectivo antes de importar.')
                ->warning()
                ->send();

            return;
        }

        $service = new DocentesImportService();

        $resultado = $service->importar(
            $this->archivoDocentes->getRealPath(),
            [
                'sede_id' => $this->sedeId,
                'periodo_lectivo_id' => $this->periodoLectivoId,
            ]
        );

        $this->resultadoDocentes = $resultado->toArray();
        $this->erroresDocentes = $resultado->errores;
        $this->mostrarModalErroresDocentes = $resultado->tieneErrores();
        $this->guardarHistorialImportacion('docentes', $this->resultadoDocentes);

        Notification::make()
            ->title($resultado->tieneErrores() ? 'Importación finalizada con inconsistencias' : 'Importación completada')
            ->body(
                'Filas leídas: '.$resultado->filasLeidas.
                ' | Importados: '.$resultado->totalImportados().
                ' | Inconsistencias: '.$resultado->totalErrores()
            )
            ->{$resultado->tieneErrores() ? 'warning' : 'success'}()
            ->send();
    }


    public function importarAsignaciones(): void
    {
        if (! $this->archivoAsignaciones) {
            Notification::make()
                ->title('Seleccione un archivo Excel.')
                ->warning()
                ->send();

            return;
        }

        if (! $this->sedeId || ! $this->periodoLectivoId) {
            Notification::make()
                ->title('Filtros incompletos')
                ->body('Seleccione sede y periodo lectivo antes de importar.')
                ->warning()
                ->send();

            return;
        }

        $service = new AsignacionesImportService();

        $resultado = $service->importar(
            $this->archivoAsignaciones->getRealPath(),
            [
                'sede_id' => $this->sedeId,
                'periodo_lectivo_id' => $this->periodoLectivoId,
            ]
        );

        $this->resultadoAsignaciones = $resultado->toArray();
        $this->erroresAsignaciones = $resultado->errores;
        $this->mostrarModalErroresAsignaciones = $resultado->tieneErrores();
        $this->guardarHistorialImportacion('asignaciones', $this->resultadoAsignaciones);

        Notification::make()
            ->title($resultado->tieneErrores() ? 'Importación finalizada con inconsistencias' : 'Importación completada')
            ->body(
                'Filas leídas: '.$resultado->filasLeidas.
                ' | Importados: '.$resultado->totalImportados().
                ' | Inconsistencias: '.$resultado->totalErrores()
            )
            ->{$resultado->tieneErrores() ? 'warning' : 'success'}()
            ->send();
    }


    public function importarAcudientes(): void
    {
        if (! $this->archivoAcudientes) {
            Notification::make()
                ->title('Seleccione un archivo Excel.')
                ->warning()
                ->send();

            return;
        }

        if (! $this->sedeId || ! $this->periodoLectivoId) {
            Notification::make()
                ->title('Filtros incompletos')
                ->body('Seleccione sede y periodo lectivo antes de importar.')
                ->warning()
                ->send();

            return;
        }

        $service = new AcudientesImportService();

        $resultado = $service->importar(
            $this->archivoAcudientes->getRealPath(),
            [
                'sede_id' => $this->sedeId,
                'periodo_lectivo_id' => $this->periodoLectivoId,
            ]
        );

        $this->resultadoAcudientes = $resultado->toArray();
        $this->erroresAcudientes = $resultado->errores;
        $this->mostrarModalErroresAcudientes = $resultado->tieneErrores();
        $this->guardarHistorialImportacion('acudientes', $this->resultadoAcudientes);

        Notification::make()
            ->title($resultado->tieneErrores() ? 'Importación finalizada con inconsistencias' : 'Importación completada')
            ->body(
                'Filas leídas: '.$resultado->filasLeidas.
                ' | Importados: '.$resultado->totalImportados().
                ' | Inconsistencias: '.$resultado->totalErrores()
            )
            ->{$resultado->tieneErrores() ? 'warning' : 'success'}()
            ->send();
    }



    public function getSedesProperty()
    {
        return Sede::orderBy('nombre')->pluck('nombre','id');
    }

    public function getPeriodosProperty()
    {
        return PeriodoLectivo::query()
            ->where('sede_id',$this->sedeId)
            ->orderByDesc('nombre')
            ->pluck('nombre','id');
    }


}