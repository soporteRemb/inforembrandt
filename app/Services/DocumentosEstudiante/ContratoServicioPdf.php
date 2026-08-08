<?php

namespace App\Services\DocumentosEstudiante;

use App\Models\Guardian;
use App\Models\Student;

use Carbon\Carbon;

use Illuminate\Contracts\View\View;

class ContratoServicioPdf
{
    /**
     * Abre la vista encargada de llenar el contrato AcroForm
     * en el navegador y conservar sus campos editables.
     */
    public function generar(Student $student): View
    {
        $datos = $this->prepararDatos($student);

        return view('documentos.contrato-servicios', [
            'student'       => $student,
            'campos'        => $datos['campos'],
            'plantillaUrl'  => asset(
                'formatos/Contrato de servicios año 2026.pdf'
            ),
            'nombreArchivo' => 'contrato-servicios-'
                . $student->codigo
                . '.pdf',
        ]);
    }

    /**
     * Prepara toda la información que será enviada
     * a los campos AcroForm del contrato.
     */
    private function prepararDatos(Student $student): array
    {
        $student->loadMissing([
            'guardians',
            'course',
            'periodoLectivo',
            'sede',
        ]);

        $responsables = $this->obtenerResponsables($student);
        $fechaFirma   = Carbon::now()->locale('es');

        return [
            'estudiante' => [
                'nombre_completo' => $this->obtenerNombreEstudiante(
                    $student
                ),
                'grado_curso' => $this->obtenerGradoCurso(
                    $student
                ),
            ],

            'padre' => $this->datosGuardian(
                $responsables['padre']
            ),

            'madre' => $this->datosGuardian(
                $responsables['madre']
            ),

            'acudiente' => $this->datosGuardian(
                $responsables['acudiente']
            ),

            'deudor_economico' => $this->datosGuardian(
                $responsables['deudor_economico']
            ),

            'fecha' => [
                'dia'  => $fechaFirma->format('d'),
                'mes'  => $fechaFirma->translatedFormat('F'),
                'anio' => $fechaFirma->format('Y'),
            ],

            'campos' => $this->construirCamposPdf(
                $student,
                $responsables,
                $fechaFirma
            ),
        ];
    }

    /**
     * Obtiene padre, madre, acudiente y deudor económico.
     */
    private function obtenerResponsables(Student $student): array
    {
        return [
            'padre' => $this->obtenerGuardianPorTipo(
                $student,
                'padre'
            ),

            'madre' => $this->obtenerGuardianPorTipo(
                $student,
                'madre'
            ),

            'acudiente' => $this->obtenerGuardianPorTipo(
                $student,
                'acudiente'
            ),

            'deudor_economico' => $this->obtenerGuardianPorTipo(
                $student,
                'deudor_economico'
            ),
        ];
    }

    /**
     * Busca un acudiente según el tipo almacenado
     * en la tabla pivote guardian_student.
     *
     * También revisa guardian.tipo por compatibilidad
     * con registros antiguos.
     */
    private function obtenerGuardianPorTipo(
        Student $student,
        string $tipo
    ): ?Guardian {
        /*
        * Relación actual mediante guardian_student.
        */
        $guardian = $student->guardians->first(
            function (Guardian $guardian) use ($tipo): bool {
                $tipoPivot    = $guardian->pivot?->tipo;
                $tipoGuardian = $guardian->tipo;

                $estadoPivot    = $guardian->pivot?->estado;
                $estadoGuardian = $guardian->estado;

                $tipoCoincide =
                    $tipoPivot === $tipo ||
                    $tipoGuardian === $tipo;

                $estaActivo =
                    empty($estadoPivot) ||
                    $estadoPivot === 'activo';

                if (
                    !empty($estadoGuardian) &&
                    $estadoGuardian !== 'activo'
                ) {
                    $estaActivo = false;
                }

                return $tipoCoincide && $estaActivo;
            }
        );

        if ($guardian) {
            return $guardian;
        }

        /*
        * Compatibilidad con registros antiguos que todavía
        * utilizan guardians.student_id y guardians.tipo.
        */
        return Guardian::query()
            ->where('student_id', $student->id)
            ->where('tipo', $tipo)
            ->where(function ($query) {
                $query
                    ->whereNull('estado')
                    ->orWhere('estado', 'activo');
            })
            ->first();
    }

    /**
     * Construye el arreglo exacto que se enviará
     * a los campos editables del contrato.
     */
    private function construirCamposPdf(
        Student $student,
        array $responsables,
        Carbon $fechaFirma
    ): array {
        $padre           = $responsables['padre'];
        $madre           = $responsables['madre'];
        $acudiente       = $responsables['acudiente'];
        $deudorEconomico = $responsables['deudor_economico'];

        /*
         * En el espacio principal se utiliza primero el padre.
         * Si no existe, se usa el acudiente registrado.
         */
        $contratantePrincipal = $padre ?? $acudiente;

        return [
            // ── Página 1 ────────────────────────────────────────────────
            'Estudiante' => $this->obtenerNombreEstudiante($student),

            'Curso' => $this->limpiarTexto(
                $student->course?->curso
            ),

            'Nombre1' => $this->limpiarTexto(
                $contratantePrincipal?->nombre
            ),

            'Nombre2' => $this->limpiarTexto(
                $madre?->nombre
            ),

            // ── Página 5: fecha actual de firma ─────────────────────────
            'Dias' => $fechaFirma->format('d'),

            'Mes' => $fechaFirma->translatedFormat('F'),

            
            'Año' => $fechaFirma->format('Y')[3],

            // ── Padre o acudiente principal ─────────────────────────────
            'Acudiente' => '',

            'CC No' => $this->limpiarTexto(
                $contratantePrincipal?->documento
            ),

            'Dirección' => $this->limpiarTexto(
                $contratantePrincipal?->direccion
            ),

            'Teléfono' => $this->limpiarTexto(
                $contratantePrincipal?->telefono
            ),

            'Correo' => $this->limpiarTexto(
                $contratantePrincipal?->correo
            ),

            // ── Madre ───────────────────────────────────────────────────
            'NOMBRE DE LA MADRE' => '',

            'CC No_2' => $this->limpiarTexto(
                $madre?->documento
            ),

            'Dirección_2' => $this->limpiarTexto(
                $madre?->direccion
            ),

            'Teléfono_2' => $this->limpiarTexto(
                $madre?->telefono
            ),

            'Correo_2' => $this->limpiarTexto(
                $madre?->correo
            ),

            
            'Representante' => '',

            // ── Codeudor: deudor económico ──────────────────────────────
            /*
             * El campo se llama internamente "Representante",
             * aunque visualmente corresponde al codeudor.
             */

            'CC No_3' => $this->limpiarTexto(
                $deudorEconomico?->documento
            ),

            'Dirección_3' => $this->limpiarTexto(
                $deudorEconomico?->direccion
            ),

            'Teléfono_3' => $this->limpiarTexto(
                $deudorEconomico?->telefono
            ),

            'Correo_3' => $this->limpiarTexto(
                $deudorEconomico?->correo
            ),
        ];
    }

    /**
     * Construye el nombre completo del estudiante.
     */
    private function obtenerNombreEstudiante(
        Student $student
    ): string {
        return $this->limpiarTexto(
            implode(' ', array_filter([
                $student->primer_nombre,
                $student->segundo_nombre,
                $student->primer_apellido,
                $student->segundo_apellido,
            ]))
        );
    }

    /**
     * Construye el texto:
     *
     * Noveno - 901
     */
    private function obtenerGradoCurso(
        Student $student
    ): string {
        if (!$student->course) {
            return '';
        }

        $grado = $this->convertirGradoATexto(
            (string) $student->course->grado
        );

        $curso = $this->limpiarTexto(
            $student->course->curso
        );

        if ($grado === '') {
            return $curso;
        }

        if ($curso === '') {
            return $grado;
        }

        return "{$grado} - {$curso}";
    }

    /**
     * Convierte el grado guardado en la base de datos
     * a su nombre completo.
     */
    private function convertirGradoATexto(
        string $grado
    ): string {
        $gradoNormalizado = mb_strtoupper(
            trim($grado),
            'UTF-8'
        );

        return match ($gradoNormalizado) {
            'P',
            'PJ',
            'PREJARDÍN',
            'PREJARDIN' => 'Prejardín',

            'J',
            'JARDÍN',
            'JARDIN' => 'Jardín',

            'T',
            'TRANSICIÓN',
            'TRANSICION' => 'Transición',

            '1',
            '01',
            'PRIMERO' => 'Primero',

            '2',
            '02',
            'SEGUNDO' => 'Segundo',

            '3',
            '03',
            'TERCERO' => 'Tercero',

            '4',
            '04',
            'CUARTO' => 'Cuarto',

            '5',
            '05',
            'QUINTO' => 'Quinto',

            '6',
            '06',
            'SEXTO' => 'Sexto',

            '7',
            '07',
            'SÉPTIMO',
            'SEPTIMO' => 'Séptimo',

            '8',
            '08',
            'OCTAVO' => 'Octavo',

            '9',
            '09',
            'NOVENO' => 'Noveno',

            '10',
            'DÉCIMO',
            'DECIMO' => 'Décimo',

            '11',
            'UNDÉCIMO',
            'UNDECIMO',
            'ONCE' => 'Undécimo',

            default => mb_convert_case(
                trim($grado),
                MB_CASE_TITLE,
                'UTF-8'
            ),
        };
    }

    /**
     * Devuelve los datos de un acudiente con una
     * estructura uniforme.
     */
    private function datosGuardian(
        ?Guardian $guardian
    ): array {
        return [
            'nombre' => $this->limpiarTexto(
                $guardian?->nombre
            ),

            'tipo_documento' => $this->limpiarTexto(
                $guardian?->tipo_documento
            ),

            'documento' => $this->limpiarTexto(
                $guardian?->documento
            ),

            'direccion' => $this->limpiarTexto(
                $guardian?->direccion
            ),

            'telefono' => $this->limpiarTexto(
                $guardian?->telefono
            ),

            'correo' => $this->limpiarTexto(
                $guardian?->correo
            ),

            'lugar_trabajo' => $this->limpiarTexto(
                $guardian?->lugar_trabajo
            ),
        ];
    }

    /**
     * Convierte valores nulos en texto vacío y elimina
     * espacios repetidos.
     */
    private function limpiarTexto(
        mixed $valor
    ): string {
        $texto = (string) ($valor ?? '');

        return trim(
            preg_replace('/\s+/u', ' ', $texto) ?? ''
        );
    }
}