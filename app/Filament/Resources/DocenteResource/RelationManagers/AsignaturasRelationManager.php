<?php

namespace App\Filament\Resources\DocenteResource\RelationManagers;

use App\Models\Course;
use App\Models\PensumAcademico;
use App\Models\PeriodoLectivo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AsignaturasRelationManager extends RelationManager
{
    protected static string $relationship = 'asignaturas';

    protected static ?string $title = 'Asignación de materias';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('course_id')
                    ->label('Curso')
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
                    ->required(),

                Forms\Components\Select::make('pensum_academico_id')
                    ->label('Asignatura')
                    ->options(function () {

                        $sedeId = Auth::user()?->sede_id;

                        $periodoLectivoId = PeriodoLectivo::query()
                            ->where('sede_id', $sedeId)
                            ->where('estado', 'abierto')
                            ->latest('id')
                            ->value('id');

                        return PensumAcademico::query()
                            ->where('sede_id', $sedeId)
                            ->where('periodo_lectivo_id', $periodoLectivoId)
                            ->orderBy('nombre')
                            ->get()
                            ->mapWithKeys(fn ($pensum) => [
                                $pensum->id => $pensum->nombre,
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('course.curso')
                    ->label('Curso')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pensumAcademico.nombre')
                    ->label('Asignatura')
                    ->searchable()
                    ->sortable(),
            ])
            ->headerActions([

                Tables\Actions\CreateAction::make()
                    ->label('Asignar materia'),
            ])
            ->actions([

                Tables\Actions\EditAction::make()
                    ->label('Editar'),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ]);
    }
}