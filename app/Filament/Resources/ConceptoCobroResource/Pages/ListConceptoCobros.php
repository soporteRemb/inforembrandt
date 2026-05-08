<?php

namespace App\Filament\Resources\ConceptoCobroResource\Pages;

use App\Filament\Resources\ConceptoCobroResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConceptoCobros extends ListRecords
{
    protected static string $resource = ConceptoCobroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
