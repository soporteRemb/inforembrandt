<?php

namespace App\Services\Importacion\Estudiantes;

use App\Models\Course;
use App\Models\Matricula;
use App\Models\PeriodoLectivo;
use App\Models\Student;

use App\Services\DocumentosEstudiante\IncorporarDocumentosPreMatriculaService;
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

    /*
    |--------------------------------------------------------------------------
    | Periodo destino para inter-operabilidad con Pre-matrículas
    |--------------------------------------------------------------------------
    |
    | Se utiliza únicamente para localizar el último registro histórico
    | anterior del estudiante. Nunca se determina el periodo por su ID.
    |
    */

    $periodoLectivoDestino = $esArchivoPreMatricula
        ? PeriodoLectivo::query()
            ->select('id', 'fecha_inicio')
            ->find($periodoLectivoId)
        : null;

    /*
    |--------------------------------------------------------------------------
    | Documentos provenientes de pre-matrícula
    |--------------------------------------------------------------------------
    |
    | Se guardan solamente los ID de los estudiantes procesados.
    | Los archivos se incorporarán DESPUÉS del commit para no mezclar
    | operaciones físicas de archivos con la transacción de base de datos.
    |
    */

    $estudiantesParaIncorporarDocumentos = [];

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
                | Buscar estudiante histórico por documento
                |--------------------------------------------------------------------------
                |
                | El documento identifica a la persona.
                | El código institucional debe conservarse durante toda su permanencia
                | en el colegio, aunque cambie de periodo lectivo.
                |
                */

                $studentExistente = Student::query()
                    ->where('documento', $documento)
                    ->where('sede_id', $sedeId)
                    ->where('periodo_lectivo_id', $periodoLectivoId)
                    ->first();

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

                /*
                |--------------------------------------------------------------------------
                | Curso indicado expresamente en el Excel
                |--------------------------------------------------------------------------
                |
                | Este comportamiento corresponde a los formatos normales de estudiantes
                | y se conserva exactamente: si el curso indicado no existe, la fila
                | presenta inconsistencia.
                |
                */

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
                | Curso automático desde Pre-matrícula
                |--------------------------------------------------------------------------
                |
                | La columna J contiene el grado al que aspira el estudiante.
                |
                | Estudiante antiguo:
                | conserva la sección de su último curso anterior (01, 02, etc.).
                |
                | Estudiante nuevo:
                | comienza intentando la sección 01.
                |
                | Si el curso propuesto no existe, el estudiante se importa sin curso
                | para que el colegio pueda asignarlo posteriormente.
                |
                | Si el estudiante ya tiene curso en el periodo destino, se conserva:
                | puede haber sido modificado manualmente por el colegio.
                |
                */

                elseif (
                    $esArchivoPreMatricula
                    && ! $studentExistente?->course_id
                    && $gradoExcel !== ''
                ) {
                    $gradoDestino = $this->normalizarGradoPreMatricula(
                        $gradoExcel
                    );

                    $seccionPreferida = '01';

                    /*
                    |--------------------------------------------------------------------------
                    | Buscar el último estudiante histórico anterior
                    |--------------------------------------------------------------------------
                    */

                    $studentHistorico = null;

                    if ($periodoLectivoDestino?->fecha_inicio) {
                        $studentHistorico = Student::query()
                            ->select('students.*')
                            ->with([
                                'course:id,grado,curso',
                            ])
                            ->join(
                                'periodos_lectivos as periodo_historico',
                                'periodo_historico.id',
                                '=',
                                'students.periodo_lectivo_id'
                            )
                            ->where(
                                'students.documento',
                                $documento
                            )
                            ->where(
                                'students.sede_id',
                                $sedeId
                            )
                            ->where(
                                'students.periodo_lectivo_id',
                                '<>',
                                $periodoLectivoId
                            )
                            ->where(
                                'periodo_historico.fecha_inicio',
                                '<',
                                $periodoLectivoDestino->fecha_inicio
                            )
                            ->orderByDesc(
                                'periodo_historico.fecha_inicio'
                            )
                            ->first();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Conservar sección histórica
                    |--------------------------------------------------------------------------
                    |
                    | No utilizamos nombres o descripciones del grado.
                    |
                    | Ejemplos:
                    | grado 9  + curso 902  -> sección 02
                    | grado 10 + curso 1001 -> sección 01
                    | grado J  + curso J01  -> sección 01
                    |
                    */

                    if ($studentHistorico?->course) {
                        $gradoHistorico = mb_strtoupper(
                            trim(
                                (string) $studentHistorico->course->grado
                            ),
                            'UTF-8'
                        );

                        $cursoHistorico = mb_strtoupper(
                            trim(
                                (string) $studentHistorico->course->curso
                            ),
                            'UTF-8'
                        );

                        if (
                            $gradoHistorico !== ''
                            && str_starts_with(
                                $cursoHistorico,
                                $gradoHistorico
                            )
                        ) {
                            $seccionHistorica = substr(
                                $cursoHistorico,
                                strlen($gradoHistorico)
                            );

                            if ($seccionHistorica !== '') {
                                $seccionPreferida =
                                    $seccionHistorica;
                            }
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Buscar curso propuesto real
                    |--------------------------------------------------------------------------
                    */

                    $cursoPreferido =
                        $gradoDestino . $seccionPreferida;

                    $course = Course::query()
                        ->where('sede_id', $sedeId)
                        ->where(
                            'periodo_lectivo_id',
                            $periodoLectivoId
                        )
                        ->where('grado', $gradoDestino)
                        ->where('curso', $cursoPreferido)
                        ->first();
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
                /*
                |--------------------------------------------------------------------------
                | Datos comunes del estudiante
                |--------------------------------------------------------------------------
                */

                $datosStudent = [
                    'sede_id' => $sedeId,
                    'periodo_lectivo_id' => $periodoLectivoId,

                    'course_id' =>
                        $course?->id
                        ?? $studentExistente?->course_id,

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

                    'parentesco_matricula' =>
                        $this->obligatorio(null),

                    'correo' => $correo ?: null,

                    'telefono_emergencia' =>
                        $telefonoEmergencia !== ''
                            ? $telefonoEmergencia
                            : ($telefono ?: null),

                    'estado' => 'activo',
                    'fecha_matricula' => now()->toDateString(),
                    'ultimo_grado' => $gradoExcel ?: null,

                    'observaciones' =>
                        $observacionesImportacion ?: null,
                ];

                /*
                |--------------------------------------------------------------------------
                | Estudiante existente
                |--------------------------------------------------------------------------
                |
                | Actualizamos los datos, pero NUNCA modificamos su código histórico.
                |
                */

                if ($studentExistente) {

                    $studentExistente->fill(
                        $datosStudent
                    );

                    $studentExistente->save();

                    $student = $studentExistente;

                    $resultado->actualizados++;

                /*
                |--------------------------------------------------------------------------
                | Estudiante nuevo
                |--------------------------------------------------------------------------
                |
                | Si el formato histórico trae código, se respeta.
                | Si viene desde Pre-matrícula sin código, no enviamos código y el modelo
                | Student genera automáticamente el consecutivo del año.
                |
                */

                } else {

                    $datosNuevoStudent = array_merge(
                        [
                            'documento' => $documento,
                        ],
                        $datosStudent
                    );

                    if ($codigoExcel !== '') {
                        $datosNuevoStudent['codigo'] =
                            $codigoExcel;
                    }

                    $student = Student::create(
                        $datosNuevoStudent
                    );

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

                /*
                |--------------------------------------------------------------------------
                | Preparar incorporación de documentos
                |--------------------------------------------------------------------------
                |
                | Solo aplica al Excel exportado desde Pre-matrículas.
                | Los Excel históricos continúan funcionando exactamente como antes.
                |
                */

                if ($esArchivoPreMatricula) {
                    $estudiantesParaIncorporarDocumentos[$student->id] =
                        $student->id;
                }
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Incorporar documentos de pre-matrícula
            |--------------------------------------------------------------------------
            |
            | Se realiza después del commit:
            |
            | - la importación de estudiantes ya quedó confirmada;
            | - no alteramos la transacción histórica;
            | - un problema con un archivo no revierte estudiantes ni matrículas;
            | - DocumentoEstudianteService mantiene la operación idempotente.
            |
            */

            if (
                $esArchivoPreMatricula
                && $estudiantesParaIncorporarDocumentos !== []
            ) {
                $servicioDocumentos = app(
                    IncorporarDocumentosPreMatriculaService::class
                );

                foreach (
                    $estudiantesParaIncorporarDocumentos
                    as $studentId
                ) {
                    try {
                        $studentDocumentos = Student::find(
                            $studentId
                        );

                        if (! $studentDocumentos) {
                            continue;
                        }

                        $servicioDocumentos->ejecutar(
                            $studentDocumentos
                        );
                    } catch (\Throwable $e) {
                        $resultado->agregarError(
                            'El estudiante fue importado correctamente, '
                            . 'pero no fue posible incorporar todos sus documentos '
                            . "al expediente. Estudiante ID {$studentId}: "
                            . $e->getMessage()
                        );
                    }
                }
            }

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
        | Normalizar grado proveniente de Pre-matrícula
        |--------------------------------------------------------------------------
        |
        | La pre-matrícula almacena el nombre visible del grado, mientras Course
        | utiliza un código estructural. Esta conversión solamente se aplica al
        | archivo identificado como proveniente de Pre-matrículas.
        |
        */

        private function normalizarGradoPreMatricula(
            string $grado
        ): string {
            $gradoNormalizado = mb_strtoupper(
                trim($grado),
                'UTF-8'
            );

            return match ($gradoNormalizado) {
                'PREESCOLAR',
                'PREJARDÍN',
                'PREJARDIN',
                'PJ',
                'P' => 'P',

                'JARDÍN',
                'JARDIN',
                'J' => 'J',

                'TRANSICIÓN',
                'TRANSICION',
                'T' => 'T',

                'PRIMERO',
                '1',
                '01' => '1',

                'SEGUNDO',
                '2',
                '02' => '2',

                'TERCERO',
                '3',
                '03' => '3',

                'CUARTO',
                '4',
                '04' => '4',

                'QUINTO',
                '5',
                '05' => '5',

                'SEXTO',
                '6',
                '06' => '6',

                'SÉPTIMO',
                'SEPTIMO',
                '7',
                '07' => '7',

                'OCTAVO',
                '8',
                '08' => '8',

                'NOVENO',
                '9',
                '09' => '9',

                'DÉCIMO',
                'DECIMO',
                '10' => '10',

                'UNDÉCIMO',
                'UNDECIMO',
                'ONCE',
                '11' => '11',

                default => $gradoNormalizado,
            };
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