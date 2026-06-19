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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información del Curso')
                ->schema([
                    Select::make('sede_id')
                        ->label('Sede')
                        ->options(Sede::where('activa', true)->pluck('nombre', 'id'))
                        ->searchable()
                        ->required()
                        ->reactive(),

                    Select::make('periodo_lectivo_id')
                        ->label('Periodo Lectivo')
                        ->options(function (callable $get) {
                            $sedeId = $get('sede_id');
                            $query  = PeriodoLectivo::query();
                            if ($sedeId) {
                                $query->where('sede_id', $sedeId);
                            }
                            return $query->orderByDesc('nombre')
                                ->get()
                                ->mapWithKeys(fn($p) => [
                                    $p->id => $p->nombre . ($sedeId ? '' : ' — ' . ($p->sede->nombre ?? ''))
                                ]);
                        })
                        ->searchable()
                        ->required(),

                    TextInput::make('anio')
                        ->label('Año')
                        ->default(date('Y'))
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
                        ->required(),
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