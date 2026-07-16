<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsignacionConceptoResource\Pages;
use App\Models\AsignacionConcepto;
use App\Models\ConceptoCobro;
use App\Models\Course;
use App\Models\PeriodoLectivo;
use App\Models\Sede;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use App\Filament\Resources\AsignacionConceptoResource\RelationManagers;

class AsignacionConceptoResource extends Resource
{
    protected static ?string $model = AsignacionConcepto::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?string $navigationLabel = 'Asignar Conceptos';

    protected static ?string $modelLabel = 'Asignación de Concepto';

    protected static ?string $pluralModelLabel = 'Asignar Conceptos';

    protected static ?int $navigationSort = 6;

    protected static bool $shouldRegisterNavigation = true;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Datos generales')
                    ->compact()
                    ->columns(4)
                    ->schema([
                        Forms\Components\Select::make('sede_id')
                            ->label('Sede')
                            ->options(fn () => Sede::query()->pluck('nombre', 'id'))
                            ->searchable()
                            ->preload()
                            ->default(fn () => session('sede_id'))
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('periodo_lectivo_id')
                            ->label('Periodo Lectivo')
                            ->options(function (Forms\Get $get) {
                                $sedeId = session('sede_id') ?? $get('sede_id');

                                return PeriodoLectivo::query()
                                    ->with('sede')
                                    ->where('sede_id', $sedeId)
                                    ->orderByDesc('nombre')
                                    ->get()
                                    ->mapWithKeys(fn ($periodo) => [
                                        $periodo->id => $periodo->sede->nombre . ' - ' . $periodo->nombre,
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->default(function (Forms\Get $get) {
                                $sedeId = session('sede_id') ?? $get('sede_id');

                                return session('periodo_lectivo_id')
                                    ?? PeriodoLectivo::query()
                                        ->where('sede_id', $sedeId)
                                        ->where('nombre', 'like', '%' . session('anio') . '%')
                                        ->value('id')
                                    ?? PeriodoLectivo::query()
                                        ->where('sede_id', $sedeId)
                                        ->orderByDesc('nombre')
                                        ->value('id');
                            })
                            ->required()
                            ->live(),
                    ]),

                Forms\Components\Section::make('Datos de asignación')
                    ->compact()
                    ->columns(6)
                    ->schema([
                        Forms\Components\Select::make('grado')
                            ->label('Grado')
                            ->options(function (Forms\Get $get) {
                                return Course::query()
                                    ->when($get('sede_id'), fn ($query, $sedeId) => $query->where('sede_id', $sedeId))
                                    ->when($get('periodo_lectivo_id'), fn ($query, $periodoId) => $query->where('periodo_lectivo_id', $periodoId))
                                    ->whereNotNull('grado')
                                    ->orderBy('grado')
                                    ->pluck('descripcion', 'grado')
                                    ->unique()
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(3),

                        Forms\Components\Select::make('concepto_cobro_id')
                            ->label('Concepto')
                            ->options(function (Forms\Get $get) {
                                return ConceptoCobro::query()
                                    ->when($get('sede_id'), fn ($query, $sedeId) => $query->where('sede_id', $sedeId))
                                    ->when($get('periodo_lectivo_id'), fn ($query, $periodoId) => $query->where('periodo_lectivo_id', $periodoId))
                                    ->where('activo', true)
                                    ->orderBy('codigo')
                                    ->get()
                                    ->mapWithKeys(function ($concepto) {
                                        return [
                                            $concepto->id => $concepto->codigo . ' - ' . $concepto->descripcion,
                                        ];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->rules([
                                function (Forms\Get $get, ?AsignacionConcepto $record) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                        $existe = AsignacionConcepto::query()
                                            ->where('sede_id', $get('sede_id'))
                                            ->where('periodo_lectivo_id', $get('periodo_lectivo_id'))
                                            ->where('grado', $get('grado'))
                                            ->where('concepto_cobro_id', $value)
                                            ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                            ->exists();

                                        if ($existe) {
                                            $fail('Este concepto ya está asignado para esta sede, periodo lectivo y grado.');
                                        }
                                    };
                                },
                            ])
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('tarifa_ordinaria')
                            ->label('Tarifa Ordinaria')
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
                                'regex' => 'Ingrese el valor sin puntos ni comas.',
                            ])
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\Hidden::make('tarifa_extemporanea')
                            ->default(0),

                        Forms\Components\Hidden::make('orden')
                        ->default(1),

                        Forms\Components\Toggle::make('activo')
                            ->label('Activo')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1)
                    ]),

                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->columns([
                Tables\Columns\TextColumn::make('sede.nombre')
                    ->label('Sede')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('periodoLectivo.nombre')
                    ->label('Periodo')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('grado')
                    ->label('Grado')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('conceptoCobro.descripcion')
                    ->label('Concepto')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('tarifa_ordinaria')
                    ->label('Valor Ord.')
                    ->money('COP')
                    ->alignEnd()
                    ->sortable(),

                Forms\Components\Hidden::make('tarifa_extemporanea')
                    ->default(0),

                Tables\Columns\TextColumn::make('orden')
                    ->label('Orden')
                    ->alignCenter()
                    ->sortable(),

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
                                    $periodo->id => $periodo->sede->nombre . ' - ' . $periodo->nombre,
                                ];
                            })
                    ),

                Tables\Filters\SelectFilter::make('grado')
                    ->label('Grado')
                    ->options(
                        fn () => Course::query()
                            ->whereNotNull('grado')
                            ->orderBy('grado')
                            ->pluck('descripcion', 'grado')
                            ->unique()
                            ->toArray()
                    ),

                Tables\Filters\SelectFilter::make('activo')
                    ->label('Estado')
                    ->options([
                        1 => 'Activo',
                        0 => 'Inactivo',
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
            ->emptyStateHeading('No hay conceptos asignados')
            ->emptyStateDescription('Asigna conceptos de cobro a los grados del colegio.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VencimientosRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $sedeId = session('sede_id');

        $periodoLectivoId = session('periodo_lectivo_id')
            ?? PeriodoLectivo::query()
                ->where('sede_id', $sedeId)
                ->where('estado', 'abierto')
                ->orderByDesc('nombre')
                ->value('id');

        return parent::getEloquentQuery()
            ->when($sedeId, fn ($query) => $query->where('sede_id', $sedeId))
            ->when($periodoLectivoId, fn ($query) => $query->where('periodo_lectivo_id', $periodoLectivoId))
            ->with([
                'sede',
                'periodoLectivo',
                'conceptoCobro',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsignacionConceptos::route('/'),
            'create' => Pages\CreateAsignacionConcepto::route('/create'),
            'edit' => Pages\EditAsignacionConcepto::route('/{record}/edit'),
        ];
    }
}