<?php

namespace App\Services\Boletines;

class BoletinPaginatorService
{
    private const INICIO_TABLA_Y = 104;
    private const ALTO_BLOQUE = 12.5;

    // Hoja base: solo tabla académica.
    private const LIMITE_HOJA_BASE = 240;

    // Hoja final: reserva espacio para observaciones, convenciones y firmas.
    private const LIMITE_HOJA_FINAL = 210;

    public function paginar(array $data): array
    {
        $asignaturas = collect($data['asignaturas'] ?? [])->values();

        if ($asignaturas->isEmpty()) {
            return [
                [
                    'asignaturas' => [],
                    'es_final' => true,
                ],
            ];
        }

        $paginas = [];
        $pendientes = $asignaturas;

        while ($pendientes->isNotEmpty()) {
            $capacidadFinal = $this->capacidadPorLimite(self::LIMITE_HOJA_FINAL);

            if ($pendientes->count() <= $capacidadFinal) {
                $paginas[] = [
                    'asignaturas' => $pendientes->values()->toArray(),
                    'es_final' => true,
                ];

                break;
            }

            $capacidadBase = $this->capacidadPorLimite(self::LIMITE_HOJA_BASE);

            $paginas[] = [
                'asignaturas' => $pendientes->take($capacidadBase)->values()->toArray(),
                'es_final' => false,
            ];

            $pendientes = $pendientes->slice($capacidadBase)->values();
        }

        return $paginas;
    }

    private function capacidadPorLimite(float $limiteY): int
    {
        return max(
            1,
            (int) floor(($limiteY - self::INICIO_TABLA_Y) / self::ALTO_BLOQUE)
        );
    }
}