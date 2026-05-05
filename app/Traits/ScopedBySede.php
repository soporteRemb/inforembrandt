<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Para Resources cuya tabla tiene columna sede_id.
 * Filtra automáticamente por la sede de la sesión.
 */
trait ScopedBySede
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $sedeId = session('sede_id');

        if ($sedeId) {
            $query->where('sede_id', $sedeId);
        }

        return $query;
    }
}
