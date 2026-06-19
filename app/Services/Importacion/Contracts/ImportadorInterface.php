<?php

namespace App\Services\Importacion\Contracts;

use App\Services\Importacion\DTO\ResultadoImportacion;

interface ImportadorInterface
{
    public function importar(string $path, array $opciones = []): ResultadoImportacion;
}