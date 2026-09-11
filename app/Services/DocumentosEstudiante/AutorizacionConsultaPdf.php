<?php

namespace App\Services\DocumentosEstudiante;

use App\Models\Guardian;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class AutorizacionConsultaPdf
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

        $acudiente = $this->obtenerGuardianPorTipo(
            $student,
            'acudiente'
        );

        $deudorEconomico = $this->obtenerGuardianPorTipo(
            $student,
            'deudor_economico'
        );

        $madre = $this->obtenerGuardianPorTipo(
            $student,
            'madre'
        );

        /*
         * Para este formato se utiliza primero el acudiente.
         * Si no existe, se usa el padre y si no la madre
         */
        $responsable = $acudiente ?? $padre ?? $madre;

        $fecha = Carbon::now()->locale('es');

        $campos = [
            // ── Ciudad y fecha ───────────────────────────────────────
            'Ciudad' => 'Bogotá',

            'Dia' => $fecha->format('d'),

            'Mes' => $fecha->translatedFormat('F'),

            'Año' => $fecha->format('Y'),

            // ── Padre o acudiente ───────────────────────────────────
            'NombrePadre' => $this->obtenerNombreResponsable(
                $responsable
            ),

            'Cedula' => $this->limpiarTexto(
                $responsable?->documento
            ),

            /*
             * No tenemos teléfono fijo separado.
             */
            'TelFijo' => '',

            'Movil' => $this->limpiarTexto(
                $responsable?->telefono
            ),

            'Direccion' => $this->limpiarTexto(
                $responsable?->direccion
            ),

            'Empresa' => $this->limpiarTexto(
                $responsable?->lugar_trabajo
            ),

            /*
             * No tenemos teléfono de empresa separado.
             */
            'TelEmpresa' => '',

            // ── Deudor económico ────────────────────────────────────
            'NombreDeudor' => $this->limpiarTexto(
                $deudorEconomico?->nombre
            ),

            'Cedula2' => $this->limpiarTexto(
                $deudorEconomico?->documento
            ),

            'TelFijo2' => '',

            'Movil2' => $this->limpiarTexto(
                $deudorEconomico?->telefono
            ),

            'Direccion2' => $this->limpiarTexto(
                $deudorEconomico?->direccion
            ),

            'Empresa2' => $this->limpiarTexto(
                $deudorEconomico?->lugar_trabajo
            ),

            'TelEmpresa2' => '',
        ];

        return view(
            'documentos.autorizacion-consulta',
            [
                'student' => $student,
                'campos' => $campos,

                'plantillaUrl' => $this->obtenerPlantillaUrl(),

                'nombreArchivo' =>
                    'autorizacion-consulta-'
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
         * Primero busca en la relación actual guardian_student.
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
    private function obtenerNombreResponsable(
        ?Guardian $guardian
    ): string {
        $nombre = $this->limpiarTexto(
            $guardian?->nombre
        );

        if ($nombre === '') {
            return '';
        }

        if (mb_strlen($nombre) <= 32) {
            return $nombre;
        }

        $partes = preg_split(
            '/\s+/u',
            $nombre
        );

        if (!$partes || count($partes) < 4) {
            return $nombre;
        }

        array_pop($partes);

        return $this->limpiarTexto(
            implode(' ', $partes)
        );
    }
    public function obtenerPlantillaUrl(): string
    {
        return route(
            'students.pdf.plantilla',
            [
                'tipo' => 'autorizacion_consulta',
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