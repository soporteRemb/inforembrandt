<?php

namespace App\Services\Importacion\ConceptosCobro;

use App\Models\ConceptoCobro;
use App\Services\Importacion\BaseImportService;
use App\Services\Importacion\Contracts\ImportadorInterface;
use App\Services\Importacion\DTO\ResultadoImportacion;
use Illuminate\Support\Facades\DB;

class ConceptosCobroImportService extends BaseImportService implements ImportadorInterface
{
    public function importar(string $path, array $opciones = []): ResultadoImportacion
    {
        $resultado = $this->nuevoResultado();

        $sedeId = (int) ($opciones['sede_id'] ?? 0);
        $periodoLectivoId = (int) ($opciones['periodo_lectivo_id'] ?? 0);

        if ($sedeId <= 0 || $periodoLectivoId <= 0) {
            $resultado->agregarError('Debe seleccionar una sede y un período lectivo.');
            return $resultado;
        }

        try {
            $sheet = $this->abrirHojaActiva($path);
        } catch (\Throwable $e) {
            $resultado->agregarError('No fue posible abrir el archivo Excel: ' . $e->getMessage());
            return $resultado;
        }

        $encabezados = [
            'CODIGO', 'DESCRIPCION', 'TIPO_MOVIMIENTO', 'CONTROL',
            'IMPUESTO', 'FACTURAR', 'OBLIGATORIO', 'ACTIVO',
            'PORTAL_ACUDIENTES', 'APLICAR_INTERESES',
        ];

        foreach ($encabezados as $indice => $encabezado) {
            $columna = chr(65 + $indice);

            if (mb_strtoupper($this->valor($sheet, "{$columna}1")) !== $encabezado) {
                $resultado->agregarError(
                    "La columna {$columna} debe tener el encabezado {$encabezado}. No se importó ningún concepto."
                );
                return $resultado;
            }
        }

        $ultimaFila = $sheet->getHighestDataRow();

        try {
            DB::beginTransaction();

            for ($fila = 2; $fila <= $ultimaFila; $fila++) {
                $datos = [];

                foreach (range('A', 'J') as $columna) {
                    $datos[$columna] = $this->valor($sheet, "{$columna}{$fila}");
                }

                if (count(array_filter($datos, fn ($valor) => $valor !== '')) === 0) {
                    continue;
                }

                $resultado->filasLeidas++;

                $codigo = $datos['A'];
                $descripcion = $this->normalizarTexto($datos['B']);
                $tipoMovimiento = mb_strtolower($datos['C']);
                $control = mb_strtolower($datos['D']);
                $impuesto = $datos['E'] === '' ? '0' : $datos['E'];

                if (
                    ! ctype_digit($codigo) ||
                    (int) $codigo < 1 ||
                    (int) $codigo > 99 ||
                    $descripcion === '' ||
                    mb_strlen($descripcion) > 150 ||
                    ! in_array($tipoMovimiento, ['ingreso', 'egreso'], true) ||
                    ! in_array($control, ['interno', 'externo'], true) ||
                    ! ctype_digit($impuesto) ||
                    (int) $impuesto > 100
                ) {
                    $this->omitir(
                        $resultado,
                        $fila,
                        $codigo,
                        $descripcion,
                        'Código, descripción, tipo de movimiento, control o impuesto inválido.'
                    );
                    continue;
                }

                $booleanos = [];
                $valido = true;

                foreach ([
                    'facturar' => 'F',
                    'obligatorio' => 'G',
                    'activo' => 'H',
                    'portal_acudientes' => 'I',
                    'aplicar_intereses' => 'J',
                ] as $campo => $columna) {
                    $valor = $this->convertirBooleano($datos[$columna]);

                    if ($valor === null) {
                        $this->omitir(
                            $resultado,
                            $fila,
                            $codigo,
                            $descripcion,
                            "El campo {$campo} debe contener Sí o No."
                        );
                        $valido = false;
                        break;
                    }

                    $booleanos[$campo] = $valor;
                }

                if (! $valido) {
                    continue;
                }

                $existe = ConceptoCobro::query()
                    ->where('sede_id', $sedeId)
                    ->where('periodo_lectivo_id', $periodoLectivoId)
                    ->where('codigo', (int) $codigo)
                    ->exists();

                if ($existe) {
                    $this->omitir(
                        $resultado,
                        $fila,
                        $codigo,
                        $descripcion,
                        'Ya existe en la sede y período seleccionados. No se modificó.'
                    );
                    continue;
                }

                ConceptoCobro::create([
                    'sede_id' => $sedeId,
                    'periodo_lectivo_id' => $periodoLectivoId,
                    'codigo' => (int) $codigo,
                    'descripcion' => $descripcion,
                    'tipo_movimiento' => $tipoMovimiento,
                    'control' => $control,
                    'impuesto' => (int) $impuesto,
                    ...$booleanos,
                ]);

                $resultado->creados++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            $resultado->creados = 0;
            $resultado->agregarError(
                'La importación fue cancelada y no se guardaron cambios: ' . $e->getMessage()
            );
        }

        return $resultado;
    }

    private function convertirBooleano(string $valor): ?bool
    {
        return match (mb_strtoupper(trim($valor))) {
            'SÍ', 'SI', '1', 'TRUE' => true,
            'NO', '0', 'FALSE' => false,
            default => null,
        };
    }

    private function omitir(
        ResultadoImportacion $resultado,
        int $fila,
        string $codigo,
        string $descripcion,
        string $motivo
    ): void {
        $resultado->omitidos++;

        $this->agregarError(
            $fila,
            "Código {$codigo} - {$descripcion}: {$motivo}"
        );
    }
}