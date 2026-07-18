<?php

namespace App\Services\Financiero\Pagos;

use App\Models\OperacionPago;
use App\Models\ReciboPago;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarPagoService
{
    public function __construct(
        protected GenerarConsecutivoReciboService $consecutivoService,
        protected CrearOperacionPagoService $crearOperacionService,
        protected CrearReciboPagoService $crearReciboService,
    ) {
    }

    /**
     * Confirma toda la cola como una sola operación financiera.
     *
     * Toda la ejecución se realiza dentro de una única transacción:
     *
     * - genera un solo número de recibo;
     * - crea la operación de caja;
     * - crea una línea de recibo por cada fila de la cola;
     * - registra las formas de pago;
     * - aplica los valores a cartera;
     * - genera saldos a favor por excedentes.
     */
    public function registrar(
        int $sedeId,
        int $periodoLectivoId,
        int $studentId,
        array $colaPagos,
        string $recibidoDe,
        int $registradoPor,
        ?Carbon $fechaPago = null,
    ): array {
        $fechaPago ??= now();

        $this->validarDatosGenerales(
            sedeId: $sedeId,
            periodoLectivoId: $periodoLectivoId,
            studentId: $studentId,
            colaPagos: $colaPagos,
            recibidoDe: $recibidoDe,
            registradoPor: $registradoPor,
        );

        return DB::transaction(function () use (
            $sedeId,
            $periodoLectivoId,
            $studentId,
            $colaPagos,
            $recibidoDe,
            $registradoPor,
            $fechaPago,
        ) {
            $cola = collect($colaPagos);

            /*
            |--------------------------------------------------------------------------
            | Totales generales de la atención
            |--------------------------------------------------------------------------
            | El subtotal y el total recibido representan dinero efectivamente
            | ingresado. Los descuentos se almacenan por separado.
            */
            $subtotal = $this->sumarCola(
                cola: $cola,
                campo: 'valor_recibido',
            );

            $totalDescuentos = $this->sumarCola(
                cola: $cola,
                campo: 'descuento',
            );

            $totalRecibido = $subtotal;

            if ($totalRecibido <= 0) {
                throw ValidationException::withMessages([
                    'colaPagos' => 'El total recibido debe ser mayor que cero.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Generar un solo consecutivo para toda la cola
            |--------------------------------------------------------------------------
            */
            $numeroRecibo = $this->consecutivoService->generar(
                sedeId: $sedeId,
                anio: (int) $fechaPago->year,
            );

            /*
            |--------------------------------------------------------------------------
            | Crear la operación que agrupa toda la atención
            |--------------------------------------------------------------------------
            */
            $operacion = $this->crearOperacionService->crear(
                sedeId: $sedeId,
                periodoLectivoId: $periodoLectivoId,
                studentId: $studentId,
                recibidoDe: $recibidoDe,
                subtotal: $subtotal,
                totalDescuentos: $totalDescuentos,
                totalRecibido: $totalRecibido,
                registradoPor: $registradoPor,
                registradoEn: $fechaPago,
            );

            /*
            |--------------------------------------------------------------------------
            | Crear todas las líneas financieras
            |--------------------------------------------------------------------------
            */
            $recibos = [];

            foreach ($colaPagos as $indice => $fila) {
                $this->validarFilaCola(
                    fila: $fila,
                    indice: $indice,
                );

                /*
                 * Reforzamos los datos comunes para que todas las filas
                 * pertenezcan inequívocamente a la misma atención.
                 */
                $fila['recibido_de'] = filled($fila['recibido_de'] ?? null)
                    ? trim((string) $fila['recibido_de'])
                    : trim($recibidoDe);

                $recibos[] = $this->crearReciboService->crear(
                    operacion: $operacion,
                    fila: $fila,
                    numeroRecibo: $numeroRecibo,
                    anio: (int) $fechaPago->year,
                    registradoPor: $registradoPor,
                    fechaPago: $fechaPago,
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Totales financieros reales creados
            |--------------------------------------------------------------------------
            */
            $recibosCollection = collect($recibos);

            $totalAplicado = round(
                (float) $recibosCollection->sum(
                    fn (ReciboPago $recibo) => (float) $recibo->valor_aplicado
                ),
                2
            );

            $saldoFavorGenerado = round(
                (float) $recibosCollection->sum(
                    fn (ReciboPago $recibo) => (float) $recibo->saldo_favor_generado
                ),
                2
            );

            return [
                'operacion' => $operacion->fresh([
                    'recibos',
                ]),

                'operacion_id' => $operacion->id,
                'numero_recibo' => $numeroRecibo,
                'anio' => (int) $fechaPago->year,
                'fecha_pago' => $fechaPago,

                'cantidad_lineas' => count($recibos),

                'subtotal' => $subtotal,
                'total_descuentos' => $totalDescuentos,
                'total_recibido' => $totalRecibido,
                'total_aplicado' => $totalAplicado,
                'saldo_favor_generado' => $saldoFavorGenerado,

                'recibos' => $recibos,
            ];
        }, 3);
    }

    /**
     * Valida los datos comunes de la operación.
     */
    private function validarDatosGenerales(
        int $sedeId,
        int $periodoLectivoId,
        int $studentId,
        array $colaPagos,
        string $recibidoDe,
        int $registradoPor,
    ): void {
        $errores = [];

        if ($sedeId <= 0) {
            $errores['sede_id'] = 'No se encontró una sede válida.';
        }

        if ($periodoLectivoId <= 0) {
            $errores['periodo_lectivo_id'] =
                'No se encontró un periodo lectivo válido.';
        }

        if ($studentId <= 0) {
            $errores['student_id'] =
                'Seleccione un estudiante válido.';
        }

        if (empty($colaPagos)) {
            $errores['colaPagos'] =
                'Debe adicionar al menos un pago a la cola.';
        }

        if (trim($recibidoDe) === '') {
            $errores['recibidoDe'] =
                'Debe indicar de quién se recibe el pago.';
        }

        if ($registradoPor <= 0) {
            $errores['registrado_por'] =
                'No se encontró el usuario que registra el pago.';
        }

        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }
    }

    /**
     * Valida una fila antes de enviarla a los servicios especializados.
     */
    private function validarFilaCola(array $fila, int $indice): void
    {
        $numeroFila = $indice + 1;

        if ((int) ($fila['movimiento_id'] ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'colaPagos' => sprintf(
                    'La fila %d no tiene una obligación válida.',
                    $numeroFila
                ),
            ]);
        }

        if ((int) ($fila['forma_pago_id'] ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'colaPagos' => sprintf(
                    'La fila %d no tiene una forma de pago válida.',
                    $numeroFila
                ),
            ]);
        }

        if ((float) ($fila['valor_recibido'] ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'colaPagos' => sprintf(
                    'El valor recibido de la fila %d debe ser mayor que cero.',
                    $numeroFila
                ),
            ]);
        }

        if ((float) ($fila['descuento'] ?? 0) < 0) {
            throw ValidationException::withMessages([
                'colaPagos' => sprintf(
                    'El descuento de la fila %d no puede ser negativo.',
                    $numeroFila
                ),
            ]);
        }
    }

    /**
     * Suma un campo monetario de toda la cola con precisión de dos decimales.
     */
    private function sumarCola(Collection $cola, string $campo): float
    {
        return round(
            (float) $cola->sum(
                fn (array $fila) => (float) ($fila[$campo] ?? 0)
            ),
            2
        );
    }
}