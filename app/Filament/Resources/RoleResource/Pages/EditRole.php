<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Permission;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return []; // Sin botón de borrar
    }

    /** Precarga las checkboxes con los permisos actuales del rol */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = array_merge($data, RoleResource::permFieldsFromRecord($this->record));
        return $data;
    }

    /** Al guardar: sincroniza los permisos y actualiza el nombre si cambió */
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Actualizar nombre del rol (solo si no es superadmin/admin)
        if (!in_array($record->name, ['superadmin', 'admin']) && isset($data['name'])) {
            $record->update(['name' => $data['name']]);
        }

        // Sincronizar permisos
        $permIds = RoleResource::collectPermIds($data);
        $perms   = Permission::whereIn('id', $permIds)->get();
        $record->syncPermissions($perms);

        return $record;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Permisos actualizados')
            ->body('Los permisos del rol han sido guardados correctamente.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
