<?php

namespace App\Services\Importacion\Estudiantes;

use App\Models\Course;
use App\Models\Matricula;
use App\Models\Student;
use App\Services\Importacion\BaseImportService;
use App\Services\Importacion\Contracts\ImportadorInterface;
use App\Services\Importacion\DTO\ResultadoImportacion;
use Illuminate\Support\Facades\DB;

class EstudiantesImportService extends BaseImportService implements ImportadorInterface
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
                $nombres = $this->valor($sheet, "A{$fila}");
                $apellidos = $this->valor($sheet, "B{$fila}");
                $documento = $this->valor($sheet, "C{$fila}");
                $fechaNacimientoRaw = $sheet->getCell("D{$fila}")->getValue();
                $genero = $this->valor($sheet, "E{$fila}");
                $telefono = $this->valor($sheet, "F{$fila}");
                $correo = $this->valor($sheet, "G{$fila}");
                $direccion = $this->valor($sheet, "H{$fila}");
                $nivel = $this->valor($sheet, "I{$fila}");
                $gradoExcel = $this->valor($sheet, "J{$fila}");
                $cursoExcel = $this->valor($sheet, "K{$fila}");
                $codigoExcel = $this->valor($sheet, "L{$fila}");

                if (
                    $nombres === '' &&
                    $apellidos === '' &&
                    $documento === '' &&
                    $cursoExcel === ''
                ) {
                    continue;
                }

                $resultado->filasLeidas++;

                if ($documento === '') {
                    $this->agregarError($fila, 'El estudiante no tiene identificación/documento.');
                    $resultado->omitidos++;
                    continue;
                }

                if ($nombres === '') {
                    $this->agregarError($fila, "El estudiante con documento {$documento} no tiene nombres.");
                    $resultado->omitidos++;
                    continue;
                }

                if ($apellidos === '') {
                    $this->agregarError($fila, "El estudiante con documento {$documento} no tiene apellidos.");
                    $resultado->omitidos++;
                    continue;
                }

                if ($cursoExcel === '') {
                    $this->agregarError($fila, "El estudiante {$documento} no tiene curso/sección.");
                    $resultado->omitidos++;
                    continue;
                }

                $course = Course::query()
                    ->where('sede_id', $sedeId)
                    ->where('periodo_lectivo_id', $periodoLectivoId)
                    ->where('curso', $cursoExcel)
                    ->first();

                if (! $course) {
                    $this->agregarError($fila, "No existe el curso {$cursoExcel} para la sede y periodo seleccionados.");
                    $resultado->omitidos++;
                    continue;
                }

                [$primerNombre, $segundoNombre] = $this->separarDosPartes($nombres);
                [$primerApellido, $segundoApellido] = $this->separarDosPartes($apellidos);

                $fechaNacimiento = $this->convertirFecha($fechaNacimientoRaw);

                $sexo = match (mb_strtoupper($genero)) {
                    'M' => 'Masculino',
                    'F' => 'Femenino',
                    'MASCULINO' => 'Masculino',
                    'FEMENINO' => 'Femenino',
                    default => $genero ?: 'Sin definir',
                };

                $observacionesImportacion = collect([
                    $direccion ? "Dirección importada: {$direccion}" : null,
                    $nivel ? "Nivel importado: {$nivel}" : null,
                    $telefono ? "Teléfono importado: {$telefono}" : null,
                    $gradoExcel ? "Grado Excel: {$gradoExcel}" : null,
                ])->filter()->implode("\n");

                $studentExistente = Student::query()
                    ->where('documento', $documento)
                    ->where('periodo_lectivo_id', $periodoLectivoId)
                    ->first();

                $student = Student::updateOrCreate(
                [
                    'documento' => $documento,
                    'periodo_lectivo_id' => $periodoLectivoId,
                ],
                [
                    'sede_id' => $sedeId,
                    'course_id' => $course->id,
                    'codigo' => $codigoExcel ?: null,
                    'tipo_documento' => 'TI',

                    'primer_nombre' => $primerNombre,
                    'segundo_nombre' => $segundoNombre,
                    'primer_apellido' => $primerApellido,
                    'segundo_apellido' => $segundoApellido,

                    'sexo' => $sexo,
                    'fecha_nacimiento' => $fechaNacimiento,

                    // Campos obligatorios del formulario que no vienen en el Excel
                    'ciudad_expedicion' => $this->obligatorio(null),
                    'ciudad_nacimiento' => $this->obligatorio(null),
                    'eps' => $this->obligatorio(null),
                    'rh' => $this->obligatorio(null),
                    'parentesco_matricula' => $this->obligatorio(null),

                    'correo' => $correo ?: null,
                    'telefono_emergencia' => $telefono ?: null,

                    'estado' => 'activo',
                    'fecha_matricula' => now()->toDateString(),

                    'ultimo_grado' => $gradoExcel ?: null,

                    'observaciones' => $observacionesImportacion ?: null,
                ]
            );

                if ($studentExistente) {
                    $resultado->actualizados++;
                } else {
                    $resultado->creados++;
                }

                Matricula::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'periodo_lectivo_id' => $periodoLectivoId,
                    ],
                    [
                        'sede_id' => $sedeId,
                        'course_id' => $course->id,
                        'fecha_matricula' => now()->toDateString(),
                        'estado' => 'activa',
                        'tipo_matricula' => 'nueva',
                        'observaciones' => 'Matrícula generada desde importación de estudiantes.',
                    ]
                );

                $resultado->matriculas++;
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