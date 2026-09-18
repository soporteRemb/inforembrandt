<?php

namespace App\Services\FirmaElectronica;

use App\Models\Guardian;
use App\Models\Student;
use RuntimeException;

class PrepararFirmantesService
{
    public function acudiente(Student $student): Guardian
    {
        $guardian = $student->guardians()
            ->wherePivot('tipo', 'acudiente')
            ->where(function ($query) {
                $query
                    ->whereNull('guardians.estado')
                    ->orWhere(
                        'guardians.estado',
                        'activo'
                    );
            })
            ->first();

        if (! $guardian) {
            $guardian = Guardian::query()
                ->where(
                    'student_id',
                    $student->id
                )
                ->where(
                    'tipo',
                    'acudiente'
                )
                ->where(function ($query) {
                    $query
                        ->whereNull('estado')
                        ->orWhere(
                            'estado',
                            'activo'
                        );
                })
                ->first();
        }

        if (! $guardian) {
            throw new RuntimeException(
                'El estudiante no tiene un acudiente activo registrado.'
            );
        }

        return $guardian;
    }

    public function datosAcudienteParaFirma(
        Student $student
    ): array {
        $guardian = $this->acudiente($student);

        $nombre = $this->limpiar(
            $guardian->nombre
        );

        $documento = $this->limpiar(
            $guardian->documento
        );

        $correo = mb_strtolower(
            $this->limpiar(
                $guardian->correo
            )
        );

        $telefono = $this->normalizarTelefonoColombia(
            $guardian->telefono
        );

        return [
            'guardian_id' => $guardian->id,
            'nombre' => $nombre,
            'documento' => $documento,

            /*
            * Solo precargamos el correo si tiene
            * un formato válido.
            */
            'correo' => (
                $correo !== ''
                && filter_var(
                    $correo,
                    FILTER_VALIDATE_EMAIL
                )
            )
                ? $correo
                : '',

            /*
            * Para el formulario mostramos el celular
            * colombiano sin +57.
            *
            * Si el dato histórico es ambiguo o inválido,
            * se deja vacío para que el funcionario
            * indique el número correcto para este envío.
            */
            'telefono' => $telefono
                ? preg_replace(
                    '/^\+57/',
                    '',
                    $telefono
                )
                : '',
        ];
    }

    public function prepararAcudiente(
        Student $student
    ): array {
        $guardian = $this->acudiente($student);

        $nombre = $this->limpiar(
            $guardian->nombre
        );

        $correo = mb_strtolower(
            $this->limpiar(
                $guardian->correo
            )
        );

        $telefono = $this->normalizarTelefonoColombia(
            $guardian->telefono
        );

        $documento = $this->limpiar(
            $guardian->documento
        );

        if ($nombre === '') {
            throw new RuntimeException(
                'El acudiente no tiene nombre registrado.'
            );
        }

        if (
            $correo === ''
            || ! filter_var(
                $correo,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new RuntimeException(
                'El acudiente no tiene un correo electrónico válido.'
            );
        }

        if ($telefono === null) {
            throw new RuntimeException(
                'El acudiente no tiene un número de celular válido.'
            );
        }

        if ($documento === '') {
            throw new RuntimeException(
                'El acudiente no tiene número de identificación registrado.'
            );
        }

        return [
            'guardian_id' => $guardian->id,

            'click_and_sign' => [
                'phone' => $telefono,
                'email' => $correo,
                'name' => $nombre,
                'id_number' => $documento,
            ],
        ];
    }

    public function prepararAcudienteParaEnvio(
        Student $student,
        string $correo,
        string $telefono
    ): array {
        $guardian = $this->acudiente($student);

        /*
        * La identidad siempre se obtiene del registro
        * del acudiente en el servidor.
        */
        $nombre = $this->limpiar(
            $guardian->nombre
        );

        $documento = $this->limpiar(
            $guardian->documento
        );

        /*
        * Correo y celular corresponden exclusivamente
        * a esta solicitud de firma.
        *
        * No se modifican los datos del Guardian.
        */
        $correo = mb_strtolower(
            $this->limpiar($correo)
        );

        $telefono = $this->normalizarTelefonoColombia(
            $telefono
        );

        if ($nombre === '') {
            throw new RuntimeException(
                'El acudiente no tiene nombre registrado.'
            );
        }

        if ($documento === '') {
            throw new RuntimeException(
                'El acudiente no tiene número de identificación registrado.'
            );
        }

        if (
            $correo === ''
            || ! filter_var(
                $correo,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new RuntimeException(
                'El correo electrónico indicado para la firma no es válido.'
            );
        }

        if ($telefono === null) {
            throw new RuntimeException(
                'El celular indicado para la firma no es válido.'
            );
        }

        return [
            'guardian_id' => $guardian->id,

            'click_and_sign' => [
                'phone' => $telefono,
                'email' => $correo,
                'name' => $nombre,
                'id_number' => $documento,
            ],
        ];
    }

    private function normalizarTelefonoColombia(
        mixed $telefono
    ): ?string {
        $numero = preg_replace(
            '/\D+/',
            '',
            (string) ($telefono ?? '')
        );

        if (! $numero) {
            return null;
        }

        /*
         * Celular colombiano almacenado localmente:
         * 3001234567 -> +573001234567
         */
        if (
            strlen($numero) === 10
            && str_starts_with($numero, '3')
        ) {
            return '+57' . $numero;
        }

        /*
         * Ya incluye indicativo colombiano:
         * 573001234567 -> +573001234567
         */
        if (
            strlen($numero) === 12
            && str_starts_with($numero, '57')
        ) {
            return '+' . $numero;
        }

        return null;
    }

    private function limpiar(
        mixed $valor
    ): string {
        $texto = (string) ($valor ?? '');

        return trim(
            preg_replace(
                '/\s+/u',
                ' ',
                $texto
            ) ?? ''
        );
    }
}