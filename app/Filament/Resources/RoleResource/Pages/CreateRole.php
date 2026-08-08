<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $rol = static::getModel()::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $idsPermisos = RoleResource::collectPermIds($data);

        $permisos = Permission::query()
            ->whereIn('id', $idsPermisos)
            ->get();

        $rol->syncPermissions($permisos);

        return $rol;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Rol creado')
            ->body('El rol y sus permisos fueron guardados correctamente.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}