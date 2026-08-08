<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MatriculaResource\Pages;
use App\Traits\ScopedBySedePeriodo;
use App\Models\Matricula;
use App\Models\Sede;
use App\Models\PeriodoLectivo;
use App\Models\Student;
use App\Models\Course;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Traits\HasRolePermissions;

class MatriculaResource extends Resource
{
    use HasRolePermissions;

    /*
    |--------------------------------------------------------------------------
    | Permisos
    |--------------------------------------------------------------------------
    */

    protected static ?string $viewPermission =
        'ver_matriculas';

    protected static ?string $createPermission =
        'crear_matriculas';

    protected static ?string $editPermission =
        'editar_matriculas';

    protected static ?string $deletePermission =
        'eliminar_matriculas';



    use ScopedBySedePeriodo;
    
    protected static ?string $model = Matricula::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Matrículas';
    protected static ?string $modelLabel = 'Matrícula';
    protected static ?string $pluralModelLabel = 'Matrículas';
    protected static ?string $navigationGroup = 'Académico';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información de la Matrícula')
                ->schema([
                    Select::make('sede_id')
                        ->label('Sede')
                        ->options(Sede::where('activa', true)->pluck('nombre', 'id'))
                        ->searchable()
                        ->required(),

                    Select::make('periodo_lectivo_id')
                        ->label('Periodo Lectivo')
                        ->options(PeriodoLectivo::where('estado', 'abierto')->pluck('nombre', 'id'))
                        ->searchable()
                        ->required(),

                    TextInput::make('consecutivo')
                        ->label('Consecutivo')
                        ->disabled()
                        ->dehydrated(false)
                        ->hidden(fn(string $operation): bool => $operation === 'create'),
                ])->columns(3),

            Section::make('Estudiante y Curso')
                ->schema([
                    Select::make('student_id')
                        ->label('Estudiante')
                        ->options(
                            Student::all()->mapWithKeys(fn($s) => [
                                $s->id => $s->primer_apellido . ' ' . $s->segundo_apellido . ', ' . $s->primer_nombre
                            ])
                        )
                        ->searchable()
                        ->required(),

                    Select::make('course_id')
                        ->label('Curso')
                        ->options(
                            Course::all()->mapWithKeys(fn($c) => [
                                $c->id => $c->descripcion . ' - ' . $c->curso
                            ])
                        )
                        ->searchable()
                        ->required(),
                ])->columns(2),

            Section::make('Detalles')
                ->schema([
                    DatePicker::make('fecha_matricula')
                        ->label('Fecha de matrícula')
                        ->native(false)->displayFormat('d/m/Y')
                        ->default(now())
                        ->required(),

                    Select::make('tipo_matricula')
                        ->label('Tipo de matrícula')
                        ->options([
                            'nueva'     => 'Nueva',
                            'renovacion' => 'Renovación',
                            'traslado'  => 'Traslado',
                        ])
                        ->default('nueva')
                        ->required(),

                    Select::make('estado')
                        ->options([
                            'activa'     => 'Activa',
                            'retirado'   => 'Retirado',
                            'trasladado' => 'Trasladado',
                            'cancelada'  => 'Cancelada',
                        ])
                        ->default('activa')
                        ->required(),

                    TextInput::make('beca_porcentaje')
                        ->label('Beca (%)')
                        ->numeric()
                        ->default(0)
                        ->suffix('%'),

                    Textarea::make('observaciones')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('consecutivo')
                    ->label('No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.primer_apellido')
                    ->label('Estudiante')
                    ->formatStateUsing(fn($record) =>
                        $record->student->primer_apellido . ' ' .
                        $record->student->primer_nombre
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('course.curso')
                    ->label('Curso')
                    ->sortable(),

                TextColumn::make('sede.nombre')
                    ->label('Sede')
                    ->sortable(),

                TextColumn::make('periodoLectivo.nombre')
                    ->label('Periodo')
                    ->sortable(),

                TextColumn::make('fecha_matricula')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('tipo_matricula')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn(string $state): string => match($state) {
                        'nueva'      => 'success',
                        'renovacion' => 'info',
                        'traslado'   => 'warning',
                        default      => 'gray',
                    }),

                TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match($state) {
                        'activa'     => 'success',
                        'retirado'   => 'danger',
                        'trasladado' => 'warning',
                        'cancelada'  => 'danger',
                        default      => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('sede_id')
                    ->label('Sede')
                    ->options(Sede::where('activa', true)->pluck('nombre', 'id')),

                SelectFilter::make('estado')
                    ->options([
                        'activa'     => 'Activa',
                        'retirado'   => 'Retirado',
                        'trasladado' => 'Trasladado',
                        'cancelada'  => 'Cancelada',
                    ]),

                SelectFilter::make('tipo_matricula')
                    ->label('Tipo')
                    ->options([
                        'nueva'      => 'Nueva',
                        'renovacion' => 'Renovación',
                        'traslado'   => 'Traslado',
                    ]),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMatriculas::route('/'),
            'create' => Pages\CreateMatricula::route('/create'),
            'edit'   => Pages\EditMatricula::route('/{record}/edit'),
        ];
    }
}