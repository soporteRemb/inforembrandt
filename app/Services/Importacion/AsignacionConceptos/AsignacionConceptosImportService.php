<?php

namespace App\Services\Importacion\AsignacionConceptos;

use App\Models\AsignacionConcepto;
use App\Models\ConceptoCobro;
use App\Models\Course;
use App\Models\PeriodoLectivo;
use App\Models\TipoLimiteExtemporaneo;
use App\Services\Importacion\BaseImportService;
use App\Services\Importacion\Contracts\ImportadorInterface;
use App\Services\Importacion\DTO\ResultadoImportacion;
use Illuminate\Support\Facades\DB;

class AsignacionConceptosImportService extends BaseImportService implements ImportadorInterface
{
    public int $tarifasCreadas = 0;

    public function importar(string $path, array $opciones = []): ResultadoImportacion
    {
        $resultado = $this->nuevoResultado();
        $this->tarifasCreadas = 0;

        $sedeId = (int) ($opciones['sede_id'] ?? 0);
        $periodoId = (int) ($opciones['periodo_lectivo_id'] ?? 0);

        if (
            ! $sedeId ||
            ! $periodoId ||
            ! PeriodoLectivo::query()
                ->whereKey($periodoId)
                ->where('sede_id', $sedeId)
                ->exists()
        ) {
            $resultado->agregarError(
                'La sede y el período lectivo de destino no son válidos.'
            );

            return $resultado;
        }

        try {
            $sheet = $this->abrirHojaActiva($path);
        } catch (\Throwable $e) {
            $resultado->agregarError(
                'No fue posible abrir el Excel: ' . $e->getMessage()
            );

            return $resultado;
        }

        $encabezados = [
            'GRADO',
            'CODIGO_CONCEPTO',
            'CONCEPTO',
            'TARIFA_ORDINARIA',
            'ORDEN',
            'ACTIVO',
            'MES',
            'AÑO',
            'VENCIMIENTO',
            'CODIGO_TIPO',
            'TIPO',
            'VALOR_EXTEMPORANEO',
        ];

        foreach ($encabezados as $indice => $encabezado) {
            $columna = chr(65 + $indice);

            if (
                mb_strtoupper($this->valor($sheet, "{$columna}1"))
                !== $encabezado
            ) {
                $resultado->agregarError(
                    "La columna {$columna} debe llamarse {$encabezado}. No se importaron asignaciones."
                );

                return $resultado;
            }
        }

        // Agrupar las filas que pertenecen a una misma asignación.
        $grupos = [];

        for ($fila = 2; $fila <= $sheet->getHighestDataRow(); $fila++) {
            $datos = [];

            foreach (range('A', 'L') as $columna) {
                $datos[$columna] = $this->valor(
                    $sheet,
                    "{$columna}{$fila}"
                );
            }

            if (! array_filter($datos, fn ($valor) => $valor !== '')) {
                continue;
            }

            $resultado->filasLeidas++;

            $grado = trim($datos['A']);
            $codigo = trim($datos['B']);

            $clave = json_encode([$grado, $codigo]);

            $grupos[$clave][] = [
                'fila' => $fila,
                'datos' => $datos,
            ];
        }

        foreach ($grupos as $registros) {
            $primero = $registros[0];
            $datos = $primero['datos'];
            $filaInicial = $primero['fila'];

            $grado = $datos['A'];
            $codigo = $datos['B'];
            $descripcion = $datos['C'];

            $identificacion = "Grado {$grado}, concepto {$codigo} ({$descripcion})";

            // Una asignación debe tener datos generales consistentes.
            $inconsistencia = null;

            foreach ($registros as $registro) {
                $actual = $registro['datos'];

                foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $columna) {
                    if ($actual[$columna] !== $datos[$columna]) {
                        $inconsistencia =
                            "La fila {$registro['fila']} contiene datos generales diferentes para la misma asignación.";
                        break 2;
                    }
                }
            }

            if ($inconsistencia) {
                $this->omitirAsignacion(
                    $resultado,
                    $filaInicial,
                    $identificacion,
                    $inconsistencia
                );

                continue;
            }

            // Validar el grado en el período de destino.
            if (
                $grado === '' ||
                (
                    $grado !== 'todos' &&
                    ! Course::query()
                        ->where('sede_id', $sedeId)
                        ->where('periodo_lectivo_id', $periodoId)
                        ->where('grado', $grado)
                        ->exists()
                )
            ) {
                $this->omitirAsignacion(
                    $resultado,
                    $filaInicial,
                    $identificacion,
                    'El grado no existe en la sede y período de destino.'
                );

                continue;
            }

            // Resolver el concepto por código, nunca por ID del origen.
            $concepto = ConceptoCobro::query()
                ->where('sede_id', $sedeId)
                ->where('periodo_lectivo_id', $periodoId)
                ->where('codigo', $codigo)
                ->first();

            if (! $concepto) {
                $this->omitirAsignacion(
                    $resultado,
                    $filaInicial,
                    $identificacion,
                    'El código del concepto no existe en el destino.'
                );

                continue;
            }

            // No modificar asignaciones existentes.
            $existe = AsignacionConcepto::query()
                ->where('sede_id', $sedeId)
                ->where('periodo_lectivo_id', $periodoId)
                ->where('grado', $grado)
                ->where('concepto_cobro_id', $concepto->id)
                ->exists();

            if ($existe) {
                $this->omitirAsignacion(
                    $resultado,
                    $filaInicial,
                    $identificacion,
                    'La asignación ya existe. No se modificó ni se agregaron tarifas.'
                );

                continue;
            }

            $tarifaOrdinaria = $this->importe($datos['D']);
            $orden = $datos['E'] === '' ? '1' : $datos['E'];
            $activo = $this->booleano($datos['F']);

            $erroresGenerales = [];

            if ($datos['D'] !== '' && $tarifaOrdinaria === null) {
                $erroresGenerales[] = "Tarifa ordinaria inválida: '{$datos['D']}'.";
            }

            if ($concepto->obligatorio && $tarifaOrdinaria === null) {
                $erroresGenerales[] = 'El concepto es obligatorio y requiere tarifa ordinaria válida.';
            }

            if (! ctype_digit($orden)) {
                $erroresGenerales[] = "Orden inválido: '{$orden}'.";
            }

            if ($activo === null) {
                $erroresGenerales[] = "Estado ACTIVO inválido: '{$datos['F']}'.";
            }

            if ($erroresGenerales !== []) {
                $this->omitirAsignacion(
                    $resultado,
                    $filaInicial,
                    $identificacion,
                    implode(' ', $erroresGenerales)
                );

                continue;
            }

            // Validar todas las tarifas antes de crear la asignación.
            $tarifas = [];
            $combinaciones = [];
            $errorTarifa = null;

            foreach ($registros as $registro) {
                $fila = $registro['fila'];
                $d = $registro['datos'];

                $camposTarifa = [
                    $d['G'], $d['H'], $d['I'],
                    $d['J'], $d['K'], $d['L'],
                ];

                // Asignación sin tarifas extemporáneas.
                if (! array_filter(
                    $camposTarifa,
                    fn ($valor) => $valor !== ''
                )) {
                    continue;
                }

                $mes = $d['G'];
                $anio = $d['H'];
                $fecha = $this->fechaExcel($d['I']);
                $codigoTipo = $d['J'];
                $valor = $this->importe($d['L']);

                $erroresTarifa = [];

                if ($fecha === null) {
                    $erroresTarifa[] = "VENCIMIENTO inválido: '{$d['I']}'.";
                }

                if ($codigoTipo === '') {
                    $erroresTarifa[] = 'CODIGO_TIPO está vacío.';
                }

                if ($valor === null) {
                    $erroresTarifa[] = "VALOR_EXTEMPORANEO inválido: '{$d['L']}'.";
                }

                if (
                    $anio !== '' &&
                    (
                        ! ctype_digit($anio) ||
                        (int) $anio < 2000 ||
                        (int) $anio > 9999
                    )
                ) {
                    $erroresTarifa[] = "AÑO inválido: '{$anio}'.";
                }

                if (
                    $mes !== '' &&
                    ! in_array(
                        $mes,
                        [
                            'ENERO', 'FEBRERO', 'MARZO',
                            'ABRIL', 'MAYO', 'JUNIO',
                            'JULIO', 'AGOSTO', 'SEPTIEMBRE',
                            'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE',
                        ],
                        true
                    )
                ) {
                    $erroresTarifa[] = "MES inválido: '{$mes}'.";
                }

                if ($erroresTarifa !== []) {
                    $errorTarifa = "Fila {$fila}: " . implode(' ', $erroresTarifa);
                    break;
                }

                $tipo = TipoLimiteExtemporaneo::query()
                    ->where('codigo', $codigoTipo)
                    ->first();

                if (! $tipo) {
                    $errorTarifa =
                        "Fila {$fila}: el código de tipo de tarifa '{$codigoTipo}' no existe.";
                    break;
                }

                // Misma regla de duplicidad del formulario.
                $claveTarifa = $fecha . '|' . $tipo->id;

                if (isset($combinaciones[$claveTarifa])) {
                    $errorTarifa =
                        "Fila {$fila}: tarifa duplicada por vencimiento y tipo. Ya aparece en la fila {$combinaciones[$claveTarifa]}.";
                    break;
                }

                $combinaciones[$claveTarifa] = $fila;

                $tarifas[] = [
                    'mes' => $mes === '' ? null : $mes,
                    'anio' => $anio === '' ? null : (int) $anio,
                    'fecha_vencimiento' => $fecha,
                    'tipo_limite_extemporaneo_id' => $tipo->id,
                    'valor' => $valor,
                ];
            }

            if ($errorTarifa) {
                $this->omitirAsignacion(
                    $resultado,
                    $filaInicial,
                    $identificacion,
                    $errorTarifa .
                    ' Se omitió la asignación completa y todas sus tarifas.'
                );

                continue;
            }

            // Guardar asignación y tarifas como una sola operación.
            try {
                DB::transaction(function () use (
                    $sedeId,
                    $periodoId,
                    $grado,
                    $concepto,
                    $tarifaOrdinaria,
                    $orden,
                    $activo,
                    $tarifas
                ) {
                    $asignacion = AsignacionConcepto::create([
                        'sede_id' => $sedeId,
                        'periodo_lectivo_id' => $periodoId,
                        'grado' => $grado,
                        'concepto_cobro_id' => $concepto->id,
                        'tarifa_ordinaria' => $tarifaOrdinaria,
                        'tarifa_extemporanea' => 0,
                        'orden' => (int) $orden,
                        'activo' => $activo,
                    ]);

                    foreach ($tarifas as $tarifa) {
                        $asignacion->vencimientos()->create($tarifa);
                    }
                });

                $resultado->creados++;
                $this->tarifasCreadas += count($tarifas);
            } catch (\Throwable $e) {
                $this->omitirAsignacion(
                    $resultado,
                    $filaInicial,
                    $identificacion,
                    'No se pudo guardar la asignación: ' .
                    $e->getMessage() .
                    ' Se revirtieron todos sus cambios.'
                );
            }
        }

        return $resultado;
    }

    private function importe(string $valor): ?string
    {
        if ($valor === '') {
            return null;
        }

        // Excel exporta los valores numéricos sin separador de miles.
        if (! preg_match('/^\d+(\.\d{1,2})?$/', $valor)) {
            return null;
        }

        return $valor;
    }

    private function booleano(string $valor): ?bool
    {
        return match (mb_strtoupper(trim($valor))) {
            'SÍ', 'SI', '1', 'TRUE' => true,
            'NO', '0', 'FALSE' => false,
            default => null,
        };
    }

    private function fechaExcel(string $valor): ?string
    {
        if ($valor === '') {
            return null;
        }

        // Aceptar fechas del exportador y fechas reales de Excel.
        $fecha = $this->convertirFecha($valor);

        if (! $fecha) {
            return null;
        }

        // Evitar que una fecha textual inválida se corrija sola.
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valor)) {
            $partes = explode('/', $valor);

            if (! checkdate(
                (int) $partes[1],
                (int) $partes[0],
                (int) $partes[2]
            )) {
                return null;
            }
        }

        return $fecha;
    }

    private function omitirAsignacion(
        ResultadoImportacion $resultado,
        int $fila,
        string $identificacion,
        string $motivo
    ): void {
        $resultado->omitidos++;

        $this->agregarError(
            $fila,
            "{$identificacion}: {$motivo}"
        );
    }
}