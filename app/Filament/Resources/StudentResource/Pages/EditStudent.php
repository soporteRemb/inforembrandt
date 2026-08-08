<?php

namespace App\Filament\Resources\StudentResource\Pages;


use App\Models\Guardian;


use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\StudentResource;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $tipos  = ['padre', 'madre', 'acudiente', 'deudor_economico'];
        $fields = [
            'user_id',
            'nombre',
            'telefono',
            'tipo_documento',
            'documento',
            'lugar_trabajo',
            'correo',
            'direccion',
        ];

        foreach ($tipos as $tipo) {
            /*
            * Primero busca mediante la relación nueva guardian_student.
            */
            $guardian = $this->record->guardians()
                ->wherePivot('tipo', $tipo)
                ->first();

            /*
            * Compatibilidad con acudientes antiguos asociados
            * mediante guardians.student_id y guardians.tipo.
            */
            if (!$guardian) {
                $guardian = Guardian::query()
                    ->where('student_id', $this->record->id)
                    ->where('tipo', $tipo)
                    ->where(function ($query) {
                        $query
                            ->whereNull('estado')
                            ->orWhere('estado', 'activo');
                    })
                    ->first();
            }

            foreach ($fields as $field) {
                $data["g_{$tipo}_{$field}"] = $guardian?->{$field} ?? null;
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->saveGuardians();
    }

    private function saveGuardians(): void
    {
        $tipos  = ['padre', 'madre', 'acudiente', 'deudor_economico'];
        $fields = [
            'user_id',
            'nombre',
            'telefono',
            'tipo_documento',
            'documento',
            'lugar_trabajo',
            'correo',
            'direccion',
        ];

        foreach ($tipos as $tipo) {
            $guardianData = [];

            foreach ($fields as $field) {
                $guardianData[$field] = $this->data["g_{$tipo}_{$field}"] ?? null;
            }

            if (empty($guardianData['nombre'])) {
                continue;
            }

            $documento = trim((string) ($guardianData['documento'] ?? ''));

            if ($documento === '') {
                $documento = 'TEMP-' . strtoupper($tipo) . '-' . $this->record->id;
                $guardianData['documento'] = $documento;
            }

            $guardian = Guardian::updateOrCreate(
                ['documento' => $documento],
                array_merge($guardianData, [
                    'student_id' => $this->record->id, // compatibilidad temporal
                    'tipo' => $tipo,                  // compatibilidad temporal
                    'estado' => 'activo',
                ])
            );

            \DB::table('guardian_student')->updateOrInsert(
                [
                    'guardian_id' => $guardian->id,
                    'student_id' => $this->record->id,
                    'tipo' => $tipo,
                ],
                [
                    'estado' => 'activo',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function generarDocumento(string $tipo): void
    {
        $this->record->documentos()->updateOrCreate(
            ['tipo' => $tipo],
            [
                'generado_at'  => now(),
                'generado_por' => Auth::user()->name ?? 'Administrador',
            ]
        );

        $this->fillForm();
    }
}