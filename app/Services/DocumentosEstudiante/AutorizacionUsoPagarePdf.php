<?php

namespace App\Services\DocumentosEstudiante;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;

class AutorizacionUsoPagarePdf
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
        $fecha = Carbon::now()->locale('es');

        $campos = [
            /*
            * Fecha de elaboración del documento.
            */
            'Dia' => $fecha->format('d'),

            'Mes' => $fecha->translatedFormat('F'),

            'Año' => $fecha->format('y'),

            /*
             * Cuerpo del documento.
             */
            'Nombre1' => $this->obtenerNombreResponsable(
                $padre
            ),

            'Nombre2' => $this->obtenerNombreResponsable(
                $madre
            ),

            'menores' => $this->obtenerNombreEstudiante(
                $student
            ),

            /*
             * Por ahora se conserva vacío hasta
             * comprobar visualmente su función.
             */
            'Autorizar' => '',

            /*
             * Segunda fecha del documento.
             * También permanece vacía.
             */
            'Dias' => '',
            'Delmes' => '',
            'DelAño' => '',

            /*
             * Datos del padre.
             */
            'Nombre' => $this->obtenerNombreResponsable(
                $padre
            ),

            'Cedula' => $this->limpiarTexto(
                $padre?->documento
            ),

            'Dirección' => $this->limpiarTexto(
                $padre?->direccion
            ),

            'Tel' => $this->limpiarTexto(
                $padre?->telefono
            ),

            'Correo 1' => $this->limpiarTexto(
                $padre?->correo
            ),

            /*
             * Datos de la madre.
             */
            'NOMBRE_2' => $this->obtenerNombreResponsable(
                $madre
            ),

            'Cedula_2' => $this->limpiarTexto(
                $madre?->documento
            ),

            'Dirección_2' => $this->limpiarTexto(
                $madre?->direccion
            ),

            'Tel_2' => $this->limpiarTexto(
                $madre?->telefono
            ),

            'Correo 2' => $this->limpiarTexto(
                $madre?->correo
            ),
        ];

        return view(
            'documentos.autorizacion-uso-pagare',
            [
                'student' => $student,
                'campos' => $campos,

                'plantillaUrl' => $this->obtenerPlantillaUrl(),

                'nombreArchivo' =>
                    'autorizacion-uso-pagare-'
                    . $student->codigo
                    . '.pdf',
            ]
        );
    }

    private function obtenerGuardianPorTipo(
        Student $student,
        string $tipo
    ): ?Guardian {
        $guardian = $student->guardians->first(
            function (Guardian $guardian) use ($tipo): bool {
                $tipoPivot = $guardian->pivot?->tipo;
                $tipoGuardian = $guardian->tipo;

                $estadoPivot = $guardian->pivot?->estado;
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
        $nombreCompleto = $this->limpiarTexto(
            implode(' ', array_filter([
                $student->primer_nombre,
                $student->segundo_nombre,
                $student->primer_apellido,
                $student->segundo_apellido,
            ]))
        );

        /*
        * El campo "menores" del GA-F13 es muy estrecho.
        */
        if (mb_strlen($nombreCompleto) <= 22) {
            return $nombreCompleto;
        }

        $nombreSinSegundoApellido = $this->limpiarTexto(
            implode(' ', array_filter([
                $student->primer_nombre,
                $student->segundo_nombre,
                $student->primer_apellido,
            ]))
        );

        if (mb_strlen($nombreSinSegundoApellido) <= 28) {
            return $nombreSinSegundoApellido;
        }

        return $this->limpiarTexto(
            implode(' ', array_filter([
                $student->primer_nombre,
                $student->primer_apellido,
            ]))
        );
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

        /*
        * Los nombres normales se conservan completos.
        */
        if (mb_strlen($nombre) <= 32) {
            return $nombre;
        }

        /*
        * Para nombres excepcionalmente largos
        * se omite únicamente el último apellido.
        */
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
                'tipo' => 'autorizacion_pagare',
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