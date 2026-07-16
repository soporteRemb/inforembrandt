<?php

namespace App\Filament\Resources\AsignacionConceptoResource\RelationManagers;

use App\Models\AsignacionConceptoVencimiento;
use App\Models\TipoLimiteExtemporaneo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VencimientosRelationManager extends RelationManager
{
    protected static string $relationship = 'vencimientos';

    protected static ?string $title = 'Tarifas extemporáneas';

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
                    ->required(),

                Forms\Components\DatePicker::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->native(false)
                    ->displayFormat('d/m/Y'),

                Forms\Components\Select::make('tipo_limite_extemporaneo_id')
                    ->label('Tipo')
                    ->options(fn () => TipoLimiteExtemporaneo::query()
                        ->where('activo', true)
                        ->orderBy('orden')
                        ->pluck('nombre', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->rules([
                        function (Forms\Get $get, ?AsignacionConceptoVencimiento $record) {
                            return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                $existe = AsignacionConceptoVencimiento::query()
                                    ->where('asignacion_concepto_id', $this->ownerRecord->id)
                                    ->where('mes', $get('mes'))
                                    ->where('tipo_limite_extemporaneo_id', $value)
                                    ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                    ->exists();

                                if ($existe) {
                                    $fail('Ya existe una tarifa para este mes y este tipo de límite.');
                                }
                            };
                        },
                    ]),

                Forms\Components\TextInput::make('valor')
                    ->label('Valor')
                    ->prefix('$')
                    ->inputMode('decimal')
                    ->formatStateUsing(function ($state) {
                        if ($state === null) {
                            return null;
                        }

                        return fmod((float) $state, 1) == 0
                            ? number_format((float) $state, 0, '', '')
                            : str_replace('.', ',', (string) $state);
                    })
                    ->dehydrateStateUsing(fn ($state) => str_replace(',', '.', $state))
                    ->rule('regex:/^\d+(,\d{1,2})?$/')
                    ->validationMessages([
                        'regex' => 'Ingrese el valor sin puntos. Use coma solo si necesita decimales.',
                    ])
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) =>
                $query->with('tipoLimiteExtemporaneo')
                    ->leftJoin(
                        'tipo_limite_extemporaneos',
                        'asignacion_concepto_vencimientos.tipo_limite_extemporaneo_id',
                        '=',
                        'tipo_limite_extemporaneos.id'
                    )
                    ->select('asignacion_concepto_vencimientos.*')
                    ->orderByRaw("
                        FIELD(
                            asignacion_concepto_vencimientos.mes,
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
                    ->orderBy('tipo_limite_extemporaneos.orden')
            )
            ->striped()
            ->heading('')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Agregar tarifa')
                    ->modalHeading('Nueva tarifa extemporánea')
                    ->modalSubmitActionLabel('Guardar tarifa')
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

                Tables\Columns\TextColumn::make('tipoLimiteExtemporaneo.nombre')
                    ->label('Tipo')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.'))
                    ->alignEnd(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('danger'),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}