<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

   
    protected function beforeCreate(): void
    {
        $tipoUsuario = UserResource::tipoUsuarioDesdeRoles(
            $this->data['roles'] ?? []
        );

        if ($tipoUsuario !== 'temporal') {
            return;
        }

        $sedeIds = collect($this->data['sedes'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($sedeIds->count() !== 1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'data.sedes' =>
                    'El usuario temporal debe tener exactamente una sede asignada.',
            ]);
        }

        $existePeriodoActual = \App\Models\PeriodoLectivo::query()
            ->where('sede_id', $sedeIds->first())
            ->where('nombre', (string) now()->year)
            ->exists();

        if (! $existePeriodoActual) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'data.sedes' =>
                    'La sede seleccionada no tiene configurado el período lectivo '
                    . now()->year . '.',
            ]);
        }
    }
   
    protected function afterCreate(): void
    {
        $tipoUsuario = UserResource::tipoUsuarioDesdeRoles(
            $this->data['roles'] ?? []
        );

        if ($tipoUsuario) {
            $this->record->forceFill([
                'tipo_usuario' => $tipoUsuario,
            ])->saveQuietly();
        }

        if ($tipoUsuario !== 'temporal') {
            return;
        }

        $sedeIds = collect($this->data['sedes'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($sedeIds->count() !== 1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'data.sedes' =>
                    'El usuario temporal debe tener exactamente una sede asignada.',
            ]);
        }

        $sedeId = $sedeIds->first();

        $periodoLectivo = \App\Models\PeriodoLectivo::query()
            ->where('sede_id', $sedeId)
            ->where('nombre', (string) now()->year)
            ->first();

        if (! $periodoLectivo) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'data.sedes' =>
                    'La sede seleccionada no tiene configurado el período lectivo '
                    . now()->year . '.',
            ]);
        }

        $administrador = auth()->user();

        if (! $administrador) {
            throw new \RuntimeException(
                'No se pudo identificar al usuario que creó el acceso temporal.'
            );
        }

        app(
            \App\Services\PreMatriculas\PreMatriculaService::class
        )->crearFormulario(
            usuarioTemporal: $this->record,
            sedeId: $sedeId,
            periodoLectivoId: $periodoLectivo->id,
            creadoPor: $administrador,
        );
    }

    

}