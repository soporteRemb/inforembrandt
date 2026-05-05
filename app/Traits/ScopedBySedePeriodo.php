<?php

namespace App\Traits;

use App\Models\PeriodoLectivo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Para Resources cuya tabla tiene sede_id + periodo_lectivo_id.
 * Filtra automáticamente por la sede y año de la sesión.
 * Si no existe el periodo para esa sede/año, devuelve cero resultados.
 */
trait ScopedBySedePeriodo
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $sedeId = session('sede_id');
        $anio   = session('anio', date('Y'));

        if (!$sedeId) {
            return $query;
        }

        $periodo = PeriodoLectivo::where('sede_id', $sedeId)
            ->where('nombre', $anio)
            ->first();

        if (!$periodo) {
            // No hay periodo para esta sede/año → cero resultados (no se filtra de más)
            return $query->whereRaw('0 = 1');
        }

        return $query
            ->where('sede_id', $sedeId)
            ->where('periodo_lectivo_id', $periodo->id);
    }
}
