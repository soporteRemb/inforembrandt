<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConceptoCobroResource\Pages;
use App\Models\ConceptoCobro;
use App\Models\PeriodoLectivo;
use App\Models\Sede;
use App\Traits\HasRolePermissions;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


use Illuminate\Database\Eloquent\Builder;

class ConceptoCobroResource extends Resource
{

    use HasRolePermissions;

    protected static ?string $viewPermission =
        'ver_conceptos_cobro';

    protected static ?string $createPermission =
        'crear_conceptos_cobro';

    protected static ?string $editPermission =
        'editar_conceptos_cobro';

    protected static ?string $deletePermission =
        'eliminar_conceptos_cobro';




    protected static ?string $model = ConceptoCobro::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?string $navigationLabel = 'Conceptos de Cobro';

    protected static ?string $modelLabel = 'Concepto de Cobro';

    protected static ?string $pluralModelLabel = 'Conceptos de Cobro';

    protected static ?int $navigationSort = 5;





    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('sede_id')
                            ->label('Sede')
                            ->options(fn () => Sede::query()->pluck('nombre', 'id'))
                            ->searchable()
                            ->preload()
                            ->default(fn () => Sede::query()->orderBy('id')->value('id'))
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('periodo_lectivo_id')
                            ->label('Periodo Lectivo')
                            ->options(
                                fn () => PeriodoLectivo::query()
                                    ->with('sede')
                                    ->orderByDesc('nombre')
                                    ->get()
                                    ->mapWithKeys(function ($periodo) {
                                        return [
                                            $periodo->id => $periodo->sede->nombre . ' - ' . $periodo->nombre
                                        ];
                                    })
                            )
                            ->searchable()
                            ->preload()
                            ->default(fn () => PeriodoLectivo::query()
                                ->where('estado', 'abierto')
                                ->orderByDesc('nombre')
                                ->value('id'))
                            ->required(),
                    ]),

                Forms\Components\Section::make('')
                    ->columns(12)
                    ->schema([
                        Forms\Components\TextInput::make('codigo')
                            ->label('Código')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(99)
                            ->required()
                            ->columnSpan(3)
                            ->live()
                            ->helperText(function (Forms\Get $get) {

                                if (!$get('sede_id') || !$get('periodo_lectivo_id')) {
                                    return null;
                                }

                                $ultimoCodigo = \App\Models\ConceptoCobro::query()
                                    ->where('sede_id', $get('sede_id'))
                                    ->where('periodo_lectivo_id', $get('periodo_lectivo_id'))
                                    ->max('codigo');

                                $siguiente = $ultimoCodigo ? $ultimoCodigo + 1 : 1;

                                return new \Illuminate\Support\HtmlString(
                                    '<span style="font-size: 12px; color: #6b7280;">
                                        Siguiente código sugerido: ' . $siguiente . '
                                    </span>'
                                );
                            })
                            ->unique(
                                table: 'concepto_cobros',
                                column: 'codigo',
                                ignoreRecord: true,
                                modifyRuleUsing: function ($rule, Forms\Get $get) {
                                    return $rule
                                        ->where('sede_id', $get('sede_id'))
                                        ->where('periodo_lectivo_id', $get('periodo_lectivo_id'));
                                }
                            ),

                        Forms\Components\TextInput::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Ej: Matrícula')
                            ->maxLength(150)
                            ->required()
                            ->columnSpan(9),

                        Forms\Components\Select::make('tipo_movimiento')
                            ->label('Tipo de Movimiento')
                            ->options([
                                'ingreso' => 'Ingreso',
                                'egreso' => 'Egreso',
                            ])
                            ->default('ingreso')
                            ->required()
                            ->columnSpan(4),

                        Forms\Components\Select::make('control')
                            ->label('Control')
                            ->options([
                                'interno' => 'Interno',
                                'externo' => 'Externo',
                            ])
                            ->default('interno')
                            ->required()
                            ->columnSpan(4),

                        Forms\Components\TextInput::make('impuesto')
                            ->label('Impuesto (%)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(1)
                            ->default(0)
                            ->required()
                            ->dehydrateStateUsing(fn ($state) => (int) $state)
                            ->columnSpan(4),

                        Forms\Components\Grid::make(3)
                            ->columnSpan(5)
                            ->schema([
                                Forms\Components\Toggle::make('facturar')
                                    ->label('Facturar')
                                    ->default(false)
                                    ->inline(false),

                                Forms\Components\Toggle::make('obligatorio')
                                    ->label('Obligatorio')
                                    ->default(false)
                                    ->inline(false),

                                Forms\Components\Toggle::make('activo')
                                    ->label('Activo')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('sede.nombre')
                    ->label('Sede')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('periodoLectivo.nombre')
                    ->label('Periodo')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo_movimiento')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => $state === 'ingreso' ? 'Ingreso' : 'Egreso')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'ingreso' ? 'success' : 'warning')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('control')
                    ->label('Control')
                    ->formatStateUsing(fn (string $state): string => $state === 'interno' ? 'Interno' : 'Externo'),

                Tables\Columns\IconColumn::make('facturar')
                    ->label('Facturar')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('obligatorio')
                    ->label('Obligatorio')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('impuesto')
                    ->label('Impuesto (%)')
                    ->suffix('%')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sede_id')
                    ->label('Sede')
                    ->options(fn () => Sede::query()->pluck('nombre', 'id')),

                Tables\Filters\SelectFilter::make('periodo_lectivo_id')
                    ->label('Periodo Lectivo')
                    ->options(
                        fn () => PeriodoLectivo::query()
                            ->with('sede')
                            ->get()
                            ->mapWithKeys(function ($periodo) {
                                return [
                                    $periodo->id => $periodo->sede->nombre . ' - ' . $periodo->nombre
                                ];
                            })
                    ),

                Tables\Filters\SelectFilter::make('tipo_movimiento')
                    ->label('Tipo de Movimiento')
                    ->options([
                        'ingreso' => 'Ingreso',
                        'egreso' => 'Egreso',
                    ]),

                Tables\Filters\SelectFilter::make('control')
                    ->label('Control')
                    ->options([
                        'interno' => 'Interno',
                        'externo' => 'Externo',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                    
                Tables\Actions\DeleteAction::make()
                    ->label('Borrar'),
                    
            ])
            
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Borrar seleccionados'),
            ])
            ->emptyStateHeading('No hay conceptos de cobro')
            ->emptyStateDescription('Crea el primer concepto como matrícula, pensión o plataforma.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['sede', 'periodoLectivo']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConceptoCobros::route('/'),
            'create' => Pages\CreateConceptoCobro::route('/create'),
            'edit' => Pages\EditConceptoCobro::route('/{record}/edit'),
        ];
    }
}