<?php

namespace App\Services\DocumentosEstudiante;

use App\Models\PeriodoLectivo;
use App\Models\PreMatricula;
use App\Models\Student;

class IncorporarDocumentosPreMatriculaService
{
    public function __construct(
        private DocumentoEstudianteService $documentoEstudianteService
    ) {
    }

    public function ejecutar(
        Student $student,
        ?string $numeroFormulario = null
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

        $numeroFormulario = trim((string) $numeroFormulario);

        if ($numeroFormulario !== '') {
            $preMatricula = PreMatricula::query()
                ->where('numero_formulario', $numeroFormulario)
                ->where('documento', $student->documento)
                ->where('sede_id', $student->sede_id)
                ->with('documentos')
                ->first();

            if ($preMatricula) {
                $periodoDestinoValido =
                    (int) $preMatricula->periodo_destino_id ===
                    (int) $student->periodo_lectivo_id;

                if (! $periodoDestinoValido) {
                    $periodoOrigen = PeriodoLectivo::query()
                        ->whereKey($preMatricula->periodo_lectivo_id)
                        ->where('sede_id', $student->sede_id)
                        ->first();

                    $periodoEstudiante = PeriodoLectivo::query()
                        ->whereKey($student->periodo_lectivo_id)
                        ->where('sede_id', $student->sede_id)
                        ->first();

                    $anioOrigen = trim((string) ($periodoOrigen?->nombre ?? ''));
                    $anioEstudiante = trim((string) ($periodoEstudiante?->nombre ?? ''));

                    $periodoDestinoValido =
                        preg_match('/^\d{4}$/', $anioOrigen) === 1
                        && preg_match('/^\d{4}$/', $anioEstudiante) === 1
                        && (int) $anioEstudiante === (int) $anioOrigen + 1;
                }

                if (! $periodoDestinoValido) {
                    $preMatricula = null;
                }
            }
        } else {
            $preMatricula = PreMatricula::query()
                ->where('documento', $student->documento)
                ->where('sede_id', $student->sede_id)
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
        }

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