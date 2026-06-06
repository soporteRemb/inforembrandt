<?php

namespace App\Filament\Resources\PeriodoLectivoResource\Pages;

use App\Filament\Resources\PeriodoLectivoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPeriodoLectivo extends EditRecord
{
    protected static string $resource = PeriodoLectivoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        PeriodoLectivoResource::guardarPeriodosAcademicos(
            $this->record,
            $this->form->getRawState()
        );
    }
}