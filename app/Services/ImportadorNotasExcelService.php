<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Student;
use App\Models\Docente;
use App\Models\NotaEstudiante;
use App\Models\PensumAcademico;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportadorNotasExcelService
{
    public function importar(
        string $filePath,
        int $periodoLectivoId,
        $user
    ): array {

        $spreadsheet = IOFactory::load($filePath);

        $docente = Docente::where('user_id', $user->id)->first();

        if (! $docente) {
            return [
                'success' => false,
                'message' => 'El usuario autenticado no tiene docente asociado.',
            ];
        }

        $hojasProcesadas = 0;
        $registrosImportados = 0;
        $errores = 0;
        $detalles = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {

            $hojasProcesadas++;

            $sheetName = $sheet->getTitle();

            /*
            |--------------------------------------------------------------------------
            | DATOS BASE DEL EXCEL
            |--------------------------------------------------------------------------
            */

            $materiaCodigo = trim((string) $sheet->getCell('F2')->getValue());
            $materiaNombre = trim((string) $sheet->getCell('G2')->getValue());

            $periodo = (int) trim((string) $sheet->getCell('F3')->getValue());

            $cursoCodigo = trim((string) $sheet->getCell('F4')->getValue());

            /*
            |--------------------------------------------------------------------------
            | VALIDAR COLUMNA SEGÚN PERIODO
            |--------------------------------------------------------------------------
            */

            $notaColumn = match ($periodo) {
                1 => 'D',
                2 => 'F',
                3 => 'H',
                4 => 'J',
                default => null,
            };

            if (! $notaColumn) {

                $errores++;

                $detalles[] =
                    "Hoja {$sheetName}: período inválido ({$periodo}).";

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | BUSCAR CURSO REAL
            |--------------------------------------------------------------------------
            */

            $course = Course::query()
                ->where('curso', $cursoCodigo)
                ->where('periodo_lectivo_id', $periodoLectivoId)
                ->first();

            if (! $course) {

                $errores++;

                $detalles[] =
                    "Hoja {$sheetName}: curso no encontrado ({$cursoCodigo}).";

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | BUSCAR PENSUM ACADÉMICO
            |--------------------------------------------------------------------------
            */

            $pensum = PensumAcademico::query()
                ->where('docente_id', $docente->id)
                ->where('course_id', $course->id)
                ->where('periodo_lectivo_id', $periodoLectivoId)
                ->where('nombre', $materiaNombre)
                ->first();

            if (! $pensum) {

                $errores++;

                $detalles[] =
                    "Hoja {$sheetName}: pensum académico no encontrado para {$materiaNombre}.";

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | LEER ESTUDIANTES
            |--------------------------------------------------------------------------
            */

            $row = 8;

            while (
                trim((string) $sheet->getCell("B{$row}")->getValue()) !== ''
            ) {

                $studentCode =
                    trim((string) $sheet->getCell("B{$row}")->getValue());

                $studentName =
                    trim((string) $sheet->getCell("C{$row}")->getValue());

                $notaValue =
                    $sheet->getCell("{$notaColumn}{$row}")->getValue();

                /*
                |--------------------------------------------------------------------------
                | BUSCAR ESTUDIANTE REAL
                |--------------------------------------------------------------------------
                */

                $student = Student::query()
                    ->where('codigo', $studentCode)
                    ->where('course_id', $course->id)
                    ->where('periodo_lectivo_id', $periodoLectivoId)
                    ->first();

                if (! $student) {

                    $errores++;

                    $detalles[] =
                        "Hoja {$sheetName}, fila {$row}: estudiante no encontrado ({$studentCode} - {$studentName}).";

                    $row++;

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | GUARDAR NOTA
                |--------------------------------------------------------------------------
                */

                if ($notaValue !== null && $notaValue !== '') {

                    NotaEstudiante::updateOrCreate(

                        [
                            'student_id' => $student->id,
                            'pensum_academico_id' => $pensum->id,
                            'periodo' => $periodo,
                        ],

                        [
                            'nota' => is_numeric($notaValue)
                                ? (float) $notaValue
                                : null,

                            'fallas' => 0,
                            'mejoramiento' => null,
                            'observacion' => null,
                        ]
                    );

                    $registrosImportados++;
                }

                $row++;
            }
        }

        return [

            'success' => true,

            'hojas' => $hojasProcesadas,

            'importados' => $registrosImportados,

            'errores' => $errores,

            'detalles' => $detalles,
        ];
    }
}