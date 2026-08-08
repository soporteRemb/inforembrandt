<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PensumAcademicoResource\Pages;
use App\Models\Course;
use App\Models\Docente;
use App\Models\PensumAcademico;
use App\Models\PeriodoLectivo;
use App\Models\Sede;
use App\Traits\HasRolePermissions;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Support\Facades\Auth;


class PensumAcademicoResource extends Resource
{
    use HasRolePermissions;

    protected static ?string $viewPermission =
        'ver_pensum';

    protected static ?string $createPermission =
        'crear_pensum';

    protected static ?string $editPermission =
        'editar_pensum';

    protected static ?string $deletePermission =
        'eliminar_pensum';


    protected static ?string $model = PensumAcademico::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Pensum Académico';
    protected static ?string $modelLabel = 'Pensum Académico';
    protected static ?string $pluralModelLabel = 'Pensum Académico';
    protected static ?string $navigationGroup = 'Académico';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información académica')
                    ->schema([
                        Forms\Components\Select::make('sede_id')
                            ->label('Sede')
                            ->options(Sede::query()->pluck('nombre', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => Auth::user()?->sede_id)
                            ->reactive(),

                        Forms\Components\Select::make('periodo_lectivo_id')
                            ->label('Periodo lectivo')
                            ->options(fn () => PeriodoLectivo::query()
                                ->with('sede')
                                ->orderByDesc('nombre')
                                ->orderBy('sede_id')
                                ->get()
                                ->mapWithKeys(fn ($periodo) => [
                                    $periodo->id => ($periodo->sede?->nombre ?? 'Sin sede') . ' - ' . $periodo->nombre,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => PeriodoLectivo::query()
                                ->where('sede_id', Auth::user()?->sede_id)
                                ->where('estado', 'abierto')
                                ->latest('id')
                                ->value('id'))
                            ->reactive(),

                        Forms\Components\Select::make('grado')
                            ->label('Grado')
                            ->options(fn (Forms\Get $get) => Course::query()
                                ->when($get('sede_id'), fn ($q, $sedeId) => $q->where('sede_id', $sedeId))
                                ->when($get('periodo_lectivo_id'), fn ($q, $periodoId) => $q->where('periodo_lectivo_id', $periodoId))
                                ->orderBy('grado')
                                ->get()
                                ->unique('grado')
                                ->mapWithKeys(fn ($course) => [
                                    $course->grado => "{$course->grado} - {$course->descripcion}",
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required(),

                        
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Asignatura')
                    ->schema([
                        Forms\Components\TextInput::make('codigo')
                            ->label('Código')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('orden')
                            ->label('Orden')
                            ->required()
                            ->numeric()
                            ->default(1),

                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre de la asignatura')
                            ->required()
                            ->maxLength(150),

                        Forms\Components\TextInput::make('nombre_corto')
                            ->label('Nombre corto')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'area' => 'Área',
                                'asignatura' => 'Asignatura',
                            ])
                            ->default('asignatura')
                            ->required(),

                        Forms\Components\TextInput::make('intensidad_horaria')
                            ->label('Intensidad horaria')
                            ->required()
                            ->numeric(),

                        Forms\Components\Select::make('forma_evaluar')
                            ->label('Forma de evaluar')
                            ->options([
                                'bimestral' => 'Bimestral',
                                'semestral' => 'Semestral',
                            ])
                            ->default('bimestral')
                            ->required(),

                        Forms\Components\Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'activo' => 'Activo',
                                'inactivo' => 'Inactivo',
                            ])
                            ->default('activo')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sede.nombre')
                    ->label('Sede')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('periodoLectivo.nombre')
                    ->label('Periodo')
                    ->sortable(),

                Tables\Columns\TextColumn::make('grado')
                    ->label('Grado')
                    ->searchable()
                    ->sortable(),
                
                    

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Asignatura')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('forma_evaluar')
                    ->label('Evaluación'),

                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'activo',
                        'danger' => 'inactivo',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                    ]),

                Tables\Filters\SelectFilter::make('periodo_lectivo_id')
                    ->label('Periodo lectivo')
                    ->options(fn () => PeriodoLectivo::query()->pluck('nombre', 'id')),

                Tables\Filters\SelectFilter::make('course_id')
                    ->label('Curso')
                    ->options(fn () => Course::query()
                        ->orderBy('grado')
                        ->orderBy('curso')
                        ->pluck('curso', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPensumAcademicos::route('/'),
            'create' => Pages\CreatePensumAcademico::route('/create'),
            'edit' => Pages\EditPensumAcademico::route('/{record}/edit'),
        ];
    }
}