<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SedeResource\Pages;

use App\Models\Sede;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;

use Filament\Resources\Resource;

use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use App\Traits\HasRolePermissions;

class SedeResource extends Resource
{
    use HasRolePermissions;

    protected static ?string $viewPermission =
    'ver_sedes';

    protected static ?string $createPermission =
        'crear_sedes';

    protected static ?string $editPermission =
        'editar_sedes';

    protected static ?string $deletePermission =
        'eliminar_sedes';



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

            Section::make('Información general')
                ->icon('heroicon-o-building-office-2')
                ->schema([

                    TextInput::make('nombre')
                        ->label('Nombre de la sede')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),

                    TextInput::make('codigo')
                        ->label('Código')
                        ->maxLength(50),

                    Toggle::make('activa')
                        ->label('Sede activa')
                        ->default(true),

                    TextInput::make('direccion')
                        ->label('Dirección')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('telefono')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
                        ->maxLength(255),

                    TextInput::make('nit')
                        ->label('NIT')
                        ->maxLength(50),

                    FileUpload::make('logo')
                        ->label('Logo institucional')
                        ->directory('sedes/logos')
                        ->image()
                        ->imageEditor()
                        ->imagePreviewHeight('120')
                        ->downloadable()
                        ->openable()
                        ->columnSpanFull(),

                ])
                ->columns(2),

            Section::make('Información para documentos')
                ->description('Estos datos serán utilizados en recibos, certificados, constancias, paz y salvo y demás documentos generados por el sistema.')
                ->icon('heroicon-o-document-text')
                ->schema([

                    Textarea::make('pie_documentos')
                        ->label('Pie de documentos')
                        ->rows(4)
                        ->columnSpanFull(),

                    TextInput::make('representante_legal')
                        ->label('Representante legal')
                        ->maxLength(255),

                    TextInput::make('cargo_representante')
                        ->label('Cargo del representante')
                        ->maxLength(255),

                    Textarea::make('informacion_legal')
                        ->label('Información legal adicional')
                        ->rows(3)
                        ->columnSpanFull(),

                    TextInput::make('prefijo_documentos')
                        ->label('Prefijo de documentos')
                        ->helperText('Opcional. Ejemplo: REM, ESC, NORTE...')
                        ->maxLength(20),

                ])
                ->columns(2),

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

    

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSedes::route('/'),
            'create' => Pages\CreateSede::route('/create'),
            'edit' => Pages\EditSede::route('/{record}/edit'),
        ];
    }
}