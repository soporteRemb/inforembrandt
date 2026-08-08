<?php

namespace App\Services\PreMatriculas;

use App\Models\PreMatricula;
use App\Models\PreMatriculaHistorial;
use App\Models\User;

class PreMatriculaHistorialService
{
    public function registrarEvento(
        PreMatricula $preMatricula,
        string $accion,
        ?string $descripcion = null,
        ?User $usuario = null
    ): PreMatriculaHistorial {
        return PreMatriculaHistorial::create([
            'pre_matricula_id' => $preMatricula->id,
            'user_id' => $usuario?->id,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'created_at' => now(),
        ]);
    }

    public function registrarCambio(
        PreMatricula $preMatricula,
        string $campo,
        mixed $valorAnterior,
        mixed $valorNuevo,
        ?User $usuario = null,
        ?string $descripcion = null
    ): ?PreMatriculaHistorial {
        if ($this->normalizarValor($valorAnterior) === $this->normalizarValor($valorNuevo)) {
            return null;
        }

        return PreMatriculaHistorial::create([
            'pre_matricula_id' => $preMatricula->id,
            'user_id' => $usuario?->id,
            'accion' => 'actualizacion',
            'descripcion' => $descripcion,
            'campo' => $campo,
            'valor_anterior' => $this->normalizarValor($valorAnterior),
            'valor_nuevo' => $this->normalizarValor($valorNuevo),
            'created_at' => now(),
        ]);
    }

    private function normalizarValor(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        if (is_bool($valor)) {
            return $valor ? '1' : '0';
        }

        if (is_array($valor) || is_object($valor)) {
            return json_encode(
                $valor,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        return (string) $valor;
    }
}