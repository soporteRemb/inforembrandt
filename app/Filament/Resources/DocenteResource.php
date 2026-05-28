<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocenteResource\Pages;
use App\Models\Docente;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Course;
use App\Models\PeriodoLectivo;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\DocenteResource\RelationManagers\AsignaturasRelationManager;

class DocenteResource extends Resource
{
    protected static ?string $model = Docente::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Docentes';
    protected static ?string $modelLabel = 'Docente';
    protected static ?string $pluralModelLabel = 'Docentes';
    protected static ?string $navigationGroup = 'Académico';
    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del docente')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario del sistema')
                            ->options(
                                User::query()
                                    ->whereHas('roles', fn ($query) => $query->where('name', 'docente'))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Seleccione el usuario con el que el docente iniciará sesión.'),

                        Forms\Components\TextInput::make('identificacion')
                            ->label('Identificación')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('apellidos')
                            ->label('Apellidos')
                            ->required()
                            ->maxLength(120),

                        Forms\Components\TextInput::make('nombres')
                            ->label('Nombres')
                            ->required()
                            ->maxLength(120),

                        Forms\Components\TextInput::make('telefono')
                            ->label('Teléfono')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('correo')
                            ->label('Correo')
                            ->required()
                            ->email()
                            ->maxLength(150),

                        Forms\Components\TextInput::make('direccion')
                            ->label('Dirección')
                            ->maxLength(180),

                        Forms\Components\TextInput::make('especialidad')
                            ->label('Especialidad')
                            ->maxLength(150),

                        Forms\Components\TextInput::make('escalafon')
                            ->label('Escalafón')
                            ->maxLength(100),

                        Forms\Components\Select::make('direccion_curso_id')
                            ->label('Dirección de curso')
                            ->options(function () {
                                $sedeId = Auth::user()?->sede_id;

                                $periodoLectivoId = PeriodoLectivo::query()
                                    ->where('sede_id', $sedeId)
                                    ->where('estado', 'abierto')
                                    ->latest('id')
                                    ->value('id');

                                return Course::query()
                                    ->where('sede_id', $sedeId)
                                    ->where('periodo_lectivo_id', $periodoLectivoId)
                                    ->orderBy('grado')
                                    ->orderBy('curso')
                                    ->get()
                                    ->mapWithKeys(fn ($course) => [
                                        $course->id => "{$course->curso} - {$course->descripcion}",
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(),

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
                Tables\Columns\TextColumn::make('identificacion')
                    ->label('Identificación')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nombre_completo')
                    ->label('Docente')
                    ->searchable(['nombres', 'apellidos']),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable(),

                Tables\Columns\TextColumn::make('especialidad')
                    ->label('Especialidad')
                    ->searchable(),

                Tables\Columns\TextColumn::make('cargo')
                    ->label('Cargo')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'activo',
                        'danger' => 'inactivo',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                    ]),
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

    public static function getRelations(): array
    {
        return [
            AsignaturasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocentes::route('/'),
            'create' => Pages\CreateDocente::route('/create'),
            'edit' => Pages\EditDocente::route('/{record}/edit'),
        ];
    }
}