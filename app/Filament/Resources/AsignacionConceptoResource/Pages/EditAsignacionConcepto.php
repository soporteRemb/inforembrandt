<?php

namespace App\Filament\Resources\AsignacionConceptoResource\Pages;

use App\Filament\Resources\AsignacionConceptoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsignacionConcepto extends EditRecord
{
    protected static string $resource = AsignacionConceptoResource::class;

    protected static ?string $title = 'Asignación de conceptos de cobro';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
