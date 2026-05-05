<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GuardiansRelationManager extends RelationManager
{
    protected static string $relationship = 'guardians';
    protected static ?string $title = 'Acudientes';

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Tipo de acudiente')
                ->schema([
                    Select::make('tipo')
                        ->label('Tipo')
                        ->options([
                            'padre'            => 'Padre',
                            'madre'            => 'Madre',
                            'acudiente'        => 'Acudiente',
                            'deudor_economico' => 'Deudor Económico',
                        ])
                        ->required(),
                ]),

            Section::make('Datos personales')
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre completo')
                        ->required(),

                    Select::make('tipo_documento')
                        ->label('Tipo de documento')
                        ->options([
                            'CC'  => 'Cédula de Ciudadanía',
                            'CE'  => 'Cédula Extranjería',
                            'PA'  => 'Pasaporte',
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

                    TextInput::make('parentesco')
                        ->label('Parentesco'),
                ])->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('tipo')
                    ->label('Tipo')
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

                TextColumn::make('documento')
                    ->label('Documento'),

                TextColumn::make('telefono')
                    ->label('Teléfono'),

                TextColumn::make('correo')
                    ->label('Correo')
                    ->toggleable(),

                TextColumn::make('lugar_trabajo')
                    ->label('Lugar de trabajo')
                    ->toggleable(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear Acudiente'),
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
}