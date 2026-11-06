<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected bool $reactivacionTemporalSolicitada = false;

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
        | Validar reactivación del acceso temporal
        |--------------------------------------------------------------------------
        |
        | Se permite reactivar un formulario completado o vencido,
        | siempre que el administrador establezca una fecha futura.
        |
        */

        $seEstaActivando =
            (bool) ($this->data['is_active'] ?? false)
            && ! (bool) $this->record->is_active;
        $this->reactivacionTemporalSolicitada = $seEstaActivando;

        $preMatriculaActual = \App\Models\PreMatricula::query()
            ->where('user_id', $this->record->id)
            ->latest('id')
            ->first();
        if (
            $preMatriculaActual
            && (int) $preMatriculaActual->sede_id !== (int) $sedeIds->first()
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'data.sedes' =>
                    'Este usuario ya tiene una pre-matrícula asociada a otra sede. No es posible cambiar su sede desde esta edición.',
            ]);
        }

        if (! $seEstaActivando) {
            return;
        }

        if (
            $preMatriculaActual
            && in_array(
                $preMatriculaActual->estado,
                ['completado', 'vencido'],
                true
            )
        ) {
            $fechaVencimiento = $this->data['expires_at'] ?? null;

            if (
                blank($fechaVencimiento)
                || \Carbon\Carbon::parse($fechaVencimiento)->startOfDay()->lte(today())
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'data.expires_at' =>
                        'Debe seleccionar una fecha futura para reactivar el formulario.',
                ]);
            }
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
        /*
        |--------------------------------------------------------------------------
        | Buscar primero el formulario existente del usuario
        |--------------------------------------------------------------------------
        |
        | Se conserva su identificador, sede y período lectivo.
        | Solo se crea un formulario cuando el usuario no tiene ninguno.
        |
        */

        $preMatriculaExistente = \App\Models\PreMatricula::query()
            ->where('user_id', $this->record->id)
            ->latest('id')
            ->first();

        if ($preMatriculaExistente) {

            /*
            |--------------------------------------------------------------------------
            | Evitar asociar el formulario a otra sede
            |--------------------------------------------------------------------------
            */

            if ((int) $preMatriculaExistente->sede_id !== (int) $sedeId) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'data.sedes' =>
                        'Este usuario ya tiene una pre-matrícula asociada a otra sede.',
                ]);
            }

            $administrador = auth()->user();

            if (! $administrador) {
                throw new \RuntimeException(
                    'No se pudo identificar al administrador que modificó el acceso temporal.'
                );
            }

            $servicio = app(
                \App\Services\PreMatriculas\PreMatriculaService::class
            );

            /*
            |--------------------------------------------------------------------------
            | Reactivar formulario completado o vencido
            |--------------------------------------------------------------------------
            */

            if (
                $this->reactivacionTemporalSolicitada
                && (bool) $this->record->is_active
            ) {

                if ($preMatriculaExistente->estado === 'completado') {
                    $servicio->reactivarFormularioCompletado(
                        $preMatriculaExistente,
                        $administrador,
                        $this->record->expires_at
                    );

                    return;
                }

                if ($preMatriculaExistente->estado === 'vencido') {
                    $servicio->reactivarFormularioVencido(
                        $preMatriculaExistente,
                        $administrador,
                        $this->record->expires_at
                    );

                    return;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Sincronizar fecha límite del formulario pendiente
            |--------------------------------------------------------------------------
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
                $preMatriculaExistente->forceFill([
                    'fecha_limite' => $this->record->expires_at,
                    'actualizado_por' => $administrador->id,
                ])->save();
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Crear formulario únicamente si no existe uno anterior
        |--------------------------------------------------------------------------
        */

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