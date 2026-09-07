<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeSave(): void
    {
        $tipoUsuario = UserResource::tipoUsuarioDesdeRoles(
            $this->data['roles'] ?? []
        );

        if ($tipoUsuario !== 'temporal') {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | El usuario temporal debe pertenecer a una sola sede
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | Un formulario enviado no puede reactivarse
        |--------------------------------------------------------------------------
        */
        $seEstaActivando =
            (bool) ($this->data['is_active'] ?? false)
            && ! (bool) $this->record->is_active;

        if (! $seEstaActivando) {
            return;
        }

        $preMatriculaActual = \App\Models\PreMatricula::query()
            ->where('user_id', $this->record->id)
            ->latest('id')
            ->first();

        if (
            $preMatriculaActual
            && $preMatriculaActual->estado === 'completado'
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'data.is_active' =>
                    'No es posible reactivar este usuario porque su formulario de pre-matrícula ya fue enviado.',
            ]);
        }
    }
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

        if ($tipoUsuario !== 'temporal') {
            return;
        }

        $sedeIds = collect($this->data['sedes'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

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

        $preMatriculaExistente = \App\Models\PreMatricula::query()
            ->where('user_id', $this->record->id)
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodoLectivo->id)
            ->latest('id')
            ->first();

        if ($preMatriculaExistente) {
            if (
                $preMatriculaExistente->estado === 'vencido'
                && (bool) $this->record->is_active
            ) {
                $administrador = auth()->user();

                if (! $administrador) {
                    throw new \RuntimeException(
                        'No se pudo identificar al usuario que reactivó el acceso temporal.'
                    );
                }

                app(
                    \App\Services\PreMatriculas\PreMatriculaService::class
                )->reactivarFormularioVencido(
                    $preMatriculaExistente,
                    $administrador,
                    $this->record->expires_at
                );
            }

            return;
        }

        $administrador = auth()->user();

        if (! $administrador) {
            throw new \RuntimeException(
                'No se pudo identificar al usuario que modificó el acceso temporal.'
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(
                    fn (): bool =>
                        $this->record
                        && UserResource::canDelete($this->record)
                ),
        ];
    }
}