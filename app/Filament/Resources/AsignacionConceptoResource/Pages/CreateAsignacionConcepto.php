<?php

namespace App\Filament\Resources\AsignacionConceptoResource\Pages;

use App\Filament\Resources\AsignacionConceptoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAsignacionConcepto extends CreateRecord
{
    protected static string $resource = AsignacionConceptoResource::class;

    protected function getRedirectUrl(): string
    {
        return AsignacionConceptoResource::getUrl('edit', [
            'record' => $this->record,
        ]);
    }
}