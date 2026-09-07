<?php

namespace App\Services\DocumentosEstudiante;

use App\Models\PreMatricula;
use App\Models\Student;

class IncorporarDocumentosPreMatriculaService
{
    public function __construct(
        private DocumentoEstudianteService $documentoEstudianteService
    ) {
    }

    public function ejecutar(
        Student $student
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Buscar pre-matrícula correspondiente
        |--------------------------------------------------------------------------
        |
        | La relación se hace por:
        | - documento del estudiante
        | - sede
        | - periodo lectivo
        |
        | Con esto evitamos tomar accidentalmente una pre-matrícula antigua
        | del mismo estudiante.
        |
        */

        $preMatricula = PreMatricula::query()
            ->where(
                'documento',
                $student->documento
            )
            ->where(
                'sede_id',
                $student->sede_id
            )
            ->where(function ($query) use ($student) {
                $query
                    ->where(
                        'periodo_destino_id',
                        $student->periodo_lectivo_id
                    )
                    ->orWhere(function ($query) use ($student) {
                        $query
                            ->whereNull('periodo_destino_id')
                            ->where(
                                'periodo_lectivo_id',
                                $student->periodo_lectivo_id
                            );
                    });
            })
            ->with('documentos')
            ->latest('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | No existe pre-matrícula
        |--------------------------------------------------------------------------
        |
        | Esto NO es un error.
        |
        | Algunos estudiantes pueden provenir de procesos históricos,
        | importaciones manuales o matrículas que no pasaron por
        | pre-matrícula.
        |
        */

        if (! $preMatricula) {
            return [
                'pre_matricula_encontrada' => false,
                'pre_matricula_id' => null,
                'documentos_encontrados' => 0,
                'documentos_incorporados' => 0,
                'documentos_ya_existentes' => 0,
            ];
        }

        $incorporados = 0;
        $yaExistentes = 0;

        /*
        |--------------------------------------------------------------------------
        | Incorporar documentos
        |--------------------------------------------------------------------------
        */

        foreach ($preMatricula->documentos as $documento) {

            $yaExistia = $student
                ->archivosDocumentos()
                ->where(
                    'pre_matricula_documento_id',
                    $documento->id
                )
                ->exists();

            $this->documentoEstudianteService
                ->incorporarDesdePreMatricula(
                    $student,
                    $documento
                );

            if ($yaExistia) {
                $yaExistentes++;
            } else {
                $incorporados++;
            }
        }

        return [
            'pre_matricula_encontrada' => true,

            'pre_matricula_id' =>
                $preMatricula->id,

            'documentos_encontrados' =>
                $preMatricula->documentos->count(),

            'documentos_incorporados' =>
                $incorporados,

            'documentos_ya_existentes' =>
                $yaExistentes,
        ];
    }
}