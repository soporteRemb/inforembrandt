<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // ── Carga los datos de acudientes al abrir el formulario ──────────────────
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $tipos  = ['padre', 'madre', 'acudiente', 'deudor_economico'];
        $fields = ['nombre', 'telefono', 'tipo_documento', 'documento', 'lugar_trabajo', 'correo', 'direccion'];

        foreach ($tipos as $tipo) {
            $guardian = $this->record->guardians()->where('tipo', $tipo)->first();
            foreach ($fields as $field) {
                $data["g_{$tipo}_{$field}"] = $guardian?->{$field} ?? '';
            }
        }

        return $data;
    }

    // ── Guarda los acudientes al guardar el formulario ────────────────────────
    protected function afterSave(): void
    {
        $this->saveGuardians();
    }

    private function saveGuardians(): void
    {
        $tipos  = ['padre', 'madre', 'acudiente', 'deudor_economico'];
        $fields = ['nombre', 'telefono', 'tipo_documento', 'documento', 'lugar_trabajo', 'correo', 'direccion'];

        foreach ($tipos as $tipo) {
            $guardianData = [];
            foreach ($fields as $field) {
                $guardianData[$field] = $this->data["g_{$tipo}_{$field}"] ?? null;
            }

            if (!empty($guardianData['nombre'])) {
                $this->record->guardians()->updateOrCreate(
                    ['tipo' => $tipo],
                    array_merge($guardianData, ['tipo' => $tipo])
                );
            }
        }
    }

    // ── Método Livewire: marcar documento como entregado (sin PDF) ───────────
    public function generarDocumento(string $tipo): void
    {
        $this->record->documentos()->updateOrCreate(
            ['tipo' => $tipo],
            [
                'generado_at'  => now(),
                'generado_por' => Auth::user()->name ?? 'Administrador',
            ]
        );

        // Refresca el formulario para que la sección de pendientes se actualice
        $this->fillForm();
    }
}
