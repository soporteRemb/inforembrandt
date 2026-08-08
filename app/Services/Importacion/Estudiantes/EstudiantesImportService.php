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
    public function importar(
        string $path,
        array $opciones = []
    ): ResultadoImportacion {
        $resultado = $this->nuevoResultado();

        $sedeId = (int) ($opciones['sede_id'] ?? 0);
        $periodoLectivoId = (int) (
            $opciones['periodo_lectivo_id'] ?? 0
        );

        if (! $sedeId) {
            $resultado->agregarError(
                'Debe seleccionar una sede.'
            );

            return $resultado;
        }

        if (! $periodoLectivoId) {
            $resultado->agregarError(
                'Debe seleccionar un periodo lectivo.'
            );

            return $resultado;
        }

        $sheet = $this->abrirHojaActiva($path);
        $ultimaFila = $sheet->getHighestDataRow();

        /*
        |--------------------------------------------------------------------------
        | Detectar plantilla de interoperabilidad
        |--------------------------------------------------------------------------
        |
        | El archivo exportado desde Pre-matrículas incluye NUMERO_FORMULARIO
        | en la columna V. El formato histórico termina en la columna L.
        |
        */

        $encabezadoNumeroFormulario = mb_strtoupper(
            trim((string) $sheet->getCell('V1')->getValue()),
            'UTF-8'
        );

        $esArchivoPreMatricula =
            $encabezadoNumeroFormulario === 'NUMERO_FORMULARIO';




        DB::beginTransaction();

        try {
            for ($fila = 2; $fila <= $ultimaFila; $fila++) {
                /*
                |--------------------------------------------------------------------------
                | Formato base A-L
                |--------------------------------------------------------------------------
                |
                | Estas columnas conservan exactamente el orden histórico.
                |
                */

                $nombres = $this->normalizarTexto(
                    $this->valor($sheet, "A{$fila}")
                );

                $apellidos = $this->normalizarTexto(
                    $this->valor($sheet, "B{$fila}")
                );

                $documento = $this->normalizarTexto(
                    $this->valor($sheet, "C{$fila}")
                );

                $fechaNacimientoRaw = $sheet
                    ->getCell("D{$fila}")
                    ->getValue();

                $genero = $this->normalizarTexto(
                    $this->valor($sheet, "E{$fila}")
                );

                $telefono = $this->normalizarTexto(
                    $this->valor($sheet, "F{$fila}")
                );

                $correo = $this->normalizarTexto(
                    $this->valor($sheet, "G{$fila}")
                );

                $direccion = $this->normalizarTexto(
                    $this->valor($sheet, "H{$fila}")
                );

                $nivel = $this->normalizarTexto(
                    $this->valor($sheet, "I{$fila}")
                );

                $gradoExcel = $this->normalizarTexto(
                    $this->valor($sheet, "J{$fila}")
                );

                $cursoExcel = $this->normalizarTexto(
                    $this->valor($sheet, "K{$fila}")
                );

                $codigoExcel = $this->normalizarTexto(
                    $this->valor($sheet, "L{$fila}")
                );

                /*
                |--------------------------------------------------------------------------
                | Formato ampliado de interoperabilidad M-V
                |--------------------------------------------------------------------------
                |
                | Si el archivo es antiguo, estas columnas estarán vacías y se
                | aplicarán los valores predeterminados utilizados hasta ahora.
                |
                */

                $tipoDocumentoExcel = $this->normalizarTexto(
                    $this->valor($sheet, "M{$fila}")
                );

                $ciudadExpedicion = $this->normalizarTexto(
                    $this->valor($sheet, "N{$fila}")
                );

                $ciudadNacimiento = $this->normalizarTexto(
                    $this->valor($sheet, "O{$fila}")
                );

                $rh = $this->normalizarTexto(
                    $this->valor($sheet, "P{$fila}")
                );

                $eps = $this->normalizarTexto(
                    $this->valor($sheet, "Q{$fila}")
                );

                $telefonoEmergencia = $this->normalizarTexto(
                    $this->valor($sheet, "R{$fila}")
                );

                $numeroHermanos = $this->normalizarTexto(
                    $this->valor($sheet, "S{$fila}")
                );

                $condicionIngreso = $this->normalizarTexto(
                    $this->valor($sheet, "T{$fila}")
                );

                $institucionAnterior = $this->normalizarTexto(
                    $this->valor($sheet, "U{$fila}")
                );

                $numeroFormulario = $this->normalizarTexto(
                    $this->valor($sheet, "V{$fila}")
                );

                /*
                |--------------------------------------------------------------------------
                | Ignorar filas completamente vacías
                |--------------------------------------------------------------------------
                */

                if (
                    $nombres === ''
                    && $apellidos === ''
                    && $documento === ''
                    && $cursoExcel === ''
                ) {
                    continue;
                }

                $resultado->filasLeidas++;

                /*
                |--------------------------------------------------------------------------
                | Validaciones históricas
                |--------------------------------------------------------------------------
                */

                if ($documento === '') {
                    $this->agregarError(
                        $fila,
                        'El estudiante no tiene identificación/documento.'
                    );

                    $resultado->omitidos++;

                    continue;
                }

                if ($nombres === '') {
                    $this->agregarError(
                        $fila,
                        "El estudiante con documento {$documento} no tiene nombres."
                    );

                    $resultado->omitidos++;

                    continue;
                }

                if ($apellidos === '') {
                    $this->agregarError(
                        $fila,
                        "El estudiante con documento {$documento} no tiene apellidos."
                    );

                    $resultado->omitidos++;

                    continue;
                }

                

                /*
                |--------------------------------------------------------------------------
                | Curso o sección
                |--------------------------------------------------------------------------
                |
                | El formato antiguo exige curso porque crea inmediatamente la matrícula.
                | El formato de Pre-matrículas puede venir sin curso: el estudiante se
                | importa y la matrícula se completa posteriormente.
                |
                */

                if ($cursoExcel === '' && ! $esArchivoPreMatricula) {
                    $this->agregarError(
                        $fila,
                        "El estudiante {$documento} no tiene curso/sección."
                    );

                    $resultado->omitidos++;

                    continue;
                }

                $course = null;

                if ($cursoExcel !== '') {
                    $course = Course::query()
                        ->where('sede_id', $sedeId)
                        ->where(
                            'periodo_lectivo_id',
                            $periodoLectivoId
                        )
                        ->where('curso', $cursoExcel)
                        ->first();

                    if (! $course) {
                        $this->agregarError(
                            $fila,
                            "No existe el curso {$cursoExcel} para la sede y periodo seleccionados."
                        );

                        $resultado->omitidos++;

                        continue;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Preparación de datos
                |--------------------------------------------------------------------------
                */

                [$primerNombre, $segundoNombre] =
                    $this->separarDosPartes($nombres);

                [$primerApellido, $segundoApellido] =
                    $this->separarDosPartes($apellidos);

                $fechaNacimiento = $this->convertirFecha(
                    $fechaNacimientoRaw
                );

                $sexo = $this->normalizarGenero($genero);

                $tipoDocumento = $this->normalizarTipoDocumento(
                    $tipoDocumentoExcel
                );

                /*
                |--------------------------------------------------------------------------
                | Datos que todavía no tienen columna propia en Student
                |--------------------------------------------------------------------------
                |
                | Se conservan en observaciones para no perder información durante
                | el traslado entre Pre-matrículas y Matrículas.
                |
                */

                $observacionesImportacion = collect([
                    $direccion !== ''
                        ? "Dirección importada: {$direccion}"
                        : null,

                    $nivel !== ''
                        ? "Nivel importado: {$nivel}"
                        : null,

                    $telefono !== ''
                        ? "Teléfono importado: {$telefono}"
                        : null,

                    $gradoExcel !== ''
                        ? "Grado Excel: {$gradoExcel}"
                        : null,

                    $numeroHermanos !== ''
                        ? "Número de hermanos: {$numeroHermanos}"
                        : null,

                    $condicionIngreso !== ''
                        ? "Condición de ingreso: {$condicionIngreso}"
                        : null,

                    $institucionAnterior !== ''
                        ? "Institución anterior: {$institucionAnterior}"
                        : null,

                    $numeroFormulario !== ''
                        ? "Formulario de origen: {$numeroFormulario}"
                        : null,
                ])
                    ->filter()
                    ->implode("\n");

                $studentExistente = Student::query()
                    ->where('documento', $documento)
                    ->where(
                        'periodo_lectivo_id',
                        $periodoLectivoId
                    )
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | Crear o actualizar estudiante
                |--------------------------------------------------------------------------
                */

                $student = Student::updateOrCreate(
                    [
                        'documento' => $documento,
                        'periodo_lectivo_id' => $periodoLectivoId,
                    ],
                    [
                        'sede_id' => $sedeId,
                        'course_id' => $course?->id,
                        'codigo' => $codigoExcel ?: null,

                        /*
                        | Archivo ampliado: usa M.
                        | Archivo antiguo: conserva TI.
                        */
                        'tipo_documento' => $tipoDocumento,

                        'primer_nombre' => $primerNombre,
                        'segundo_nombre' => $segundoNombre,
                        'primer_apellido' => $primerApellido,
                        'segundo_apellido' => $segundoApellido,

                        'sexo' => $sexo,
                        'fecha_nacimiento' => $fechaNacimiento,

                        /*
                        | Archivo ampliado: usa N, O, Q y P.
                        | Archivo antiguo: conserva el valor obligatorio anterior.
                        */
                        'ciudad_expedicion' =>
                            $ciudadExpedicion !== ''
                                ? $ciudadExpedicion
                                : $this->obligatorio(null),

                        'ciudad_nacimiento' =>
                            $ciudadNacimiento !== ''
                                ? $ciudadNacimiento
                                : $this->obligatorio(null),

                        'eps' =>
                            $eps !== ''
                                ? $eps
                                : $this->obligatorio(null),

                        'rh' =>
                            $rh !== ''
                                ? $rh
                                : $this->obligatorio(null),

                        /*
                        | Este dato aún no viene en ninguno de los dos archivos.
                        */
                        'parentesco_matricula' =>
                            $this->obligatorio(null),

                        'correo' => $correo ?: null,

                        /*
                        | Archivo ampliado: usa R.
                        | Archivo antiguo: conserva el teléfono de F.
                        */
                        'telefono_emergencia' =>
                            $telefonoEmergencia !== ''
                                ? $telefonoEmergencia
                                : ($telefono ?: null),

                        'estado' => 'activo',
                        'fecha_matricula' => now()->toDateString(),
                        'ultimo_grado' => $gradoExcel ?: null,

                        'observaciones' =>
                            $observacionesImportacion ?: null,
                    ]
                );

                if ($studentExistente) {
                    $resultado->actualizados++;
                } else {
                    $resultado->creados++;
                }

                

                /*
                |--------------------------------------------------------------------------
                | Crear o actualizar matrícula solamente cuando ya exista curso
                |--------------------------------------------------------------------------
                |
                | Los estudiantes provenientes de Pre-matrículas pueden importarse sin
                | curso. Su matrícula será creada o completada en el proceso posterior.
                |
                */

                if ($course) {
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
                            'observaciones' =>
                                'Matrícula generada desde importación de estudiantes.',
                        ]
                    );

                    $resultado->matriculas++;
                }
            }

            DB::commit();

            return $resultado;
        } catch (\Throwable $e) {
            DB::rollBack();

            $resultado->agregarError(
                'Error general durante la importación: '
                . $e->getMessage()
            );

            return $resultado;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar género
    |--------------------------------------------------------------------------
    */

    private function normalizarGenero(string $genero): string
    {
        return match (
            mb_strtoupper(
                trim($genero),
                'UTF-8'
            )
        ) {
            'M', 'MASCULINO' => 'Masculino',
            'F', 'FEMENINO' => 'Femenino',
            default => $genero !== ''
                ? $genero
                : 'Sin definir',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar tipo de documento
    |--------------------------------------------------------------------------
    |
    | Permite códigos y nombres completos procedentes de la pre-matrícula.
    |
    */

    private function normalizarTipoDocumento(
        string $tipoDocumento
    ): string {
        return match (
            mb_strtoupper(
                trim($tipoDocumento),
                'UTF-8'
            )
        ) {
            'RC',
            'REGISTRO CIVIL' => 'RC',

            'TI',
            'TARJETA DE IDENTIDAD' => 'TI',

            'CC',
            'CÉDULA DE CIUDADANÍA',
            'CEDULA DE CIUDADANIA' => 'CC',

            'CE',
            'CÉDULA DE EXTRANJERÍA',
            'CEDULA DE EXTRANJERIA' => 'CE',

            'PASAPORTE',
            'PA' => 'PA',

            '',
            'SIN DEFINIR' => 'TI',

            default => mb_strtoupper(
                trim($tipoDocumento),
                'UTF-8'
            ),
        };
    }
}