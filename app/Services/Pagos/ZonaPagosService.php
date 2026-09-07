<?php

namespace App\Services\Pagos;

use App\Models\Student;
use App\Services\Financiero\Pagos\CarteraEstudianteService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ZonaPagosService
{
    public function __construct(
        protected CarteraEstudianteService $carteraEstudianteService,
    ) {
    }

    /**
     * Obtiene los estudiantes y obligaciones pendientes
     * visibles para una cuenta familiar.
     *
     * Esta consulta:
     * - NO modifica cartera.
     * - NO crea órdenes.
     * - NO registra pagos.
     *
     * El saldo financiero siempre proviene del mismo servicio
     * utilizado por la pantalla administrativa de Pagos.
     */
    public function obtenerZonaPagos(int $userId): array
    {
        $studentIds = $this->obtenerEstudiantesPermitidos($userId);

        if ($studentIds->isEmpty()) {
            return [
                'estudiantes' => [],
                'total_pendiente' => 0.0,
                'cantidad_obligaciones' => 0,
            ];
        }

        $estudiantes = Student::query()
            ->with([
                'course',
            ])
            ->whereIn('id', $studentIds)
            ->where('estado', 'activo')
            ->orderBy('primer_apellido')
            ->orderBy('segundo_apellido')
            ->orderBy('primer_nombre')
            ->orderBy('segundo_nombre')
            ->get();

        /*
         * Primero obtenemos todas las obligaciones reales utilizando
         * CarteraEstudianteService.
         *
         * De esta manera Pagos administrativo y Zona de Pagos utilizan
         * exactamente el mismo saldo financiero.
         */
        $datosEstudiantes = $estudiantes
            ->map(function (Student $estudiante) {
                $obligaciones = $this
                    ->carteraEstudianteService
                    ->obtenerObligaciones(
                        studentId: (int) $estudiante->id,
                        sedeId: (int) $estudiante->sede_id,
                        periodoLectivoId:
                            (int) $estudiante->periodo_lectivo_id,
                    );

                return [
                    'modelo' => $estudiante,
                    'obligaciones' => $obligaciones,
                ];
            });

        /*
         * Reunimos todos los movimientos visibles para consultar de una
         * sola vez cuáles ya tienen una orden digital activa.
         */
        $movimientoIds = $datosEstudiantes
            ->flatMap(
                fn (array $datos) =>
                    $datos['obligaciones']->pluck('id')
            )
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $ordenesActivasPorMovimiento = collect();

        if ($movimientoIds->isNotEmpty()) {
            $ordenesActivasPorMovimiento = DB::table(
                'ordenes_pago_detalles as detalle'
            )
                ->join(
                    'ordenes_pago as orden',
                    'orden.id',
                    '=',
                    'detalle.orden_pago_id'
                )
                ->whereIn(
                    'detalle.movimiento_cartera_estudiante_id',
                    $movimientoIds
                )
                ->whereIn('orden.estado', [
                    'pendiente',
                    'procesando',
                ])
                ->select([
                    'detalle.movimiento_cartera_estudiante_id',
                    'orden.id as orden_pago_id',
                    'orden.referencia',
                    'orden.estado',
                ])
                ->orderByDesc('orden.id')
                ->get()
                ->keyBy(
                    'movimiento_cartera_estudiante_id'
                );
        }

        $totalPendiente = 0.0;
        $cantidadObligaciones = 0;

        $resultado = $datosEstudiantes
            ->map(function (array $datos) use (
                $ordenesActivasPorMovimiento,
                &$totalPendiente,
                &$cantidadObligaciones
            ) {
                /** @var Student $estudiante */
                $estudiante = $datos['modelo'];

                $obligaciones = $datos['obligaciones']
                    ->map(function (array $obligacion) use (
                        $ordenesActivasPorMovimiento,
                        &$totalPendiente,
                        &$cantidadObligaciones
                    ) {
                        $movimientoId = (int) $obligacion['id'];

                        $ordenActiva =
                            $ordenesActivasPorMovimiento->get(
                                $movimientoId
                            );

                        $saldoPendiente = round(
                            (float) (
                                $obligacion['saldo_pendiente']
                                ?? 0
                            ),
                            2
                        );

                        $totalPendiente += $saldoPendiente;
                        $cantidadObligaciones++;

                        return [
                            'movimiento_id' =>
                                $movimientoId,

                            'concepto_cobro_id' =>
                                $obligacion['concepto_cobro_id']
                                ?? null,

                            'codigo' =>
                                $obligacion['codigo']
                                ?? null,

                            'concepto' =>
                                $obligacion['concepto']
                                ?? 'Obligación',

                            'tipo_concepto' =>
                                $obligacion['tipo_concepto']
                                ?? null,

                            'obligatorio' =>
                                (bool) (
                                    $obligacion['obligatorio']
                                    ?? false
                                ),

                            'mes' =>
                                $obligacion['mes']
                                ?? null,

                            'mes_numero' =>
                                $obligacion['mes_numero']
                                ?? null,

                            'fecha_movimiento' =>
                                $obligacion['fecha_movimiento']
                                ?? null,

                            'fecha_vencimiento' =>
                                $obligacion['fecha_vencimiento']
                                ?? null,

                            'valor_original' =>
                                round(
                                    (float) (
                                        $obligacion[
                                            'valor_original'
                                        ]
                                        ?? 0
                                    ),
                                    2
                                ),

                            'valor_vigente' =>
                                round(
                                    (float) (
                                        $obligacion[
                                            'valor_vigente'
                                        ]
                                        ?? 0
                                    ),
                                    2
                                ),

                            'valor_aplicado' =>
                                round(
                                    (float) (
                                        $obligacion[
                                            'valor_aplicado'
                                        ]
                                        ?? 0
                                    ),
                                    2
                                ),

                            'saldo_pendiente' =>
                                $saldoPendiente,

                            'aumento_extemporaneo' =>
                                round(
                                    (float) (
                                        $obligacion[
                                            'aumento_extemporaneo'
                                        ]
                                        ?? 0
                                    ),
                                    2
                                ),

                            'tiene_tarifa_extemporanea' =>
                                (bool) (
                                    $obligacion[
                                        'tiene_tarifa_extemporanea'
                                    ]
                                    ?? false
                                ),

                            /*
                             * Estado exclusivo de la Zona de Pagos.
                             *
                             * Esto NO cambia el estado financiero de
                             * movimientos_cartera_estudiante.
                             */
                            'estado_pago' =>
                                $ordenActiva
                                    ? 'en_proceso'
                                    : 'disponible',

                            'seleccionable' =>
                                $ordenActiva === null,

                            'orden_pago_id' =>
                                $ordenActiva
                                    ? (int) $ordenActiva
                                        ->orden_pago_id
                                    : null,

                            'orden_referencia' =>
                                $ordenActiva
                                    ? (string) $ordenActiva
                                        ->referencia
                                    : null,

                            'orden_estado' =>
                                $ordenActiva
                                    ? (string) $ordenActiva
                                        ->estado
                                    : null,
                        ];
                    })
                    ->values()
                    ->toArray();

                return [
                    'id' =>
                        (int) $estudiante->id,

                    'codigo' =>
                        $estudiante->codigo,

                    'nombres' =>
                        trim(
                            implode(' ', array_filter([
                                $estudiante->primer_nombre,
                                $estudiante->segundo_nombre,
                            ]))
                        ),

                    'apellidos' =>
                        trim(
                            implode(' ', array_filter([
                                $estudiante->primer_apellido,
                                $estudiante->segundo_apellido,
                            ]))
                        ),

                    'nombre_completo' =>
                        trim(
                            implode(' ', array_filter([
                                $estudiante->primer_nombre,
                                $estudiante->segundo_nombre,
                                $estudiante->primer_apellido,
                                $estudiante->segundo_apellido,
                            ]))
                        ),

                    'sede_id' =>
                        (int) $estudiante->sede_id,

                    'periodo_lectivo_id' =>
                        (int) $estudiante->periodo_lectivo_id,

                    'course_id' =>
                        $estudiante->course_id
                            ? (int) $estudiante->course_id
                            : null,

                    'curso' =>
                        $estudiante->course?->curso
                        ?? $estudiante->course?->descripcion
                        ?? null,

                    'obligaciones' =>
                        $obligaciones,

                    'cantidad_obligaciones' =>
                        count($obligaciones),

                    'total_pendiente' =>
                        round(
                            collect($obligaciones)
                                ->sum('saldo_pendiente'),
                            2
                        ),
                ];
            })
            ->values()
            ->toArray();

        return [
            'estudiantes' =>
                $resultado,

            'total_pendiente' =>
                round($totalPendiente, 2),

            'cantidad_obligaciones' =>
                $cantidadObligaciones,
        ];
    }

    /**
     * Obtiene todos los estudiantes asociados a la cuenta familiar.
     *
     * Se mantiene compatibilidad con:
     * - guardian_student;
     * - guardians.student_id.
     */
    protected function obtenerEstudiantesPermitidos(
        int $userId
    ): Collection {
        $porRelacion = DB::table('guardians as guardian')
            ->join(
                'guardian_student as relacion',
                'relacion.guardian_id',
                '=',
                'guardian.id'
            )
            ->where('guardian.user_id', $userId)
            ->where('guardian.estado', 'activo')
            ->where('relacion.estado', 'activo')
            ->pluck('relacion.student_id');

        $porRelacionDirecta = DB::table('guardians')
            ->where('user_id', $userId)
            ->where('estado', 'activo')
            ->whereNotNull('student_id')
            ->pluck('student_id');

        return $porRelacion
            ->merge($porRelacionDirecta)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();
    }
}