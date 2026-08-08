<?php

namespace App\Services\PreMatriculas;

use App\Models\PreMatricula;
use App\Models\User;

class PreMatriculaValidacionService
{
    public function puedeDiligenciar(
        User $usuario,
        PreMatricula $preMatricula
    ): bool {
        return $usuario->is_active
            && $usuario->id === $preMatricula->user_id
            && $preMatricula->estaPendiente()
            && ! $preMatricula->plazoVencido();
    }

    public function tieneResponsableCompleto(array $datos): bool
    {
        return $this->padreCompleto($datos)
            || $this->madreCompleta($datos)
            || $this->acudienteCompleto($datos);
    }

    public function padreCompleto(array $datos): bool
    {
        return $this->grupoCompleto($datos, [
            'padre_nombre',
            'padre_telefono',
            'padre_tipo_documento',
            'padre_documento',
        ]);
    }

    public function madreCompleta(array $datos): bool
    {
        return $this->grupoCompleto($datos, [
            'madre_nombre',
            'madre_telefono',
            'madre_tipo_documento',
            'madre_documento',
        ]);
    }

    public function acudienteCompleto(array $datos): bool
    {
        return $this->grupoCompleto($datos, [
            'acudiente_nombre',
            'acudiente_telefono',
            'acudiente_tipo_documento',
            'acudiente_documento',
        ]);
    }

    private function grupoCompleto(array $datos, array $campos): bool
    {
        foreach ($campos as $campo) {
            if (blank($datos[$campo] ?? null)) {
                return false;
            }
        }

        return true;
    }
}