<?php

namespace App\Filament\Resources\AsignacionConceptoResource\Pages;

use App\Filament\Resources\AsignacionConceptoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAsignacionConceptos extends ListRecords
{
    protected static string $resource = AsignacionConceptoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
