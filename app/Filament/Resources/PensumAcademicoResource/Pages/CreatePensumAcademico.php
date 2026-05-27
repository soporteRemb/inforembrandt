<?php

namespace App\Filament\Resources\PensumAcademicoResource\Pages;

use App\Filament\Resources\PensumAcademicoResource;
use App\Models\PensumAcademico;
use Filament\Resources\Pages\CreateRecord;

class CreatePensumAcademico extends CreateRecord
{
    protected static string $resource = PensumAcademicoResource::class;

    protected function handleRecordCreation(array $data): PensumAcademico
    {
        return PensumAcademico::create([
            'sede_id' => $data['sede_id'],
            'periodo_lectivo_id' => $data['periodo_lectivo_id'],
            'grado' => $data['grado'],
            'course_id' => null,
            'docente_id' => null,
            'codigo' => $data['codigo'],
            'orden' => $data['orden'],
            'nombre' => $data['nombre'],
            'nombre_corto' => $data['nombre_corto'],
            'tipo' => $data['tipo'],
            'intensidad_horaria' => $data['intensidad_horaria'],
            'forma_evaluar' => $data['forma_evaluar'],
            'estado' => $data['estado'],
        ]);
    }
}