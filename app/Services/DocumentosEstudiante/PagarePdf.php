<?php

namespace App\Services\DocumentosEstudiante;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Contracts\View\View;

class PagarePdf
{
    public function generar(Student $student): View
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

        $madre = $this->obtenerGuardianPorTipo(
            $student,
            'madre'
        );

        $deudorEconomico = $this->obtenerGuardianPorTipo(
            $student,
            'deudor_economico'
        );

        //$fecha = Carbon::now()->locale('es');

        $campos = [
            /*

            para activar añadir a use use Carbon\Carbon;

             * Encabezado / suscriptores.
             * Solo se llenan datos que ya conocemos.
             
            'Nosotros' => $this->limpiarTexto(
                $padre?->nombre
            ),

            'Nosotros2' => $this->limpiarTexto(
                $madre?->nombre
            ),

            'Nosotros3' => $this->limpiarTexto(
                $deudorEconomico?->nombre
            ),

            
             * Fecha actual de firma.
             
            'DiaFirma' => $fecha->format('d'),

            'MesFirma' => $fecha->translatedFormat('F'),

            
             * El formulario ya muestra "20".
             * Inicialmente usamos los dos últimos dígitos.
             * Si visualmente el campo espera solo uno,
             * lo ajustamos después de la primera prueba.
             
            'AñoFirma' => $fecha->format('y'),
            */
            



            /*
             * Padre.
             */
            'NombrePadre' => $this->limpiarTexto(
                $padre?->nombre
            ),

            'CedulaPadre' => $this->limpiarTexto(
                $padre?->documento
            ),

            'DireccionPadre' => $this->limpiarTexto(
                $padre?->direccion
            ),

            'TelefonoPadre' => $this->limpiarTexto(
                $padre?->telefono
            ),

            /*
             * Madre.
             */
            'NombreMadre' => $this->limpiarTexto(
                $madre?->nombre
            ),

            'CedulaMadre' => $this->limpiarTexto(
                $madre?->documento
            ),

            'DireccionMadre' => $this->limpiarTexto(
                $madre?->direccion
            ),

            'TelefonoMadre' => $this->limpiarTexto(
                $madre?->telefono
            ),

            /*
             * Codeudor = deudor económico.
             */
            'NombreCodeudor' => $this->limpiarTexto(
                $deudorEconomico?->nombre
            ),

            'CedulaCodeudor' => $this->limpiarTexto(
                $deudorEconomico?->documento
            ),

            'DireccionCodeudor' => $this->limpiarTexto(
                $deudorEconomico?->direccion
            ),

            'TelefonoCodeudor' => $this->limpiarTexto(
                $deudorEconomico?->telefono
            ),

            /*
             * Campos económicos y de vencimiento:
             * se dejan vacíos intencionalmente para que
             * puedan completarse manualmente.
             */
            'Por la suma de' => '',
            'Vencimientos' => '',
            'Intereses' => '',
        ];

        return view(
            'documentos.pagare',
            [
                'student' => $student,
                'campos' => $campos,

                'plantillaUrl' => $this->obtenerPlantillaUrl(),

                'nombreArchivo' =>
                    'pagare-'
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
         * Compatibilidad con registros antiguos.
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

    public function obtenerPlantillaUrl(): string
    {
        return route(
            'students.pdf.plantilla',
            [
                'tipo' => 'pagare',
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