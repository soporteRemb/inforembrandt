<?php

namespace App\Services\Importacion\Asignaciones;

use App\Models\Course;
use App\Models\Docente;
use App\Models\DocenteAsignatura;
use App\Models\PensumAcademico;
use App\Services\Importacion\BaseImportService;
use App\Services\Importacion\Contracts\ImportadorInterface;
use App\Services\Importacion\DTO\ResultadoImportacion;
use Illuminate\Support\Facades\DB;

class AsignacionesImportService extends BaseImportService implements ImportadorInterface
{
    public function importar(string $path, array $opciones = []): ResultadoImportacion
    {
        $resultado = $this->nuevoResultado();

        $sedeId = (int) ($opciones['sede_id'] ?? 0);
        $periodoLectivoId = (int) ($opciones['periodo_lectivo_id'] ?? 0);

        if (! $sedeId) {
            $resultado->agregarError('Debe seleccionar una sede.');
            return $resultado;
        }

        if (! $periodoLectivoId) {
            $resultado->agregarError('Debe seleccionar un periodo lectivo.');
            return $resultado;
        }

        $sheet = $this->abrirHojaActiva($path);
        $ultimaFila = $sheet->getHighestDataRow();

        DB::beginTransaction();

        try {
            for ($fila = 2; $fila <= $ultimaFila; $fila++) {
                $codigoDocente = $this->normalizarCodigo($this->valor($sheet, "A{$fila}"));
                $cursoExcel = $this->normalizarCodigo($this->valor($sheet, "B{$fila}"));
                $codigoMateria = $this->normalizarCodigo($this->valor($sheet, "C{$fila}"));

                if ($codigoDocente === '' && $cursoExcel === '' && $codigoMateria === '') {
                    continue;
                }

                $resultado->filasLeidas++;

                if ($codigoDocente === '') {
                    $this->agregarError($fila, 'No tiene código de docente.');
                    $resultado->omitidos++;
                    continue;
                }

                if ($cursoExcel === '') {
                    $this->agregarError($fila, "El docente {$codigoDocente} no tiene curso.");
                    $resultado->omitidos++;
                    continue;
                }

                if ($codigoMateria === '') {
                    $this->agregarError($fila, "El docente {$codigoDocente} no tiene código de materia.");
                    $resultado->omitidos++;
                    continue;
                }

                $docente = Docente::query()
                    ->where('codigo', $codigoDocente)
                    ->first();

                if (! $docente) {
                    $this->agregarError($fila, "No existe el docente con código {$codigoDocente}.");
                    $resultado->omitidos++;
                    continue;
                }

                $course = Course::query()
                    ->where('sede_id', $sedeId)
                    ->where('periodo_lectivo_id', $periodoLectivoId)
                    ->where('curso', $cursoExcel)
                    ->first();

                if (! $course) {
                    $this->agregarError($fila, "No existe el curso {$cursoExcel}.");
                    $resultado->omitidos++;
                    continue;
                }

                $pensum = PensumAcademico::query()
                    ->where('sede_id', $sedeId)
                    ->where('periodo_lectivo_id', $periodoLectivoId)
                    ->where('grado', $course->grado)
                    ->where('codigo', $codigoMateria)
                    ->first();

                if (! $pensum) {
                    $this->agregarError(
                        $fila,
                        "No existe la materia {$codigoMateria} para el grado {$course->grado} del curso {$cursoExcel}."
                    );
                    $resultado->omitidos++;
                    continue;
                }

                $existente = DocenteAsignatura::query()
                    ->where('docente_id', $docente->id)
                    ->where('course_id', $course->id)
                    ->where('pensum_academico_id', $pensum->id)
                    ->first();

                DocenteAsignatura::updateOrCreate(
                    [
                        'docente_id' => $docente->id,
                        'course_id' => $course->id,
                        'pensum_academico_id' => $pensum->id,
                    ],
                    []
                );

                if ($existente) {
                    $resultado->actualizados++;
                } else {
                    $resultado->creados++;
                }
            }

            DB::commit();

            return $resultado;
        } catch (\Throwable $e) {
            DB::rollBack();

            $resultado->agregarError('Error general durante la importación: ' . $e->getMessage());

            return $resultado;
        }
    }
}