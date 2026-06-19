<?php

namespace App\Services\Importacion\Pensum;

use App\Models\PensumAcademico;
use App\Services\Importacion\BaseImportService;
use App\Services\Importacion\Contracts\ImportadorInterface;
use App\Services\Importacion\DTO\ResultadoImportacion;
use Illuminate\Support\Facades\DB;

class PensumImportService extends BaseImportService implements ImportadorInterface
{
    public function importar(string $path, array $opciones = []): ResultadoImportacion
    {
        $resultado = $this->nuevoResultado();

        $sedeId = (int) ($opciones['sede_id'] ?? 0);
        $periodoLectivoId = (int) ($opciones['periodo_lectivo_id'] ?? 0);

        if (! $sedeId) {
            $resultado->agregarError('Debe seleccionar una sede.');
            return $resultado;
        }

        if (! $periodoLectivoId) {
            $resultado->agregarError('Debe seleccionar un periodo lectivo.');
            return $resultado;
        }

        $sheet = $this->abrirHojaActiva($path);
        $ultimaFila = $sheet->getHighestDataRow();

        DB::beginTransaction();

        try {
            for ($fila = 2; $fila <= $ultimaFila; $fila++) {
                $grado = $this->normalizarCodigo($this->valor($sheet, "B{$fila}"));
                $codigo = $this->normalizarCodigo($this->valor($sheet, "C{$fila}"));
                $tipoExcel = $this->normalizarTexto($this->valor($sheet, "D{$fila}"));
                $nombre = $this->normalizarTexto($this->valor($sheet, "E{$fila}"));
                $nombreCorto = $this->normalizarTexto($this->valor($sheet, "F{$fila}"));
                $intensidad = $this->valor($sheet, "G{$fila}");
                $evaluaExcel = $this->normalizarCodigo($this->valor($sheet, "H{$fila}"));

                if (
                    $grado === '' &&
                    $codigo === '' &&
                    $nombre === ''
                ) {
                    continue;
                }

                $resultado->filasLeidas++;

                if ($grado === '') {
                    $this->agregarError($fila, 'No tiene grado.');
                    $resultado->omitidos++;
                    continue;
                }

                if ($codigo === '') {
                    $this->agregarError($fila, 'No tiene código de asignatura.');
                    $resultado->omitidos++;
                    continue;
                }

                if ($nombre === '') {
                    $this->agregarError($fila, "La asignatura código {$codigo} no tiene nombre.");
                    $resultado->omitidos++;
                    continue;
                }

                $tipo = $this->mapearTipo($tipoExcel);
                $formaEvaluar = $this->mapearFormaEvaluar($evaluaExcel);

                if (! is_numeric($intensidad)) {
                    $this->agregarError($fila, "La intensidad horaria de {$codigo} no es numérica.");
                    $resultado->omitidos++;
                    continue;
                }

                $existente = PensumAcademico::query()
                    ->where('sede_id', $sedeId)
                    ->where('periodo_lectivo_id', $periodoLectivoId)
                    ->where('grado', $grado)
                    ->where('codigo', $codigo)
                    ->first();

                PensumAcademico::updateOrCreate(
                    [
                        'sede_id' => $sedeId,
                        'periodo_lectivo_id' => $periodoLectivoId,
                        'grado' => $grado,
                        'codigo' => $codigo,
                    ],
                    [
                        'course_id' => null,
                        'docente_id' => null,
                        'orden' => $fila - 1,
                        'nombre' => $nombre,
                        'nombre_corto' => $nombreCorto ?: $nombre,
                        'tipo' => $tipo,
                        'intensidad_horaria' => (int) $intensidad,
                        'forma_evaluar' => $formaEvaluar,
                        'estado' => 'activo',
                    ]
                );

                if ($existente) {
                    $resultado->actualizados++;
                } else {
                    $resultado->creados++;
                }
            }

            DB::commit();

            return $resultado;
        } catch (\Throwable $e) {
            DB::rollBack();

            $resultado->agregarError('Error general durante la importación: ' . $e->getMessage());

            return $resultado;
        }
    }

    private function mapearTipo(string $valor): string
    {
        $valor = mb_strtoupper(trim($valor));

        return match ($valor) {
            'AREA', 'ÁREA', 'A' => 'area',
            default => 'asignatura',
        };
    }

    private function mapearFormaEvaluar(string $valor): string
    {
        return match ($valor) {
            '02', '2', 'SEMESTRAL' => 'semestral',
            default => 'bimestral',
        };
    }
}