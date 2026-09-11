<?php

namespace App\Services\DocumentosEstudiante;

use App\Models\Guardian;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class AutorizacionDatosPersonalesPdf
{
    public function generar(Student $student): View
    {
        $student->loadMissing([
            'guardians',
            'course',
            'periodoLectivo',
            'sede',
        ]);

        $acudiente = $this->obtenerGuardianPorTipo(
            $student,
            'acudiente'
        );

        $padre = $this->obtenerGuardianPorTipo(
            $student,
            'padre'
        );

        $madre = $this->obtenerGuardianPorTipo(
            $student,
            'madre'
        );

        /*
         * Prioridad para este formato:
         * Acudiente → Padre → Madre.
         */
        $responsable =
            $acudiente
            ?? $padre
            ?? $madre;

        $fecha = Carbon::now()->locale('es');

        $campos = [
            // Fecha superior
            'Dia' => $fecha->format('d'),

            'Mes' => $fecha->translatedFormat('F'),

            /*
             * La plantilla ya muestra "20".
             * Por eso enviamos solamente los últimos
             * dos dígitos del año.
             */
            'Año' => $fecha->format('y'),

            // Cuerpo del documento
            'Suscrito' => $this->limpiarTexto(
                $responsable?->nombre
            ),

            'Estudiante' => $this->obtenerNombreEstudiante(
                $student
            ),

            // Bloque final
            'Nombre' => $this->limpiarTexto(
                $responsable?->nombre
            ),

            'CC' => $this->limpiarTexto(
                $responsable?->documento
            ),
        ];

        return view(
            'documentos.autorizacion-datos-personales',
            [
                'student' => $student,
                'campos' => $campos,

                'plantillaUrl' => $this->obtenerPlantillaUrl(),

                'nombreArchivo' =>
                    'autorizacion-datos-personales-'
                    . $student->codigo
                    . '.pdf',
            ]
        );
    }

    private function obtenerGuardianPorTipo(
        Student $student,
        string $tipo
    ): ?Guardian {
        /*
         * Relación actual mediante guardian_student.
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
         * Compatibilidad con registros históricos.
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
        return $this->limpiarTexto(
            implode(' ', array_filter([
                $student->primer_nombre,
                $student->segundo_nombre,
                $student->primer_apellido,
                $student->segundo_apellido,
            ]))
        );
    }
    public function obtenerPlantillaUrl(): string
    {
        return route(
            'students.pdf.plantilla',
            [
                'tipo' => 'autorizacion_datos',
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