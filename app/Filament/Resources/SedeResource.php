<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SedeResource\Pages;
use App\Models\Sede;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Traits\HasRolePermissions;

class SedeResource extends Resource
{
    use HasRolePermissions;

    protected static ?string $model = Sede::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Sedes';
    protected static ?string $modelLabel = 'Sede';
    protected static ?string $pluralModelLabel = 'Sedes';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('nombre')
                ->required()
                ->maxLength(255),

            TextInput::make('codigo')
                ->label('Código')
                ->maxLength(50),

            TextInput::make('direccion')
                ->label('Dirección')
                ->maxLength(255),

            TextInput::make('telefono')
                ->label('Teléfono')
                ->maxLength(20),

            TextInput::make('email')
                ->email()
                ->maxLength(255),

            Toggle::make('activa')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable(),

                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->toggleable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->toggleable(),

                TextColumn::make('email')
                    ->toggleable(),

                IconColumn::make('activa')
                    ->boolean(),
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
            'index' => Pages\ListSedes::route('/'),
            'create' => Pages\CreateSede::route('/create'),
            'edit' => Pages\EditSede::route('/{record}/edit'),
        ];
    }
}