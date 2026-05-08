<?php

namespace App\Filament\Resources\AsignacionConceptoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;



class VencimientosRelationManager extends RelationManager
{
    protected static string $relationship = 'vencimientos';

    protected static ?string $title = 'Vencimientos extemporáneos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('mes')
                    ->label('Mes')
                    ->options([
                        'ENERO' => 'Enero',
                        'FEBRERO' => 'Febrero',
                        'MARZO' => 'Marzo',
                        'ABRIL' => 'Abril',
                        'MAYO' => 'Mayo',
                        'JUNIO' => 'Junio',
                        'JULIO' => 'Julio',
                        'AGOSTO' => 'Agosto',
                        'SEPTIEMBRE' => 'Septiembre',
                        'OCTUBRE' => 'Octubre',
                        'NOVIEMBRE' => 'Noviembre',
                        'DICIEMBRE' => 'Diciembre',
                    ])
                    ->required()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: function ($rule, $livewire) {
                            return $rule->where(
                                'asignacion_concepto_id',
                                $livewire->ownerRecord->id
                            );
                        }
                    )
                    ->validationMessages([
                        'unique' => 'Ya existe un vencimiento configurado para este mes.',
                    ]),


                Forms\Components\DatePicker::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->native(false)
                    ->displayFormat('d/m/Y'),

                Forms\Components\TextInput::make('porcentaje')
                    ->label('Porcentaje (%)')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('dias')
                    ->label('Días')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) =>
                $query->orderByRaw("
                    FIELD(
                        mes,
                        'ENERO',
                        'FEBRERO',
                        'MARZO',
                        'ABRIL',
                        'MAYO',
                        'JUNIO',
                        'JULIO',
                        'AGOSTO',
                        'SEPTIEMBRE',
                        'OCTUBRE',
                        'NOVIEMBRE',
                        'DICIEMBRE'
                    )
                ")
            )
            ->striped()
            ->heading('')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Agregar vencimiento')
                    ->modalHeading('Nuevo vencimiento extemporáneo')
                    ->modalSubmitActionLabel('Guardar vencimiento')
                    ->color('danger'),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('mes')
                    ->label('Mes')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('porcentaje')
                    ->label('Porcentaje')
                    ->suffix('%')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('dias')
                    ->label('Días')
                    ->alignCenter(),
            ])
            
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('danger'),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}
