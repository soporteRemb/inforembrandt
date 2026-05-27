<?php

namespace App\Filament\Resources\PensumAcademicoResource\Pages;

use App\Filament\Resources\PensumAcademicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPensumAcademicos extends ListRecords
{
    protected static string $resource = PensumAcademicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
