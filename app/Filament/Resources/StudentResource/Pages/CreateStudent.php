<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function afterCreate(): void
    {
        $tipos  = ['padre', 'madre', 'acudiente', 'deudor_economico'];
        $fields = ['nombre', 'telefono', 'tipo_documento', 'documento', 'lugar_trabajo', 'correo', 'direccion'];

        foreach ($tipos as $tipo) {
            $guardianData = [];
            foreach ($fields as $field) {
                $guardianData[$field] = $this->data["g_{$tipo}_{$field}"] ?? null;
            }

            if (!empty($guardianData['nombre'])) {
                $this->record->guardians()->create(
                    array_merge($guardianData, ['tipo' => $tipo])
                );
            }
        }
    }
}
