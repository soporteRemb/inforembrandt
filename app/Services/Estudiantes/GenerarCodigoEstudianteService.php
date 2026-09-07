<?php

namespace App\Services\Estudiantes;

use App\Models\PeriodoLectivo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GenerarCodigoEstudianteService
{
    public function generarParaPeriodo(int $periodoLectivoId): string
    {
        $periodo = PeriodoLectivo::query()
            ->findOrFail($periodoLectivoId);

        $anio = $this->obtenerAnioPeriodo($periodo->nombre);

        return $this->generarParaAnio($anio);
    }

    public function generarParaAnio(int $anio): string
    {
        if ($anio < 1000 || $anio > 9999) {
            throw new RuntimeException(
                'El año utilizado para generar el código del estudiante no es válido.'
            );
        }

        return DB::transaction(function () use ($anio) {
            /*
            |--------------------------------------------------------------------------
            | Garantizar que exista la fila de control
            |--------------------------------------------------------------------------
            |
            | insertOrIgnore evita errores si dos procesos intentan crear
            | simultáneamente el consecutivo del mismo año.
            |
            */

            DB::table('consecutivos_estudiantes')
                ->insertOrIgnore([
                    'anio' => $anio,
                    'ultimo_consecutivo' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Bloqueo exclusivo del consecutivo del año
            |--------------------------------------------------------------------------
            */

            $control = DB::table('consecutivos_estudiantes')
                ->where('anio', $anio)
                ->lockForUpdate()
                ->first();

            if (! $control) {
                throw new RuntimeException(
                    "No fue posible obtener el consecutivo de estudiantes para {$anio}."
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Verificar códigos que ya existen
            |--------------------------------------------------------------------------
            |
            | El consecutivo debe compartir una sola secuencia entre:
            |
            | - students.codigo
            | - pre_matriculas.codigo
            |
            | Por eso no dependemos únicamente del valor almacenado en la tabla
            | de control. Si se importó históricamente un código mayor, también
            | será tenido en cuenta.
            |
            */

            $ultimoStudents = $this->obtenerMayorConsecutivo(
                'students',
                $anio
            );

            $ultimoPreMatriculas = $this->obtenerMayorConsecutivo(
                'pre_matriculas',
                $anio
            );

            $ultimoRegistrado = (int) $control->ultimo_consecutivo;

            $ultimoReal = max(
                $ultimoRegistrado,
                $ultimoStudents,
                $ultimoPreMatriculas
            );

            $siguiente = $ultimoReal + 1;

            DB::table('consecutivos_estudiantes')
                ->where('anio', $anio)
                ->update([
                    'ultimo_consecutivo' => $siguiente,
                    'updated_at' => now(),
                ]);

            return (string) $anio . $siguiente;
        });
    }

    private function obtenerMayorConsecutivo(
        string $tabla,
        int $anio
    ): int {
        $codigo = DB::table($tabla)
            ->whereNotNull('codigo')
            ->where('codigo', 'like', $anio . '%')
            ->whereRaw(
                'codigo REGEXP ?',
                ['^' . $anio . '[0-9]+$']
            )
            ->orderByRaw(
                'CAST(SUBSTRING(codigo, 5) AS UNSIGNED) DESC'
            )
            ->value('codigo');

        if (! $codigo) {
            return 0;
        }

        return (int) substr(
            (string) $codigo,
            4
        );
    }

    private function obtenerAnioPeriodo(mixed $nombrePeriodo): int
    {
        $nombre = trim((string) $nombrePeriodo);

        if (! preg_match('/^\d{4}$/', $nombre)) {
            throw new RuntimeException(
                "El periodo lectivo '{$nombre}' no contiene un año válido de cuatro dígitos."
            );
        }

        return (int) $nombre;
    }
}