<?php

namespace App\Services\PreMatriculas;

use App\Models\PreMatricula;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PreMatriculaDashboardService
{
    public function obtenerResumen(
        int $sedeId,
        int $periodoLectivoId
    ): array {
        $consulta = $this->consultaContexto(
            $sedeId,
            $periodoLectivoId
        );

        $total = (clone $consulta)->count();

        $pendientes = (clone $consulta)
            ->where('estado', 'pendiente')
            ->count();

        $completadas = (clone $consulta)
            ->where('estado', 'completado')
            ->count();

        $vencidas = (clone $consulta)
            ->where('estado', 'vencido')
            ->count();

        $hombres = (clone $consulta)
            ->whereRaw('LOWER(genero) = ?', ['masculino'])
            ->count();

        $mujeres = (clone $consulta)
            ->whereRaw('LOWER(genero) = ?', ['femenino'])
            ->count();

        $completadasHoy = (clone $consulta)
            ->where('estado', 'completado')
            ->whereDate('fecha_envio', today())
            ->count();

        $completadasSemana = (clone $consulta)
            ->where('estado', 'completado')
            ->whereBetween('fecha_envio', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->count();

        return [
            'total' => $total,
            'pendientes' => $pendientes,
            'completadas' => $completadas,
            'vencidas' => $vencidas,
            'hombres' => $hombres,
            'mujeres' => $mujeres,

            'grado_mas_solicitado' =>
                $this->gradoMasSolicitado(
                    $sedeId,
                    $periodoLectivoId
                ) ?? [
                    'grado' => 'Sin información',
                    'total' => 0,
                ],

            'grado_menos_solicitado' =>
                $this->gradoMenosSolicitado(
                    $sedeId,
                    $periodoLectivoId
                ) ?? [
                    'grado' => 'Sin información',
                    'total' => 0,
                ],

            'completadas_hoy' => $completadasHoy,
            'completadas_semana' => $completadasSemana,
        ];
    }

    public function obtenerListado(
        int $sedeId,
        int $periodoLectivoId,
        ?string $buscar = null,
        ?string $estado = null,
        ?string $grado = null,
        ?string $fechaDesde = null,
        ?string $fechaHasta = null
    ): Collection {
        $consulta = $this->consultaContexto(
            $sedeId,
            $periodoLectivoId
        )->with([
            'usuario',
            'eps',
        ]);

        if (filled($buscar)) {
            $termino = trim($buscar);

            $consulta->where(function (Builder $query) use ($termino) {
                $query
                    ->where(
                        'numero_formulario',
                        'like',
                        "%{$termino}%"
                    )
                    ->orWhere(
                        'documento',
                        'like',
                        "%{$termino}%"
                    )
                    ->orWhere(
                        'nombres',
                        'like',
                        "%{$termino}%"
                    )
                    ->orWhere(
                        'apellidos',
                        'like',
                        "%{$termino}%"
                    )
                    ->orWhereRaw(
                        "CONCAT_WS(' ', nombres, apellidos) LIKE ?",
                        ["%{$termino}%"]
                    );
            });
        }

        if (filled($estado)) {
            $consulta->where('estado', $estado);
        }

        if (filled($grado)) {
            $consulta->where('grado_aspira', $grado);
        }

        if (filled($fechaDesde)) {
            $consulta->whereDate(
                'fecha_envio',
                '>=',
                $fechaDesde
            );
        }

        if (filled($fechaHasta)) {
            $consulta->whereDate(
                'fecha_envio',
                '<=',
                $fechaHasta
            );
        }

        return $consulta
            ->orderByRaw("
                CASE estado
                    WHEN 'completado' THEN 1
                    WHEN 'pendiente' THEN 2
                    WHEN 'vencido' THEN 3
                    ELSE 4
                END
            ")
            ->orderByRaw("
                CASE
                    WHEN estado = 'completado'
                        THEN COALESCE(fecha_envio, fecha_habilitacion)
                    ELSE fecha_habilitacion
                END DESC
            ")
            ->orderByDesc('id')
            ->get();
    }

    public function gradoMasSolicitado(
        int $sedeId,
        int $periodoLectivoId
    ): ?array {
        $fila = $this->consultaContexto(
            $sedeId,
            $periodoLectivoId
        )
            ->whereNotNull('grado_aspira')
            ->selectRaw('grado_aspira, COUNT(*) AS total')
            ->groupBy('grado_aspira')
            ->orderByDesc('total')
            ->orderBy('grado_aspira')
            ->first();

        return $fila
            ? [
                'grado' => $fila->grado_aspira,
                'total' => (int) $fila->total,
            ]
            : null;
    }

    public function gradoMenosSolicitado(
        int $sedeId,
        int $periodoLectivoId
    ): ?array {
        $fila = $this->consultaContexto(
            $sedeId,
            $periodoLectivoId
        )
            ->whereNotNull('grado_aspira')
            ->selectRaw('grado_aspira, COUNT(*) AS total')
            ->groupBy('grado_aspira')
            ->orderBy('total')
            ->orderBy('grado_aspira')
            ->first();

        return $fila
            ? [
                'grado' => $fila->grado_aspira,
                'total' => (int) $fila->total,
            ]
            : null;
    }

    public function contarPorEstado(
        int $sedeId,
        int $periodoLectivoId,
        string $estado
    ): int {
        return $this->consultaContexto(
            $sedeId,
            $periodoLectivoId
        )
            ->where('estado', $estado)
            ->count();
    }

    private function consultaContexto(
        int $sedeId,
        int $periodoLectivoId
    ): Builder {
        return PreMatricula::query()
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodoLectivoId);
    }
}