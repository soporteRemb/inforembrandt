<?php

namespace App\Services\Importacion;

use App\Services\Importacion\DTO\ResultadoImportacion;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseImportService
{
    protected ResultadoImportacion $resultado;

    protected function nuevoResultado(): ResultadoImportacion
    {
        $this->resultado = new ResultadoImportacion();

        return $this->resultado;
    }

    protected function abrirHojaActiva(string $path): Worksheet
    {
        return IOFactory::load($path)->getActiveSheet();
    }

    protected function valor(Worksheet $sheet, string $celda): string
    {
        return trim((string) $sheet->getCell($celda)->getValue());
    }

    protected function separarDosPartes(string $texto): array
    {
        $texto = trim(preg_replace('/\s+/', ' ', $texto));

        if ($texto === '') {
            return ['', null];
        }

        $partes = explode(' ', $texto, 2);

        return [
            $partes[0] ?? '',
            $partes[1] ?? null,
        ];
    }

    protected function convertirFecha($valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        try {
            if (is_numeric($valor)) {
                return Carbon::instance(
                    ExcelDate::excelToDateTimeObject($valor)
                )->toDateString();
            }

            $texto = trim((string) $valor);

            foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $formato) {
                try {
                    return Carbon::createFromFormat($formato, $texto)->toDateString();
                } catch (\Throwable) {
                    continue;
                }
            }

            return Carbon::parse($texto)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function normalizarTexto(?string $valor): string
    {
        return trim((string) $valor);
    }

    protected function normalizarCodigo(?string $valor): string
    {
        return strtoupper(trim((string) $valor));
    }

    protected function agregarError(int|string $fila, string $mensaje): void
    {
        $this->resultado->agregarError("Fila {$fila}: {$mensaje}");
    }


    protected function obligatorio(?string $valor): string
    {
        return blank(trim((string) $valor))
            ? '*'
            : trim((string) $valor);
    }
}