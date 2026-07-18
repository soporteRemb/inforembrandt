<?php

namespace App\Services\Financiero\Pagos;

use App\Models\FormaPago;
use Illuminate\Support\Collection;

class PagosService
{
    public function __construct(
        protected CarteraEstudianteService $cartera
    ) {
    }

    public function cartera(): CarteraEstudianteService
    {
        return $this->cartera;
    }

    public function obtenerFormasPagoActivas(): Collection
    {
        return FormaPago::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    }
}