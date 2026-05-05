<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Models\Course;
use App\Models\PeriodoLectivo;
use App\Models\Sede;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MatriculasRelationManager extends RelationManager
{
    protected static string $relationship = 'matriculas';
    protected static ?string $title = 'Matrículas';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('course_id')
                ->label('Curso')
                ->options(function () {
                    $sedeId = session('sede_id');
                    $anio   = session('anio', date('Y'));
                    $periodo = PeriodoLectivo::where('sede_id', $sedeId)
                        ->where('nombre', $anio)->first();
                    if (!$periodo) return [];
                    return Course::where('sede_id', $sedeId)
                        ->where('periodo_lectivo_id', $periodo->id)
                        ->get()
                        ->mapWithKeys(fn($c) => [$c->id => $c->grado . '° - ' . $c->curso]);
                })
                ->searchable()
                ->required(),

            DatePicker::make('fecha_matricula')
                ->label('Fecha de matrícula')
                ->native(false)->displayFormat('d/m/Y')
                ->default(now())
                ->required(),

            Select::make('tipo_matricula')
                ->label('Tipo de matrícula')
                ->options([
                    'nueva'      => 'Nueva',
                    'renovacion' => 'Renovación',
                    'traslado'   => 'Traslado',
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
        ])->columns(2);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $student = $this->getOwnerRecord();
        $data['sede_id']            = $student->sede_id;
        $data['periodo_lectivo_id'] = $student->periodo_lectivo_id;
        return $data;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('consecutivo')
                    ->label('No.')
                    ->sortable(),

                TextColumn::make('course.curso')
                    ->label('Curso')
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
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Nueva matrícula'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
