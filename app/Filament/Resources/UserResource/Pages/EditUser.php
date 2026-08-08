<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function afterSave(): void
    {
        $tipoUsuario = UserResource::tipoUsuarioDesdeRoles(
            $this->data['roles'] ?? []
        );

        if (
            $tipoUsuario
            && $this->record->tipo_usuario !== $tipoUsuario
        ) {
            $this->record->forceFill([
                'tipo_usuario' => $tipoUsuario,
            ])->saveQuietly();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}