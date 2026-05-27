<?php

namespace App\Filament\Resources\PensumAcademicoResource\Pages;

use App\Filament\Resources\PensumAcademicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPensumAcademico extends EditRecord
{
    protected static string $resource = PensumAcademicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
