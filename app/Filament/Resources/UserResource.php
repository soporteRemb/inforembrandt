<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use Filament\Resources\Resource;

use App\Models\User;
use App\Models\Sede;


use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;


use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;


use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

use App\Traits\HasRolePermissions;


use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    use HasRolePermissions;



    protected static ?string $viewPermission =
        'ver_usuarios';

    protected static ?string $createPermission =
        'crear_usuarios';

    protected static ?string $editPermission =
        'editar_usuarios';

    protected static ?string $deletePermission =
        'eliminar_usuarios';
    



    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuarios';
    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int $navigationSort = 2;





    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $usuarioActual = auth()->user();

        if (! $usuarioActual) {
            return $query->whereRaw('1 = 0');
        }

        /*
        |--------------------------------------------------------------------------
        | Admin y SuperAdmin
        |--------------------------------------------------------------------------
        |
        | Pueden ver todos los usuarios, incluidos admin y superadmin.
        |
        */

        if ($usuarioActual->hasAnyRole([
            'admin',
            'superadmin',
        ])) {
            return $query;
        }

        /*
        |--------------------------------------------------------------------------
        | Resto de usuarios
        |--------------------------------------------------------------------------
        |
        | Aunque tengan todos los permisos del módulo Usuarios,
        | nunca verán cuentas admin o superadmin.
        |
        */

        return $query->whereDoesntHave(
            'roles',
            function (Builder $roles): void {
                $roles->whereIn(
                    'name',
                    [
                        'admin',
                        'superadmin',
                    ]
                );
            }
        );
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nombre completo')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Usuario')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
                

            TextInput::make('correo')
                ->label('Correo electrónico')
                ->email()
                ->maxLength(255)
                ->placeholder('Opcional'),
                

            TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->revealable()
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context): bool => $context === 'create')
                ->dehydrateStateUsing(
                    fn ($state) => filled($state)
                        ? Hash::make($state)
                        : null
                ),

            Select::make('roles')
                ->label('Roles')
                ->multiple()
                ->relationship(
                    name: 'roles',
                    titleAttribute: 'name',
                    modifyQueryUsing: function (Builder $query): Builder {
                        $usuarioActual = auth()->user();

                        if (
                            $usuarioActual?->hasAnyRole([
                                'admin',
                                'superadmin',
                            ])
                        ) {
                            return $query;
                        }

                        return $query->whereNotIn(
                            'name',
                            [
                                'admin',
                                'superadmin',
                            ]
                        );
                    }
                )
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, Set $set): void {
                    if (self::tieneRolSeleccionado($state, 'superadmin')) {
                        $set('is_active', true);
                        $set('expires_at', null);
                    }
                }),

            Select::make('sedes')
                ->label('Sedes')
                ->multiple()
                ->relationship('sedes', 'nombre')
                ->preload()
                ->helperText(
                    'Selecciona todas las sedes a las que tiene acceso este usuario.'
                ),

            Toggle::make('is_active')
                ->label('Usuario activo')
                ->default(true)
                ->inline(false)
                ->live()
                ->disabled(
                    fn (Get $get): bool =>
                        self::tieneRolSeleccionado(
                            $get('roles'),
                            'superadmin'
                        )
                )
                ->dehydrated()
                ->helperText(
                    'Los usuarios inactivos no podrán iniciar sesión.'
                ),

            DatePicker::make('expires_at')
                ->label('Fecha de vencimiento')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->closeOnDateSelection()
                ->minDate(today())
                ->required(
                    fn (Get $get): bool =>
                        ! self::tieneRolSeleccionado(
                            $get('roles'),
                            'superadmin'
                        )
                )
                ->hidden(
                    fn (Get $get): bool =>
                        self::tieneRolSeleccionado(
                            $get('roles'),
                            'superadmin'
                        )
                )
                ->dehydrateStateUsing(
                    fn ($state) => filled($state)
                        ? \Carbon\Carbon::parse($state)->endOfDay()
                        : null
                ),
                
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('correo')
                    ->label('Correo')
                    ->placeholder('Sin correo')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->placeholder('Sin vencimiento')
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->color(fn(string $state): string => match($state) {
                        'superadmin'              => 'danger',
                        'admin'                   => 'warning',
                        'rector'                  => 'info',
                        'coordinador_academico'   => 'info',
                        'coordinador_convivencia' => 'info',
                        'secretaria'              => 'success',
                        'director_grupo'          => 'success',
                        'docente'                 => 'success',
                        'acudiente'               => 'gray',
                        default                   => 'gray',
                    }),

                TextColumn::make('sedes.nombre')
                    ->label('Sedes')
                    ->badge()
                    ->color('primary')
                    ->separator(','),
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

    

    public static function canCreate(): bool
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return false;
        }

        return $usuario->hasAnyRole([
            'superadmin',
            'admin',
        ]) || $usuario->can('crear_usuarios');
    }

    public static function canEdit(
        \Illuminate\Database\Eloquent\Model $record
    ): bool {
        $usuario = auth()->user();

        if (! $usuario) {
            return false;
        }

        return $usuario->hasAnyRole([
            'superadmin',
            'admin',
        ]) || $usuario->can('editar_usuarios');
    }

    public static function tieneRolSeleccionado(
        mixed $estadoRoles,
        string $nombreRol
    ): bool {
        $ids = collect($estadoRoles ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($ids->isEmpty()) {
            return false;
        }

        $nombreBuscado = self::normalizarNombreRol($nombreRol);

        return Role::query()
            ->whereIn('id', $ids)
            ->get(['name'])
            ->contains(
                fn (Role $rol): bool =>
                    self::normalizarNombreRol($rol->name)
                    === $nombreBuscado
            );
    }

    public static function normalizarNombreRol(?string $nombre): ?string
    {
        if (blank($nombre)) {
            return null;
        }

        return (string) Str::of($nombre)
            ->trim()
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_');
    }

    /**
     * Obtiene el rol principal seleccionado y lo convierte
     * en el valor técnico que se guardará en tipo_usuario.
     */
    public static function tipoUsuarioDesdeRoles(
        mixed $estadoRoles
    ): ?string {
        $rolId = collect($estadoRoles ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->first();

        if (! $rolId) {
            return null;
        }

        $nombreRol = Role::query()
            ->whereKey($rolId)
            ->value('name');

        return self::normalizarNombreRol($nombreRol);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
