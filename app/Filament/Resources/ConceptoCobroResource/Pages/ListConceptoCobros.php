<?php

namespace App\Filament\Resources\ConceptoCobroResource\Pages;

use App\Filament\Resources\ConceptoCobroResource;
use App\Models\ConceptoCobro;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ListConceptoCobros extends ListRecords
{
    protected static string $resource = ConceptoCobroResource::class;

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
                    $conceptos = ConceptoCobroResource::getEloquentQuery()
                        ->orderBy('codigo')
                        ->get();

                    $filas = $conceptos->map(fn ($concepto) => [
                        $concepto->codigo,
                        $concepto->descripcion,
                        ucfirst($concepto->tipo_movimiento),
                        ucfirst($concepto->control),
                        (int) $concepto->impuesto,
                        $concepto->facturar ? 'Sí' : 'No',
                        $concepto->obligatorio ? 'Sí' : 'No',
                        $concepto->activo ? 'Sí' : 'No',
                        $concepto->portal_acudientes ? 'Sí' : 'No',
                        $concepto->aplicar_intereses ? 'Sí' : 'No',
                    ])->toArray();

                    $exportacion = new class($filas) implements FromArray, WithHeadings {
                        public function __construct(private array $filas) {}

                        public function array(): array
                        {
                            return $this->filas;
                        }

                        public function headings(): array
                        {
                            return [
                                'CODIGO',
                                'DESCRIPCION',
                                'TIPO_MOVIMIENTO',
                                'CONTROL',
                                'IMPUESTO',
                                'FACTURAR',
                                'OBLIGATORIO',
                                'ACTIVO',
                                'PORTAL_ACUDIENTES',
                                'APLICAR_INTERESES',
                            ];
                        }
                    };

                    return Excel::download(
                        $exportacion,
                        'Conceptos_Cobro_' . now()->format('Ymd_His') . '.xlsx'
                    );
                }),

            Actions\CreateAction::make(),
        ];
    }
}