<?php

namespace App\Filament\Pages;

use App\Models\RangoDesempenoNota;
use App\Models\Eps;
use App\Models\Jornada;


use Filament\Notifications\Notification;
use Filament\Pages\Page;

class OtrosParametros extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Otros Parámetros';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 50;

    protected static string $view = 'filament.pages.otros-parametros';

    public string $buscarParametro = '';

    public array $rangos = [];

    public array $eps = [];
    public array $jornadas = [];

    public function mount(): void
    {
        $this->cargarRangos();
        $this->cargarEps();
        $this->cargarJornadas();
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

    
    
}