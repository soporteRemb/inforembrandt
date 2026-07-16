<?php

namespace App\Services\Financiero\Pagos;

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
}