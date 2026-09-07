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
        /*
        |--------------------------------------------------------------------------
        | Sincronizar roles seleccionados
        |--------------------------------------------------------------------------
        |
        | El formulario de usuarios administra los roles mediante Spatie.
        | Esta sincronización explícita garantiza que, al corregir manualmente
        | un rol (por ejemplo, de acudiente a temporal), el usuario conserve
        | exactamente los roles seleccionados y no quede desalineado con
        | tipo_usuario.
        |
        */

        $rolesSeleccionados = collect(
            $this->data['roles'] ?? []
        )
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $nombresRoles = \Spatie\Permission\Models\Role::query()
            ->whereIn('id', $rolesSeleccionados)
            ->pluck('name')
            ->all();

        $this->record->syncRoles(
            $nombresRoles
        );

        /*
        |--------------------------------------------------------------------------
        | Sincronizar tipo_usuario
        |--------------------------------------------------------------------------
        */

        $tipoUsuario = UserResource::tipoUsuarioDesdeRoles(
            $rolesSeleccionados
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
        /*
        |--------------------------------------------------------------------------
        | Reactivar formulario vencido
        |--------------------------------------------------------------------------
        */

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

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Sincronizar fecha límite del formulario pendiente
        |--------------------------------------------------------------------------
        |
        | Cuando se modifica la fecha de vencimiento del usuario temporal,
        | mantenemos sincronizada la fecha límite de su formulario pendiente.
        |
        | Los formularios completados no se modifican y los vencidos solamente
        | cambian su fecha mediante el proceso normal de reactivación.
        |
        */

        if (
            $preMatriculaExistente->estado === 'pendiente'
            && $this->record->expires_at
            && (
                ! $preMatriculaExistente->fecha_limite
                || ! $preMatriculaExistente->fecha_limite->equalTo(
                    $this->record->expires_at
                )
            )
        ) {
            $administrador = auth()->user();

            if (! $administrador) {
                throw new \RuntimeException(
                    'No se pudo identificar al usuario que modificó el acceso temporal.'
                );
            }

            $preMatriculaExistente->forceFill([
                'fecha_limite' => $this->record->expires_at,
                'actualizado_por' => $administrador->id,
            ])->save();
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
                )
                ->action(function (): void {
                    if ($this->record->preMatriculas()->exists()) {
                        \Filament\Notifications\Notification::make()
                            ->title('No se puede eliminar el usuario')
                            ->body(
                                'Este usuario tiene un formulario de pre-matrícula asociado.'
                            )
                            ->warning()
                            ->send();

                        return;
                    }

                    $this->record->delete();

                    \Filament\Notifications\Notification::make()
                        ->title('Usuario eliminado')
                        ->success()
                        ->send();

                    $this->redirect(
                        UserResource::getUrl('index')
                    );
                }),
        ];
    }
}