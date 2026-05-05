<?php

namespace App\Filament\Resources\PeriodoLectivoResource\Pages;

use App\Filament\Resources\PeriodoLectivoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeriodoLectivos extends ListRecords
{
    protected static string $resource = PeriodoLectivoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
