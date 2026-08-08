<?php

namespace App\Filament\Pages;

use App\Models\RangoDesempenoNota;
use App\Models\Eps;
use App\Models\Jornada;
use App\Models\TipoLimiteExtemporaneo;
use App\Models\FormaPago;

use App\Traits\HasPagePermissions;


use Filament\Notifications\Notification;
use Filament\Pages\Page;

class OtrosParametros extends Page
{
    use HasPagePermissions;

    protected static ?string $viewPermission =
        'ver_otros_parametros';



    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Otros Parámetros';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 50;

    protected static string $view = 'filament.pages.otros-parametros';

    public string $buscarParametro = '';

    public array $rangos = [];

    public array $eps = [];
    public array $jornadas = [];
    public array $tiposLimite = [];
    public array $formasPago = [];


    public function mount(): void
    {
        $this->cargarRangos();
        $this->cargarEps();
        $this->cargarJornadas();
        $this->cargarTiposLimite();
        $this->cargarFormasPago();
    }

    public function cargarRangos(): void
    {
        if (RangoDesempenoNota::count() === 0) {
            RangoDesempenoNota::insert([
                ['nombre' => 'Bajo', 'desde' => 0, 'hasta' => 59, 'orden' => 1, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Básico', 'desde' => 60, 'hasta' => 79, 'orden' => 2, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Alto', 'desde' => 80, 'hasta' => 94, 'orden' => 3, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Superior', 'desde' => 95, 'hasta' => 100, 'orden' => 4, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $this->rangos = RangoDesempenoNota::query()
            ->orderBy('orden')
            ->get()
            ->map(fn ($rango) => [
                'id' => $rango->id,
                'nombre' => $rango->nombre,
                'desde' => $rango->desde,
                'hasta' => $rango->hasta,
                'activo' => (bool) $rango->activo,
            ])
            ->toArray();

        $this->rangos = RangoDesempenoNota::query()
            ->orderBy('orden')
            ->get()
            ->map(fn ($rango) => [
                'id' => $rango->id,
                'nombre' => $rango->nombre,
                'convencion' => $rango->convencion,
                'descripcion_convencion' => $rango->descripcion_convencion,
                'desde' => $rango->desde,
                'hasta' => $rango->hasta,
                'activo' => (bool) $rango->activo,
            ])
            ->toArray();
    }

    public function guardarRangos(): void
{
    foreach ($this->rangos as $rango) {

            if (empty(trim($rango['nombre']))) {
                Notification::make()
                    ->title('Todos los rangos deben tener nombre')
                    ->danger()
                    ->send();

                return;
            }

            if (mb_strlen(trim($rango['descripcion_convencion'] ?? '')) > 70) {
                Notification::make()
                    ->title('La descripción de la convención no puede superar los 70 caracteres.')
                    ->danger()
                    ->send();

                return;
            }
            
            RangoDesempenoNota::where('id', $rango['id'])->update([
                'nombre' => trim($rango['nombre']),
                'convencion' => trim($rango['convencion']),
                'descripcion_convencion' => trim($rango['descripcion_convencion']),
                'desde' => $rango['desde'],
                'hasta' => $rango['hasta'],
                'activo' => $rango['activo'] ?? true,
            ]);
        }

        $this->cargarRangos();

        Notification::make()
            ->title('Rangos guardados correctamente')
            ->success()
            ->send();
    }

    public function cargarEps(): void
    {
        $this->eps = Eps::query()
            ->orderBy('nombre')
            ->get()
            ->map(fn ($eps) => [
                'id' => $eps->id,
                'nombre' => $eps->nombre,
                'activo' => (bool) $eps->activo,
            ])
            ->toArray();
    }

    public function agregarEps(): void
    {
        $this->eps[] = [
            'id' => null,
            'nombre' => '',
            'activo' => true,
        ];
    }

    public function guardarEps(): void
    {
        foreach ($this->eps as $item) {
            $nombre = trim($item['nombre'] ?? '');

            if ($nombre === '') {
                continue;
            }

            Eps::updateOrCreate(
                ['id' => $item['id'] ?? null],
                [
                    'nombre' => $nombre,
                    'activo' => (bool) ($item['activo'] ?? true),
                ]
            );
        }

        $this->cargarEps();

        Notification::make()
            ->title('EPS guardadas correctamente')
            ->success()
            ->send();
    }

    public function cargarJornadas(): void
    {
        if (Jornada::count() === 0) {
            Jornada::create([
                'nombre' => 'Completa',
                'activo' => true,
            ]);
        }

        $this->jornadas = Jornada::query()
            ->orderBy('nombre')
            ->get()
            ->map(fn ($jornada) => [
                'id' => $jornada->id,
                'nombre' => $jornada->nombre,
                'activo' => (bool) $jornada->activo,
            ])
            ->toArray();
    }

    public function agregarJornada(): void
    {
        $this->jornadas[] = [
            'id' => null,
            'nombre' => '',
            'activo' => true,
        ];
    }

    public function guardarJornadas(): void
    {
        foreach ($this->jornadas as $item) {
            $nombre = trim($item['nombre'] ?? '');

            if ($nombre === '') {
                continue;
            }

            Jornada::updateOrCreate(
                ['id' => $item['id'] ?? null],
                [
                    'nombre' => $nombre,
                    'activo' => (bool) ($item['activo'] ?? true),
                ]
            );
        }

        $this->cargarJornadas();

        Notification::make()
            ->title('Jornadas guardadas correctamente')
            ->success()
            ->send();
    }

    public function eliminarEps(int $index): void
    {
        if (!empty($this->eps[$index]['id'])) {
            Eps::where('id', $this->eps[$index]['id'])->delete();
        }

        unset($this->eps[$index]);

        $this->eps = array_values($this->eps);
    }

    public function eliminarJornada(int $index): void
    {
        if (!empty($this->jornadas[$index]['id'])) {
            Jornada::where('id', $this->jornadas[$index]['id'])->delete();
        }

        unset($this->jornadas[$index]);

        $this->jornadas = array_values($this->jornadas);
    }

    
    public function cargarTiposLimite(): void
    {
        if (TipoLimiteExtemporaneo::count() === 0) {
            TipoLimiteExtemporaneo::insert([
                ['codigo' => 'Limite 1', 'nombre' => '30 días', 'orden' => 1, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['codigo' => 'Limite 2', 'nombre' => '60 días', 'orden' => 2, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['codigo' => 'Limite 3', 'nombre' => '90 días', 'orden' => 3, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $this->tiposLimite = TipoLimiteExtemporaneo::query()
            ->orderBy('orden')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'codigo' => $item->codigo,
                'nombre' => $item->nombre,
                'orden' => $item->orden,
                'activo' => (bool) $item->activo,
            ])
            ->toArray();
    }

    public function agregarTipoLimite(): void
    {
        $siguienteOrden = count($this->tiposLimite) + 1;

        $this->tiposLimite[] = [
            'id' => null,
            'codigo' => 'Limite ' . $siguienteOrden,
            'nombre' => '',
            'orden' => $siguienteOrden,
            'activo' => true,
        ];
    }

    public function guardarTiposLimite(): void
    {
        foreach ($this->tiposLimite as $index => $item) {
            $codigo = trim($item['codigo'] ?? '');
            $nombre = trim($item['nombre'] ?? '');

            if ($codigo === '' || $nombre === '') {
                continue;
            }

            TipoLimiteExtemporaneo::updateOrCreate(
                ['id' => $item['id'] ?? null],
                [
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'orden' => $index + 1,
                    'activo' => (bool) ($item['activo'] ?? true),
                ]
            );
        }

        $this->cargarTiposLimite();

        Notification::make()
            ->title('Límites extemporáneos guardados correctamente')
            ->success()
            ->send();
    }

    public function eliminarTipoLimite(int $index): void
    {
        if (!empty($this->tiposLimite[$index]['id'])) {
            TipoLimiteExtemporaneo::where('id', $this->tiposLimite[$index]['id'])->delete();
        }

        unset($this->tiposLimite[$index]);

        $this->tiposLimite = array_values($this->tiposLimite);
    }


    public function cargarFormasPago(): void
    {
        if (FormaPago::count() === 0) {
            FormaPago::insert([
                ['nombre' => 'Efectivo', 'requiere_referencia' => false, 'requiere_fecha_consignacion' => false, 'orden' => 1, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Transferencia', 'requiere_referencia' => true, 'requiere_fecha_consignacion' => true, 'orden' => 2, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Consignación', 'requiere_referencia' => true, 'requiere_fecha_consignacion' => true, 'orden' => 3, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Nequi', 'requiere_referencia' => true, 'requiere_fecha_consignacion' => false, 'orden' => 4, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Daviplata', 'requiere_referencia' => true, 'requiere_fecha_consignacion' => false, 'orden' => 5, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $this->formasPago = FormaPago::query()
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get()
            ->map(fn (FormaPago $forma) => [
                'id' => $forma->id,
                'nombre' => $forma->nombre,
                'requiere_referencia' => (bool) $forma->requiere_referencia,
                'requiere_fecha_consignacion' => (bool) $forma->requiere_fecha_consignacion,
                'orden' => $forma->orden,
                'activo' => (bool) $forma->activo,
            ])
            ->toArray();
    }

    public function agregarFormaPago(): void
    {
        $this->formasPago[] = [
            'id' => null,
            'nombre' => '',
            'requiere_referencia' => false,
            'requiere_fecha_consignacion' => false,
            'orden' => count($this->formasPago) + 1,
            'activo' => true,
        ];
    }

    public function guardarFormasPago(): void
    {
        $nombres = [];

        foreach ($this->formasPago as $index => $item) {
            $nombre = trim($item['nombre'] ?? '');

            if ($nombre === '') {
                continue;
            }

            $clave = mb_strtolower($nombre);
            if (in_array($clave, $nombres, true)) {
                Notification::make()->title('No puede repetir una forma de pago.')->danger()->send();
                return;
            }
            $nombres[] = $clave;

            FormaPago::updateOrCreate(
                ['id' => $item['id'] ?? null],
                [
                    'nombre' => $nombre,
                    'requiere_referencia' => (bool) ($item['requiere_referencia'] ?? false),
                    'requiere_fecha_consignacion' => (bool) ($item['requiere_fecha_consignacion'] ?? false),
                    'orden' => $index + 1,
                    'activo' => (bool) ($item['activo'] ?? true),
                ]
            );
        }

        $this->cargarFormasPago();
        Notification::make()->title('Formas de pago guardadas correctamente')->success()->send();
    }

    public function eliminarFormaPago(int $index): void
    {
        $id = $this->formasPago[$index]['id'] ?? null;

        if ($id) {
            $forma = FormaPago::find($id);
            if ($forma?->recibosFormasPago()->exists()) {
                Notification::make()
                    ->title('La forma de pago ya tiene movimientos y no puede eliminarse.')
                    ->body('Puedes desactivarla para que no aparezca en nuevos pagos.')
                    ->warning()
                    ->send();
                return;
            }
            $forma?->delete();
        }

        unset($this->formasPago[$index]);
        $this->formasPago = array_values($this->formasPago);
    }

}