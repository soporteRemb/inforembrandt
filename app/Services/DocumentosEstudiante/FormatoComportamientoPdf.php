<?php

namespace App\Services\DocumentosEstudiante;

use App\Models\Guardian;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class FormatoComportamientoPdf
{
    public function generar(Student $student): View
    {
        $datos = $this->prepararDatos($student);

        return view(
            'documentos.formato-comportamiento',
            [
                'student' => $student,
                'campos' => $datos['campos'],

                'plantillaUrl' => $this->obtenerPlantillaUrl(),

                'nombreArchivo' =>
                    'formato-comportamiento-'
                    . $student->codigo
                    . '.pdf',
            ]
        );
    }

    /**
     * Prepara toda la información necesaria para llenar
     * el AcroForm del formato de comportamiento.
     *
     * Este método también es utilizado por el paquete
     * de documentos del estudiante.
     */
    public function prepararDatos(Student $student): array
    {
        $student->loadMissing([
            'guardians',
            'course',
            'periodoLectivo',
            'sede',
        ]);

        $padre = $this->obtenerGuardianPorTipo(
            $student,
            'padre'
        );

        $acudiente = $this->obtenerGuardianPorTipo(
            $student,
            'acudiente'
        );

        $madre = $this->obtenerGuardianPorTipo(
            $student,
            'madre'
        );

        /*
        * Para este formato se utiliza primero el acudiente.
        * Si no existe, se usa el padre registrado
        * o finalmente la madre.
        */
        $responsable = $acudiente ?? $padre ?? $madre;

        return [
            'campos' => [
                'Nombre' => $this->limpiarTexto(
                    $responsable?->nombre
                ),

                'Cedula' => $this->limpiarTexto(
                    $responsable?->documento
                ),

                'Estudiante' => $this->obtenerNombreEstudiante(
                    $student
                ),

                'DocumentoEstudiante' => $this->limpiarTexto(
                    $student->documento
                ),

                'Grado' => $this->limpiarTexto(
                    $student->course?->curso
                ),

                'Fecha' => Carbon::now()
                    ->format('d/m/Y'),

                // Se reserva para firma manuscrita/electrónica.
                'Firma delPadre o Tutor' => '',
            ],
        ];
    }

    private function obtenerGuardianPorTipo(
        Student $student,
        string $tipo
    ): ?Guardian {
        /*
        * 1. Buscar primero mediante la relación actual
        *    guardian_student.
        */
        $guardian = $student->guardians()
            ->wherePivot('tipo', $tipo)
            ->where(function ($query) {
                $query
                    ->whereNull('guardians.estado')
                    ->orWhere('guardians.estado', 'activo');
            })
            ->first();

        if ($guardian) {
            return $guardian;
        }

        /*
        * 2. Compatibilidad con registros antiguos
        *    guardados directamente en guardians.
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

    private function obtenerNombreEstudiante(
        Student $student
    ): string {
        $nombreCompleto = $this->limpiarTexto(
            implode(' ', array_filter([
                $student->primer_nombre,
                $student->segundo_nombre,
                $student->primer_apellido,
                $student->segundo_apellido,
            ]))
        );

        /*
        * Si el nombre completo cabe normalmente,
        * conservar los dos apellidos.
        */
        if (mb_strlen($nombreCompleto) <= 36) {
            return $nombreCompleto;
        }

        /*
        * Para nombres excepcionalmente largos,
        * omitir únicamente el segundo apellido.
        */
        return $this->limpiarTexto(
            implode(' ', array_filter([
                $student->primer_nombre,
                $student->segundo_nombre,
                $student->primer_apellido,
            ]))
        );
    }

    public function obtenerPlantillaUrl(): string
    {
        return route(
            'students.pdf.plantilla',
            [
                'tipo' => 'comportamiento',
            ]
        );
    }

    private function limpiarTexto(
        mixed $valor
    ): string {
        $texto = (string) ($valor ?? '');

        return trim(
            preg_replace('/\s+/u', ' ', $texto) ?? ''
        );
    }
}