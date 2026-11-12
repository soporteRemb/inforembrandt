<?php

namespace App\Filament\Resources\AsignacionConceptoResource\Pages;

use App\Filament\Resources\AsignacionConceptoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ListAsignacionConceptos extends ListRecords
{
    protected static string $resource = AsignacionConceptoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportarExcel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->extraAttributes([
                    'style' => 'background-color: #dcfce7 !important; color: #15803d !important; border: 1px solid #86efac !important;',
                ])
                ->action(function () {
                    $asignaciones = AsignacionConceptoResource::getEloquentQuery()
                        ->with([
                            'vencimientos' => fn ($query) => $query
                                ->with('tipoLimiteExtemporaneo')
                                ->orderBy('fecha_vencimiento')
                                ->orderBy('tipo_limite_extemporaneo_id')
                                ->orderBy('id'),
                        ])
                        ->orderBy('grado')
                        ->orderBy('concepto_cobro_id')
                        ->get();

                    $filas = [];

                    foreach ($asignaciones as $asignacion) {
                        $datosAsignacion = [
                            $asignacion->grado,
                            $asignacion->conceptoCobro?->codigo,
                            $asignacion->conceptoCobro?->descripcion,
                            $asignacion->tarifa_ordinaria,
                            $asignacion->orden,
                            $asignacion->activo ? 'Sí' : 'No',
                        ];

                        if ($asignacion->vencimientos->isEmpty()) {
                            $filas[] = array_merge(
                                $datosAsignacion,
                                ['', '', '', '', '', '']
                            );

                            continue;
                        }

                        foreach ($asignacion->vencimientos as $vencimiento) {
                            $filas[] = array_merge(
                                $datosAsignacion,
                                [
                                    $vencimiento->mes,
                                    $vencimiento->anio,
                                    $vencimiento->fecha_vencimiento
                                        ? $vencimiento->fecha_vencimiento->format('d/m/Y')
                                        : '',
                                    $vencimiento->tipoLimiteExtemporaneo?->codigo,
                                    $vencimiento->tipoLimiteExtemporaneo?->nombre,
                                    $vencimiento->valor,
                                ]
                            );
                        }
                    }

                    $exportacion = new class($filas) implements FromArray, WithHeadings {
                        public function __construct(private array $filas) {}

                        public function array(): array
                        {
                            return $this->filas;
                        }

                        public function headings(): array
                        {
                            return [
                                'GRADO',
                                'CODIGO_CONCEPTO',
                                'CONCEPTO',
                                'TARIFA_ORDINARIA',
                                'ORDEN',
                                'ACTIVO',
                                'MES',
                                'AÑO',
                                'VENCIMIENTO',
                                'CODIGO_TIPO',
                                'TIPO',
                                'VALOR_EXTEMPORANEO',
                            ];
                        }
                    };

                    return Excel::download(
                        $exportacion,
                        'Asignaciones_Conceptos_' . now()->format('Ymd_His') . '.xlsx'
                    );
                }),

            Actions\CreateAction::make(),
        ];
    }
}
