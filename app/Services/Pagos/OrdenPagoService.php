<?php

namespace App\Services\Pagos;

use App\Models\MovimientoCarteraEstudiante;
use App\Models\OrdenPago;
use App\Services\Financiero\Pagos\CalcularValorVigenteObligacionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrdenPagoService
{
    public function __construct(
        protected CalcularValorVigenteObligacionService $calculador,
    ) {
    }
    /**
     * Reserva de forma segura el siguiente consecutivo del año
     * y construye la referencia visible de la orden.
     *
     * Ejemplo:
     * 2026-00000001235
     */
    protected function generarReferencia(?int $anio = null): array
    {
        $anio ??= now()->year;

        /*
         * insertOrIgnore evita el problema de dos solicitudes simultáneas
         * intentando crear por primera vez el consecutivo del mismo año.
         */
        DB::table('consecutivos_ordenes_pago')->insertOrIgnore([
            'anio' => $anio,
            'ultimo_numero' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $registro = DB::table('consecutivos_ordenes_pago')
            ->where('anio', $anio)
            ->lockForUpdate()
            ->first();

        $siguiente = ((int) $registro->ultimo_numero) + 1;

        DB::table('consecutivos_ordenes_pago')
            ->where('anio', $anio)
            ->update([
                'ultimo_numero' => $siguiente,
                'updated_at' => now(),
            ]);

        return [
            'anio' => $anio,
            'consecutivo' => $siguiente,
            'referencia' => sprintf(
                '%d-%011d',
                $anio,
                $siguiente
            ),
        ];
    }

    /**
     * Crea una orden completa con una o varias obligaciones.
     *
     * IMPORTANTE:
     * - NO aplica pagos.
     * - NO crea recibos.
     * - NO modifica movimientos de cartera.
     * - NO marca ninguna obligación como pagada.
     */
    public function crearOrden(array $datos): OrdenPago
    {
        return DB::transaction(function () use ($datos) {
            $userId = (int) ($datos['user_id'] ?? 0);

            $movimientoIds = collect(
                $datos['movimiento_ids'] ?? []
            )
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            if (!$userId) {
                throw ValidationException::withMessages([
                    'user_id' => 'No se pudo identificar la cuenta familiar.',
                ]);
            }

            if ($movimientoIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'obligaciones' => 'Debe seleccionar al menos una obligación.',
                ]);
            }

            /*
             * Obtenemos los estudiantes permitidos para esta cuenta familiar.
             *
             * Un mismo user puede estar asociado a varios registros guardian.
             * Por eso reunimos todos los estudiantes vinculados a cualquiera
             * de esos guardianes.
             */
            $studentIdsPermitidos = DB::table('guardians as g')
                ->join(
                    'guardian_student as gs',
                    'gs.guardian_id',
                    '=',
                    'g.id'
                )
                ->where('g.user_id', $userId)
                ->where('g.estado', 'activo')
                ->where('gs.estado', 'activo')
                ->pluck('gs.student_id')
                ->unique()
                ->values();

            /*
             * Compatibilidad con registros donde guardians.student_id
             * todavía tenga relación directa con el estudiante.
             */
            $studentIdsDirectos = DB::table('guardians')
                ->where('user_id', $userId)
                ->where('estado', 'activo')
                ->whereNotNull('student_id')
                ->pluck('student_id');

            $studentIdsPermitidos = $studentIdsPermitidos
                ->merge($studentIdsDirectos)
                ->unique()
                ->values();

            if ($studentIdsPermitidos->isEmpty()) {
                throw ValidationException::withMessages([
                    'estudiantes' => 'La cuenta familiar no tiene estudiantes asociados.',
                ]);
            }

            /*
             * Bloqueamos los movimientos seleccionados mientras se construye
             * la orden para trabajar sobre una fotografía consistente.
             */
            $movimientos = MovimientoCarteraEstudiante::query()
                ->with([
                    'conceptoCobro',
                    'aplicacionesPago.reciboPago',
                ])
                ->whereIn('id', $movimientoIds)
                ->lockForUpdate()
                ->get();

            if ($movimientos->count() !== $movimientoIds->count()) {
                throw ValidationException::withMessages([
                    'obligaciones' => 'Una o más obligaciones seleccionadas no existen.',
                ]);
            }

            $detallesPreparados = [];
            $totalCentavos = 0;

            foreach ($movimientos as $movimiento) {
                if (
                    !$studentIdsPermitidos->contains(
                        (int) $movimiento->student_id
                    )
                ) {
                    throw ValidationException::withMessages([
                        'obligaciones' => 'Una de las obligaciones no pertenece a esta cuenta familiar.',
                    ]);
                }

                if ($movimiento->estado !== 'activo') {
                    throw ValidationException::withMessages([
                        'obligaciones' => 'Una de las obligaciones ya no se encuentra activa.',
                    ]);
                }

                /*
                * Recalculamos el valor financiero vigente dentro del bloqueo.
                *
                * Utilizamos exactamente el mismo motor de la pantalla
                * administrativa de Pagos y de la Zona de Pagos.
                *
                * Este cálculo contempla:
                * - valor original o personalizado;
                * - pagos que continúan confirmados;
                * - recibos anulados;
                * - tarifas extemporáneas;
                * - fecha actual.
                */
                $calculo = $this->calculador->calcular(
                    movimiento: $movimiento,
                    fechaCorte: now(),
                );

                $valorVigenteCentavos = $this->aCentavos(
                    number_format(
                        (float) ($calculo['valor_vigente'] ?? 0),
                        2,
                        '.',
                        ''
                    )
                );

                $saldoCentavos = $this->aCentavos(
                    number_format(
                        (float) ($calculo['saldo_pendiente'] ?? 0),
                        2,
                        '.',
                        ''
                    )
                );

                if ($saldoCentavos <= 0) {
                    throw ValidationException::withMessages([
                        'obligaciones' =>
                            'Una de las obligaciones seleccionadas ya no tiene saldo pendiente.',
                    ]);
                }

                /*
                 * Evitamos una segunda orden activa sobre la misma obligación.
                 *
                 * La cartera sigue activa; simplemente impedimos iniciar
                 * simultáneamente dos intentos digitales sobre el mismo saldo.
                 */
                $yaTieneOrdenActiva = DB::table('ordenes_pago_detalles as opd')
                    ->join(
                        'ordenes_pago as op',
                        'op.id',
                        '=',
                        'opd.orden_pago_id'
                    )
                    ->where(
                        'opd.movimiento_cartera_estudiante_id',
                        $movimiento->id
                    )
                    ->whereIn('op.estado', [
                        'pendiente',
                        'procesando',
                    ])
                    ->exists();

                if ($yaTieneOrdenActiva) {
                    throw ValidationException::withMessages([
                        'obligaciones' => 'Una de las obligaciones seleccionadas ya tiene un pago digital en proceso.',
                    ]);
                }

                $concepto = null;

                if ($movimiento->concepto_cobro_id) {
                    $concepto = DB::table('concepto_cobros')
                        ->where('id', $movimiento->concepto_cobro_id)
                        ->value('descripcion');
                }

                $valorVigente = $this->desdeCentavos(
                    $valorVigenteCentavos
                );

                $saldo = $this->desdeCentavos(
                    $saldoCentavos
                );

                $detallesPreparados[] = [
                    'student_id' => $movimiento->student_id,
                    'sede_id' => $movimiento->sede_id,
                    'periodo_lectivo_id' => $movimiento->periodo_lectivo_id,

                    'movimiento_cartera_estudiante_id' => $movimiento->id,
                    'concepto_cobro_id' => $movimiento->concepto_cobro_id,

                    'concepto' => $concepto
                        ?: $movimiento->descripcion
                        ?: 'Obligación',

                    'descripcion' => $movimiento->descripcion,
                    'tipo_concepto' => $movimiento->tipo_concepto,

                    'mes' => $movimiento->mes,
                    'mes_numero' => $movimiento->mes_numero,
                    'fecha_vencimiento' => $movimiento->fecha_vencimiento,

                    'valor_obligacion' => $valorVigente,
                    'saldo_al_seleccionar' => $saldo,
                    'valor_a_pagar' => $saldo,

                    'estado' => 'pendiente',
                    'aplicacion_pago_id' => null,
                ];

                $totalCentavos += $saldoCentavos;
            }

            $numeracion = $this->generarReferencia(
                $datos['anio'] ?? null
            );

            $total = $this->desdeCentavos($totalCentavos);

            $orden = OrdenPago::create([
                'uuid' => (string) Str::uuid(),

                'anio' => $numeracion['anio'],
                'consecutivo' => $numeracion['consecutivo'],
                'referencia' => $numeracion['referencia'],

                'user_id' => $userId,

                'metodo_pago' => $datos['metodo_pago'] ?? 'pse',
                'estado' => 'pendiente',

                'subtotal' => $total,
                'total' => $total,

                'proveedor' => $datos['proveedor'] ?? null,

                'referencia_proveedor' => null,
                'transaccion_proveedor' => null,
                'respuesta_proveedor' => null,

                'iniciada_en' => null,
                'confirmada_en' => null,
                'rechazada_en' => null,
                'expira_en' => $datos['expira_en'] ?? null,
            ]);

            foreach ($detallesPreparados as $detalle) {
                $orden->detalles()->create($detalle);
            }

            return $orden->load([
                'detalles.estudiante',
                'detalles.sede',
                'detalles.periodoLectivo',
                'detalles.movimientoCartera',
                'detalles.conceptoCobro',
            ]);
        });
    }

    /**
     * Convierte un decimal monetario a centavos sin utilizar float.
     *
     * "120000.00" -> 12000000
     */
    protected function aCentavos(string $valor): int
    {
        $valor = trim($valor);

        if ($valor === '') {
            return 0;
        }

        $negativo = str_starts_with($valor, '-');

        $valor = ltrim($valor, '+-');

        [$entero, $decimal] = array_pad(
            explode('.', $valor, 2),
            2,
            '0'
        );

        $decimal = substr(
            str_pad($decimal, 2, '0'),
            0,
            2
        );

        $centavos = ((int) $entero * 100) + (int) $decimal;

        return $negativo
            ? -$centavos
            : $centavos;
    }

    /**
     * Convierte centavos nuevamente a decimal exacto.
     *
     * 12000000 -> "120000.00"
     */
    protected function desdeCentavos(int $centavos): string
    {
        $negativo = $centavos < 0;

        $centavos = abs($centavos);

        $entero = intdiv($centavos, 100);

        $decimal = $centavos % 100;

        return sprintf(
            '%s%d.%02d',
            $negativo ? '-' : '',
            $entero,
            $decimal
        );
    }
}