<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Sede;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use App\Traits\HasRolePermissions;

class UserResource extends Resource
{
    use HasRolePermissions;

    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuarios';
    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255),

            TextInput::make('password')
                ->password()
                ->dehydrateStateUsing(fn($state) => Hash::make($state))
                ->dehydrated(fn($state) => filled($state))
                ->required(fn(string $context) => $context === 'create')
                ->label('Contraseña'),

            Select::make('sede_id')
                ->label('Sede')
                ->options(Sede::where('activa', true)->pluck('nombre', 'id'))
                ->searchable()
                ->nullable(),

            Select::make('tipo_usuario')
                ->label('Tipo de usuario')
                ->options([
                    'superadmin' => 'Super Admin',
                    'admin'      => 'Administrador',
                    'rector'     => 'Rector',
                    'docente'    => 'Docente',
                    'acudiente'  => 'Acudiente',
                ])
                ->required(),

            Select::make('roles')
                ->label('Rol')
                ->multiple()
                ->relationship('roles', 'name')
                ->preload(),
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
                    ->searchable(),

                TextColumn::make('sede.nombre')
                    ->label('Sede')
                    ->sortable(),

                TextColumn::make('tipo_usuario')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn(string $state): string => match($state) {
                        'superadmin' => 'danger',
                        'admin'      => 'warning',
                        'rector'     => 'info',
                        'docente'    => 'success',
                        'acudiente'  => 'gray',
                        default      => 'gray',
                    }),

                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge(),
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

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if (in_array($user->tipo_usuario ?? '', ['superadmin', 'admin'])) return true;
        if (method_exists($user, 'hasAnyRole')) return $user->hasAnyRole(['superadmin', 'admin']);
        return false;
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