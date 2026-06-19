<?php

namespace App\Services\Importacion\DTO;

class ResultadoImportacion
{
    public int $filasLeidas = 0;
    public int $creados = 0;
    public int $actualizados = 0;
    public int $omitidos = 0;
    public int $matriculas = 0;

    public array $errores = [];

    public ?string $fecha = null;

    public function __construct()
    {
        $this->fecha = now()->format('d/m/Y h:i A');
    }

    public function totalImportados(): int
    {
        return $this->creados + $this->actualizados;
    }

    public function totalErrores(): int
    {
        return count($this->errores);
    }

    public function tieneErrores(): bool
    {
        return $this->totalErrores() > 0;
    }

    public function agregarError(string $mensaje): void
    {
        $this->errores[] = $mensaje;
    }

    public function toArray(): array
    {
        return [
            'fecha' => $this->fecha,
            'filasLeidas' => $this->filasLeidas,
            'creados' => $this->creados,
            'actualizados' => $this->actualizados,
            'omitidos' => $this->omitidos,
            'matriculas' => $this->matriculas,
            'totalImportados' => $this->totalImportados(),
            'totalErrores' => $this->totalErrores(),
            'errores' => $this->errores,
        ];
    }
}