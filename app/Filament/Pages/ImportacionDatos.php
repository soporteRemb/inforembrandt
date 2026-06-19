<?php

namespace App\Filament\Pages;

use App\Models\PeriodoLectivo;
use App\Models\Sede;
use App\Services\Importacion\DTO\ResultadoImportacion;
use App\Services\Importacion\Estudiantes\EstudiantesImportService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class ImportacionDatos extends Page
{
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

    public function mount(): void
    {
        $this->sedeId = session('sede_id');

        $this->periodoLectivoId = PeriodoLectivo::query()
            ->where('sede_id', session('sede_id'))
            ->where('estado', 'abierto')
            ->value('id');
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