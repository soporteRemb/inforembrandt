<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use App\Models\Sede;
use App\Models\PeriodoLectivo;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Traits\HasRolePermissions;
use Illuminate\Validation\Rules\Unique;

class CourseResource extends Resource
{
    use HasRolePermissions;

    protected static ?string $model = Course::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'Cursos';
    protected static ?string $modelLabel = 'Curso';
    protected static ?string $pluralModelLabel = 'Cursos';
    protected static ?string $navigationGroup = 'Académico';
    protected static ?int $navigationSort = 3;


    /*
    |--------------------------------------------------------------------------
    | Permisos
    |--------------------------------------------------------------------------
    */
    protected static ?string $viewPermission =
        'ver_cursos';

    protected static ?string $createPermission =
        'crear_cursos';

    protected static ?string $editPermission =
        'editar_cursos';

    protected static ?string $deletePermission =
        'eliminar_cursos';


    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        $sedeId = session('sede_id');
        $anio = session('anio');

        if ($sedeId) {
            $query->where('sede_id', $sedeId);
        }

        if ($anio) {
            $query->where('anio', $anio);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información del Curso')
                ->schema([
                    Select::make('sede_id')
                        ->label('Sede')
                        ->options(Sede::where('activa', true)->pluck('nombre', 'id'))
                        ->default(fn () => session('sede_id'))
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $state) {
                            $periodo = PeriodoLectivo::query()
                                ->where('sede_id', $state)
                                ->where('nombre', session('anio'))
                                ->value('id');

                            $set('periodo_lectivo_id', $periodo);
                        }),

                    Select::make('periodo_lectivo_id')
                        ->label('Periodo Lectivo')
                        ->options(function (callable $get) {
                            $sedeId = $get('sede_id');

                            $query = PeriodoLectivo::query()->with('sede');

                            if ($sedeId) {
                                $query->where('sede_id', $sedeId);
                            }

                            return $query
                                ->orderByDesc('nombre')
                                ->orderBy('sede_id')
                                ->get()
                                ->mapWithKeys(fn ($p) => [
                                    $p->id => $p->nombre . ' — ' . ($p->sede->nombre ?? ''),
                                ])
                                ->toArray();
                        })
                        ->default(function () {
                            return PeriodoLectivo::query()
                                ->where('sede_id', session('sede_id'))
                                ->where('nombre', session('anio'))
                                ->value('id');
                        })
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $state) {
                            $periodo = PeriodoLectivo::find($state);

                            if ($periodo) {
                                $set('anio', $periodo->nombre);
                            }
                        }),

                    TextInput::make('anio')
                        ->label('Año')
                        ->default(fn () => session('anio') ?? date('Y'))
                        ->required(),

                    TextInput::make('grado')
                        ->label('Grado')
                        ->placeholder('Ej: P, J, T, 1, 10, 11...')
                        ->maxLength(2)
                        ->required()
                        ->dehydrateStateUsing(fn ($state) => strtoupper(trim($state)))
                        ->rule('regex:/^[A-Za-z0-9]{1,2}$/'),
                        

                    TextInput::make('descripcion')
                        ->label('Descripción')
                        ->placeholder('Ej: Noveno, Décimo...')
                        ->required(),

                    TextInput::make('curso')
                        ->label('Curso')
                        ->placeholder('Ej: 901, 902...')
                        ->required()
                        ->rule(function (callable $get, ?\App\Models\Course $record) {
                            return \Illuminate\Validation\Rule::unique('courses', 'curso')
                                ->where('sede_id', $get('sede_id'))
                                ->where('anio', $get('anio'))
                                ->where('grado', strtoupper(trim($get('grado'))))
                                ->where('descripcion', trim($get('descripcion')))
                                ->ignore($record?->id);
                        })
                        ->validationMessages([
                            'unique' => 'Ya existe un curso registrado con la misma sede, año, grado, descripción y curso.',
                        ]),

                        Select::make('jornada')
                            ->label('Jornada')
                            ->options(
                                \App\Models\Jornada::orderBy('nombre')
                                    ->pluck('nombre', 'nombre')
                            )
                            ->default(fn () => \App\Models\Jornada::value('nombre'))
                            ->searchable()
                            ->preload(),
                ])->columns(3),
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

                TextColumn::make('periodoLectivo.nombre')
                    ->label('Periodo')
                    ->sortable(),

                TextColumn::make('anio')
                    ->label('Año')
                    ->sortable(),

                TextColumn::make('grado')
                    ->label('Grado')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable(),

                TextColumn::make('curso')
                    ->label('Curso')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('sede_id')
                    ->label('Sede')
                    ->options(Sede::where('activa', true)->pluck('nombre', 'id')),
            ])
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
            'index'  => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit'   => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}