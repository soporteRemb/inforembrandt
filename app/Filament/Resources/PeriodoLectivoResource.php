<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodoLectivoResource\Pages;
use App\Models\PeriodoLectivo;
use App\Models\Sede;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Traits\HasRolePermissions;

class PeriodoLectivoResource extends Resource
{
    use HasRolePermissions;

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
                ->native(false)->displayFormat('d/m/Y')
                ->required(),

            DatePicker::make('fecha_fin')
                ->label('Fecha fin')
                ->native(false)->displayFormat('d/m/Y')
                ->required(),

            Select::make('estado')
                ->options([
                    'abierto'   => 'Abierto',
                    'cerrado'   => 'Cerrado',
                ])
                ->default('abierto')
                ->required(),
        ]);
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
                    ->color(fn(string $state): string => match($state) {
                        'abierto'   => 'success',
                        'cerrado'   => 'danger',
                        default     => 'gray',
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
            'index'  => Pages\ListPeriodoLectivos::route('/'),
            'create' => Pages\CreatePeriodoLectivo::route('/create'),
            'edit'   => Pages\EditPeriodoLectivo::route('/{record}/edit'),
        ];
    }
}