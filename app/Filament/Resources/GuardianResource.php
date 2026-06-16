<?php

namespace App\Filament\Resources;


use App\Models\Guardian;
use App\Models\PeriodoLectivo;
use App\Models\Student;
use App\Models\User;

use App\Traits\HasRolePermissions;


use App\Filament\Resources\GuardianResource\Pages;


use Illuminate\Database\Eloquent\Builder;


use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;


class GuardianResource extends Resource
{
    use HasRolePermissions;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $sedeId = session('sede_id');
        $anio   = session('anio', date('Y'));

        if (!$sedeId) {
            return $query;
        }

        $periodo = PeriodoLectivo::where('sede_id', $sedeId)
            ->where('nombre', $anio)
            ->first();

        if (!$periodo) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereHas('student', function (Builder $q) use ($sedeId, $periodo) {
            $q->where('sede_id', $sedeId)
              ->where('periodo_lectivo_id', $periodo->id);
        });
    }
    protected static ?string $model = Guardian::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Acudientes';
    protected static ?string $modelLabel = 'Acudiente';
    protected static ?string $pluralModelLabel = 'Acudientes';
    protected static ?string $navigationGroup = 'Académico';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Estudiante')
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

                    Select::make('user_id')
                        ->label('Usuario de acceso')
                        ->options(
                            User::query()
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn ($user) => [
                                    $user->id => $user->name . ' - ' . $user->email,
                                ])
                                ->toArray()
                        )
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->helperText('Cuenta con la que este acudiente podrá ingresar al portal. Puede repetirse para hermanos o grupo familiar.'),

                    Select::make('tipo')
                        ->label('Tipo')
                        ->options([
                            'padre'           => 'Padre',
                            'madre'           => 'Madre',
                            'acudiente'       => 'Acudiente',
                            'deudor_economico' => 'Deudor Económico',
                        ])
                        ->required(),
                ])->columns(3),

            Section::make('Datos personales')
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre completo')
                        ->required(),

                    Select::make('tipo_documento')
                        ->label('Tipo de documento')
                        ->options([
                            'CC' => 'Cédula de Ciudadanía',
                            'CE' => 'Cédula Extranjería',
                            'PA' => 'Pasaporte',
                            'NIT' => 'NIT',
                        ]),

                    TextInput::make('documento')
                        ->label('Documento'),

                    TextInput::make('telefono')
                        ->label('Teléfono'),

                    TextInput::make('correo')
                        ->label('Correo electrónico')
                        ->email(),

                    TextInput::make('direccion')
                        ->label('Dirección'),

                    TextInput::make('lugar_trabajo')
                        ->label('Lugar de trabajo'),
                        
                    Select::make('estado')
                        ->options([
                            'activo'   => 'Activo',
                            'inactivo' => 'Inactivo',
                        ])
                        ->default('activo')
                        ->required(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.primer_apellido')
                    ->label('Estudiante')
                    ->formatStateUsing(fn($record) =>
                        $record->student->primer_apellido . ' ' .
                        $record->student->primer_nombre
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tipo')
                    ->badge()
                    ->color(fn(string $state): string => match($state) {
                        'padre'            => 'info',
                        'madre'            => 'success',
                        'acudiente'        => 'warning',
                        'deudor_economico' => 'danger',
                        default            => 'gray',
                    }),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Cuenta familiar')
                    ->placeholder('Sin cuenta')
                    ->searchable()
                    ->toggleable()
                    ->description(function ($record) {
                        if (! $record->user_id) {
                            return 'Sin acceso al portal';
                        }

                        return Guardian::query()
                            ->with('student')
                            ->where('user_id', $record->user_id)
                            ->where('estado', 'activo')
                            ->get()
                            ->pluck('student')
                            ->filter()
                            ->unique('id')
                            ->map(fn ($student) =>
                                trim(
                                    $student->primer_nombre . ' ' .
                                    $student->primer_apellido
                                )
                            )
                            ->implode(' · ');
                    }),

                        

                TextColumn::make('telefono')
                    ->label('Teléfono'),

                TextColumn::make('correo')
                    ->label('Correo')
                    ->toggleable(),

                TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match($state) {
                        'activo'   => 'success',
                        'inactivo' => 'danger',
                        default    => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->options([
                        'padre'            => 'Padre',
                        'madre'            => 'Madre',
                        'acudiente'        => 'Acudiente',
                        'deudor_economico' => 'Deudor Económico',
                    ]),

                SelectFilter::make('estado')
                    ->options([
                        'activo'   => 'Activo',
                        'inactivo' => 'Inactivo',
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGuardians::route('/'),
            'create' => Pages\CreateGuardian::route('/create'),
            'edit'   => Pages\EditGuardian::route('/{record}/edit'),
        ];
    }
}