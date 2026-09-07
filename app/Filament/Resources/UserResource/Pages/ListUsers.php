<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\PeriodoLectivo;
use App\Models\Sede;
use App\Services\PreMatriculas\GenerarUsuariosPreMatriculaService;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\HtmlString;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('crearUsuariosPreMatricula')
                ->label('Crear usuarios temporales - Estudiantes antiguos')
                ->icon('heroicon-o-user-group')
                ->color('gray')
                ->extraAttributes([
                    'style' => '
                        background-color: #dcfce7 !important;
                        color: #166534 !important;
                        box-shadow: inset 0 0 0 1px #bbf7d0 !important;
                    ',
                ])

                /*
                |--------------------------------------------------------------------------
                | Modal de advertencia
                |--------------------------------------------------------------------------
                */
                ->modalHeading('Creación masiva de usuarios temporales')
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->modalIconColor('success')
                ->modalAlignment(Alignment::Center)
                ->modalWidth('lg')
                ->modalDescription(new HtmlString(
                    '
                    <div style="
                        text-align: left;
                        line-height: 1.6;
                        max-width: 520px;
                        margin: 0 auto;
                    ">
                        <p>
                            Esta función permite crear de forma masiva los usuarios
                            temporales para que los <strong>estudiantes antiguos</strong>
                            diligencien el formulario de pre-matrícula.
                        </p>

                        <p style="margin-top: 12px;">
                            Los estudiantes serán tomados del periodo lectivo anterior
                            al periodo que seleccione en el siguiente paso.
                        </p>

                        <p style="margin-top: 12px;">
                            <strong>
                                Esta opción es únicamente para estudiantes antiguos.
                            </strong>
                            Para estudiantes nuevos, continúe utilizando la creación
                            individual de usuarios.
                        </p>
                    </div>
                    '
                ))

                /*
                |--------------------------------------------------------------------------
                | No usamos el botón de envío normal
                |--------------------------------------------------------------------------
                |
                | "Continuar" será una acción anidada que abre el segundo modal.
                |
                */
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cancelar')

                ->extraModalFooterActions([
                    /*
                    |--------------------------------------------------------------------------
                    | Segundo modal: configuración
                    |--------------------------------------------------------------------------
                    */
                    Actions\Action::make('configurarUsuariosPreMatricula')
                        ->label('Continuar')
                        ->icon('heroicon-o-arrow-right')
                        ->color('success')
                        ->cancelParentActions()
                        ->modalHeading(
                            'Crear usuarios temporales - Estudiantes antiguos'
                        )
                        ->modalDescription(
                            'Seleccione la sede, el periodo lectivo para el cual se realizará el proceso de pre-matrícula y la fecha de vencimiento de los accesos temporales.'
                        )
                        ->modalSubmitActionLabel('Crear usuarios')
                        ->modalCancelActionLabel('Cancelar')
                        ->modalWidth('lg')
                        ->form([
                            Select::make('sede_id')
                                ->label('Sede')
                                ->options(
                                    Sede::query()
                                        ->orderBy('nombre')
                                        ->pluck('nombre', 'id')
                                        ->toArray()
                                )
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->afterStateUpdated(
                                    function (Set $set): void {
                                        $set('periodo_lectivo_id', null);
                                    }
                                ),

                            Select::make('periodo_lectivo_id')
                                ->label('Periodo lectivo')
                                ->options(function (Get $get): array {
                                    $sedeId = $get('sede_id');

                                    if (! $sedeId) {
                                        return [];
                                    }

                                    return PeriodoLectivo::query()
                                        ->where('sede_id', $sedeId)
                                        ->orderByDesc('nombre')
                                        ->pluck('nombre', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->disabled(
                                    fn (Get $get): bool =>
                                        blank($get('sede_id'))
                                ),

                            DatePicker::make('fecha_vencimiento')
                                ->label('Fecha de vencimiento')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->closeOnDateSelection()
                                ->minDate(today())
                                ->required(),
                        ])
                        ->action(function (array $data): void {
                            set_time_limit(300);

                            $administrador = auth()->user();

                            if (! $administrador) {
                                Notification::make()
                                    ->title('No fue posible realizar el proceso')
                                    ->body(
                                        'No se pudo identificar al usuario administrador.'
                                    )
                                    ->danger()
                                    ->send();

                                return;
                            }

                            try {
                                $resultado = app(
                                    GenerarUsuariosPreMatriculaService::class
                                )->prepararMasivamente(
                                    sedeId: (int) $data['sede_id'],
                                    periodoDestinoId:
                                        (int) $data['periodo_lectivo_id'],
                                    fechaVencimiento:
                                        (string) $data['fecha_vencimiento'],
                                    usuarioAdministrador: $administrador,
                                );

                                $mensaje = implode('<br>', [
                                    '<strong>Proceso completado.</strong>',
                                    '',
                                    'Estudiantes encontrados: '
                                        . $resultado['total_estudiantes'],
                                    'Usuarios creados: '
                                        . $resultado['usuarios_creados'],
                                    'Usuarios reutilizados: '
                                        . $resultado['usuarios_reutilizados'],
                                    'Pre-matrículas creadas: '
                                        . $resultado['pre_matriculas_creadas'],
                                    'Pre-matrículas omitidas: '
                                        . $resultado['pre_matriculas_omitidas'],
                                    'Documentos encontrados: '
                                        . $resultado['documentos_encontrados'],
                                    'Documentos copiados: '
                                        . $resultado['documentos_copiados'],
                                    'Documentos ya existentes: '
                                        . $resultado['documentos_ya_existentes'],
                                    'Sin documento: '
                                        . $resultado['sin_documento'],
                                    'Conflictos: '
                                        . $resultado['conflictos'],
                                    'Errores: '
                                        . $resultado['errores'],
                                ]);

                                Notification::make()
                                    ->title(
                                        $resultado['errores'] > 0
                                        || $resultado['conflictos'] > 0
                                            ? 'Proceso completado con novedades'
                                            : 'Usuarios temporales creados'
                                    )
                                    ->body(new HtmlString($mensaje))
                                    ->success()
                                    ->persistent()
                                    ->send();
                            } catch (\Throwable $exception) {
                                report($exception);

                                Notification::make()
                                    ->title('No fue posible completar el proceso')
                                    ->body($exception->getMessage())
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }
                        }),
                ])

                /*
                |--------------------------------------------------------------------------
                | Esta acción principal no escribe nada
                |--------------------------------------------------------------------------
                */
                ->action(function (): void {
                    // Sin procesamiento.
                }),

            /*
            |--------------------------------------------------------------------------
            | Creación individual existente
            |--------------------------------------------------------------------------
            */
            Actions\CreateAction::make(),
        ];
    }
}