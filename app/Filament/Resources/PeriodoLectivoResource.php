<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodoLectivoResource\Pages;
use App\Models\PeriodoAcademico;
use App\Models\PeriodoLectivo;
use App\Models\Sede;
use App\Traits\HasRolePermissions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PeriodoLectivoResource extends Resource
{
    use HasRolePermissions;


    protected static ?string $viewPermission =
    'ver_periodos';

    protected static ?string $createPermission =
        'crear_periodos';

    protected static ?string $editPermission =
        'editar_periodos';

    protected static ?string $deletePermission =
        'eliminar_periodos';



    protected static ?string $model = PeriodoLectivo::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Periodos Lectivos';
    protected static ?string $modelLabel = 'Periodo Lectivo';
    protected static ?string $pluralModelLabel = 'Periodos Lectivos';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información general')
                ->schema([
                    Select::make('sede_id')
                        ->label('Sede')
                        ->options(Sede::where('activa', true)->pluck('nombre', 'id'))
                        ->searchable()
                        ->required(),

                    TextInput::make('nombre')
                        ->label('Nombre del periodo')
                        ->placeholder('Ej: 2026')
                        ->required(),

                    DatePicker::make('fecha_inicio')
                        ->label('Fecha inicio')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->required(),

                    DatePicker::make('fecha_fin')
                        ->label('Fecha fin')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->required(),

                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'abierto' => 'Abierto',
                            'cerrado' => 'Cerrado',
                        ])
                        ->default('abierto')
                        ->required(),
                ])
                ->columns(2),

            Section::make('Estado de Periodos Académicos')
                ->schema([
                    Select::make('periodo_1_estado')
                        ->label('Primer periodo')
                        ->options([
                            'abierto' => 'Abierto',
                            'cerrado' => 'Cerrado',
                        ])
                        ->native(false)
                        ->default('abierto')
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Select $component, $state, $record) {
                            $component->state(
                                static::obtenerEstadoPeriodoAcademico($record, '1')
                            );
                        }),

                    Select::make('periodo_2_estado')
                        ->label('Segundo periodo')
                        ->options([
                            'abierto' => 'Abierto',
                            'cerrado' => 'Cerrado',
                        ])
                        ->native(false)
                        ->default('abierto')
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Select $component, $state, $record) {
                            $component->state(
                                static::obtenerEstadoPeriodoAcademico($record, '2')
                            );
                        }),

                    Select::make('periodo_3_estado')
                        ->label('Tercer periodo')
                        ->options([
                            'abierto' => 'Abierto',
                            'cerrado' => 'Cerrado',
                        ])
                        ->native(false)
                        ->default('abierto')
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Select $component, $state, $record) {
                            $component->state(
                                static::obtenerEstadoPeriodoAcademico($record, '3')
                            );
                        }),

                    Select::make('periodo_4_estado')
                        ->label('Cuarto periodo')
                        ->options([
                            'abierto' => 'Abierto',
                            'cerrado' => 'Cerrado',
                        ])
                        ->native(false)
                        ->default('abierto')
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Select $component, $state, $record) {
                            $component->state(
                                static::obtenerEstadoPeriodoAcademico($record, '4')
                            );
                        }),
                ])
                ->columns(4),
        ]);
    }

    public static function obtenerEstadoPeriodoAcademico($record, string $numero): string
    {
        if (! $record) {
            return 'abierto';
        }

        return PeriodoAcademico::query()
            ->where('periodo_lectivo_id', $record->id)
            ->where('numero', $numero)
            ->value('estado') ?? 'abierto';
    }

    public static function guardarPeriodosAcademicos(PeriodoLectivo $record, array $data): void
    {
        $periodos = [
            '1' => 'Primer periodo',
            '2' => 'Segundo periodo',
            '3' => 'Tercer periodo',
            '4' => 'Cuarto periodo',
        ];

        foreach ($periodos as $numero => $nombre) {
            PeriodoAcademico::updateOrCreate(
                [
                    'periodo_lectivo_id' => $record->id,
                    'numero' => $numero,
                ],
                [
                    'nombre' => $nombre,
                    'estado' => $data["periodo_{$numero}_estado"] ?? 'abierto',
                ]
            );
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sede.nombre')
                    ->label('Sede')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nombre')
                    ->label('Periodo')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y'),

                TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->date('d/m/Y'),

                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'abierto' => 'success',
                        'cerrado' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeriodoLectivos::route('/'),
            'create' => Pages\CreatePeriodoLectivo::route('/create'),
            'edit' => Pages\EditPeriodoLectivo::route('/{record}/edit'),
        ];
    }
}