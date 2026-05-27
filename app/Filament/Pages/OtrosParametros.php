<?php

namespace App\Filament\Pages;

use App\Models\RangoDesempenoNota;
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

    public function mount(): void
    {
        $this->cargarRangos();
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

            RangoDesempenoNota::where('id', $rango['id'])->update([
                'nombre' => trim($rango['nombre']),
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
}