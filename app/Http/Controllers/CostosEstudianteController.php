<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\FichaCostoEstudiante;
use App\Models\AsignacionConcepto;
use App\Models\PensionEstudiante;
use App\Models\OtroCostoEstudiante;
use App\Models\CostoMoraEstudiante;
use App\Models\MovimientoCarteraEstudiante;
use App\Models\ReciboPago;
use App\Models\AsignacionCostosEstudiante;


use App\Services\Financiero\ResumenCuentaEstudianteService;
use App\Services\Financiero\Pagos\SincronizacionCarteraEstudianteService;
use App\Services\Financiero\Pagos\HistorialPagosService;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CostosEstudianteController extends Controller
{
    public function index(Student $student)
    {
        $student->load([
            'sede',
            'periodoLectivo',
            'course',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Datos administrativos de la ficha
        |--------------------------------------------------------------------------
        | La ficha sigue guardando tipo de pago, pagaré, observaciones y otros
        | datos administrativos, pero los valores causados se leen de cartera.
        */
        $ficha = FichaCostoEstudiante::firstOrCreate(
            [
                'student_id' => $student->id,
                'periodo_lectivo_id' => $student->periodo_lectivo_id,
            ],
            [
                'sede_id' => $student->sede_id,
                'tipo_pago' => 'PRIVADO',
                'mes_causado' => 'MARZO',
                'saldo_anterior' => 0,
                'matricula' => 0,
                'costos_academicos' => 0,
                'deudas' => 0,
                'otras_deudas' => 0,
                'abonos' => 0,
                'total_deuda' => 0,
                'pension_inicial' => 0,
                'pagare_no' => 0,
                'observaciones' => null,
            ]
        );

        $asignacionCostos = AsignacionCostosEstudiante::query()
            ->where('student_id', $student->id)
            ->where('periodo_lectivo_id', $student->periodo_lectivo_id)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Fuente única de valores económicos
        |--------------------------------------------------------------------------
        */
        $movimientos = MovimientoCarteraEstudiante::query()
            ->with([
                'conceptoCobro',
                'aplicacionesPago.reciboPago',
            ])
            ->where('student_id', $student->id)
            ->where('sede_id', $student->sede_id)
            ->where(
                'periodo_lectivo_id',
                $student->periodo_lectivo_id
            )
            ->where('tipo_movimiento', 'causacion')
            ->where('estado', 'activo')
            ->orderByRaw('mes_numero IS NULL')
            ->orderBy('mes_numero')
            ->orderBy('concepto_cobro_id')
            ->get()
            ->map(function (MovimientoCarteraEstudiante $movimiento) {
                /*
                * Solo bloqueamos por pagos actualmente confirmados.
                * Las aplicaciones asociadas a recibos anulados permanecen
                * como trazabilidad, pero ya no representan un pago vigente.
                */
                $movimiento->tiene_pago_confirmado =
                    $movimiento->aplicacionesPago->contains(
                        fn ($aplicacion) =>
                            $aplicacion->reciboPago?->estado
                            === ReciboPago::ESTADO_CONFIRMADO
                    );

                $movimiento->valor_pagado_confirmado =
                    $movimiento->aplicacionesPago
                        ->filter(
                            fn ($aplicacion) =>
                                $aplicacion->reciboPago?->estado
                                === ReciboPago::ESTADO_CONFIRMADO
                        )
                        ->sum(
                            fn ($aplicacion) =>
                                (float) $aplicacion->valor_aplicado
                        );

                return $movimiento;
            });

        /*
        |--------------------------------------------------------------------------
        | Clasificar los movimientos para las tarjetas
        |--------------------------------------------------------------------------
        */
        $movimientoMatricula = $movimientos->first(
            function (MovimientoCarteraEstudiante $movimiento) {
                $descripcion = Str::upper(
                    Str::ascii(
                        $movimiento->conceptoCobro?->descripcion
                        ?? $movimiento->descripcion
                        ?? ''
                    )
                );

                return str_contains($descripcion, 'MATRICULA');
            }
        );

        $movimientoCostosAcademicos = $movimientos->first(
            function (MovimientoCarteraEstudiante $movimiento) {
                $descripcion = Str::upper(
                    Str::ascii(
                        $movimiento->conceptoCobro?->descripcion
                        ?? $movimiento->descripcion
                        ?? ''
                    )
                );

                return str_contains(
                    $descripcion,
                    'COSTOS ACADEMICOS'
                );
            }
        );

        $movimientosPension = $movimientos
            ->filter(
                function (MovimientoCarteraEstudiante $movimiento) {
                    $descripcion = Str::upper(
                        Str::ascii(
                            $movimiento->conceptoCobro?->descripcion
                            ?? $movimiento->descripcion
                            ?? ''
                        )
                    );

                    return str_contains($descripcion, 'PENSION');
                }
            )
            ->keyBy(
                fn (MovimientoCarteraEstudiante $movimiento) =>
                    (int) $movimiento->mes_numero
            );

        $otrosMovimientos = $movimientos
            ->filter(
                function (MovimientoCarteraEstudiante $movimiento) {
                    $descripcion = Str::upper(
                        Str::ascii(
                            $movimiento->conceptoCobro?->descripcion
                            ?? $movimiento->descripcion
                            ?? ''
                        )
                    );

                    return ! str_contains($descripcion, 'MATRICULA')
                        && ! str_contains($descripcion, 'PENSION')
                        && ! str_contains(
                            $descripcion,
                            'COSTOS ACADEMICOS'
                        );
                }
            )
            ->values();
        $totalOtrosMovimientos = $otrosMovimientos->sum(
            fn (MovimientoCarteraEstudiante $movimiento) =>
                (float) $movimiento->valor
        );

        

        /*
        |--------------------------------------------------------------------------
        | Meses causados
        |--------------------------------------------------------------------------
        */
        $mesesCausados = $movimientosPension
            ->keys()
            ->map(fn ($mes) => (int) $mes)
            ->values()
            ->toArray();

        $ultimoMesCausado = app(
            ResumenCuentaEstudianteService::class
        )->obtenerUltimoMesCausado($student);

        if ($ultimoMesCausado) {
            $ficha->update([
                'mes_causado' => strtoupper($ultimoMesCausado),
            ]);

            $ficha->refresh();
        }

        /*
        |--------------------------------------------------------------------------
        | Totales provenientes de cartera
        |--------------------------------------------------------------------------
        */
        $valorMatricula =
            (float) ($movimientoMatricula?->valor ?? 0);

        $valorCostosAcademicos =
            (float) ($movimientoCostosAcademicos?->valor ?? 0);

        $totalPensiones = $movimientosPension->sum(
            fn ($movimiento) => (float) $movimiento->valor
        );

        $totalOtrosCostos = $otrosMovimientos->sum(
            fn ($movimiento) => (float) $movimiento->valor
        );

        $totalCausado =
            $valorMatricula
            + $valorCostosAcademicos
            + $totalPensiones
            + $totalOtrosCostos;

    

        /*
        |--------------------------------------------------------------------------
        | Resumen de pagos realizados
        |--------------------------------------------------------------------------
        | Usa el mismo servicio del historial de la pantalla Pagos.
        | Solo muestra recibos actualmente confirmados.
        */
        $historialPagos = app(
            HistorialPagosService::class
        )->consultar(
            studentId: (int) $student->id,
            sedeId: (int) $student->sede_id,
            periodoLectivoId: (int) $student->periodo_lectivo_id,
            estado: ReciboPago::ESTADO_CONFIRMADO,
            limite: 100,
        );

        $pagosRealizados = collect(
            $historialPagos['filas'] ?? []
        );

        $totalAbonos = $pagosRealizados->sum(
            fn (array $pago) => (float) ($pago['valor_pagado'] ?? 0)
        );

        $tieneCostosAsignados = $asignacionCostos !== null;

        $totalPensionesVisible = $tieneCostosAsignados
            ? $totalPensiones
            : 0;

        $totalOtrosCostosVisible = $tieneCostosAsignados
            ? $totalOtrosCostos
            : 0;

        $totalDeuda = max(
            (float) ($asignacionCostos?->saldo_anterior ?? 0)
            + (float) ($asignacionCostos?->matricula ?? 0)
            + (float) ($asignacionCostos?->costos_academicos ?? 0)
            + $totalPensionesVisible
            + $totalOtrosCostosVisible
            - $totalAbonos,
            0
        );

        return view('costos-estudiante.index', [
            'student' => $student,
            'ficha' => $ficha,

            'movimientoMatricula' =>
                $movimientoMatricula,

            'movimientoCostosAcademicos' =>
                $movimientoCostosAcademicos,

            'movimientosPension' =>
                $movimientosPension,

            'otrosMovimientos' =>
                $otrosMovimientos,

            'mesesCausados' =>
                $mesesCausados,

            'valorMatricula' =>
                $valorMatricula,

            'valorCostosAcademicos' =>
                $valorCostosAcademicos,

            'totalPensiones' =>
                $totalPensiones,

            'totalOtrosCostos' =>
                $totalOtrosCostos,

            'totalOtrosMovimientos' =>
                $totalOtrosMovimientos,

            'totalDeuda' =>
                $totalDeuda,
            
            'totalAbonos' =>
                $totalAbonos,

            'pagosRealizados' =>
                $pagosRealizados,
            
            'asignacionCostos' =>
                $asignacionCostos,

            'totalPensionesVisible' =>
                $totalPensionesVisible,

            'totalOtrosCostosVisible' =>
                $totalOtrosCostosVisible,
        ]);
    }

    public function guardar(Request $request, Student $student)
    {
        $data = $request->validate([
            'tipo_pago' => ['nullable', 'string', 'max:50'],
            'saldo_anterior' => ['nullable', 'string'],
            'matricula' => ['nullable', 'string'],
            'costos_academicos' => ['nullable', 'string'],
            'deudas' => ['nullable', 'string'],
            'otras_deudas' => ['nullable', 'string'],
            'abonos' => ['nullable', 'string'],
            'pension_inicial' => ['nullable', 'string'],
            'pagare_no' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string'],
            'pensiones' => ['nullable', 'array'],
            'otros_costos' => ['nullable', 'array'],
        ]);

        $camposMoneda = [
            'saldo_anterior',
            'matricula',
            'costos_academicos',
            'deudas',
            'otras_deudas',
            'abonos',
            'pension_inicial',
        ];

        foreach ($camposMoneda as $campo) {
            $limpio = preg_replace('/\D/', '', $data[$campo] ?? '0');
            $data[$campo] = number_format((float) $limpio, 2, '.', '');
        }

        $totalOtrosCostos = 0;

        if ($request->filled('otros_costos')) {
            foreach ($request->otros_costos as $valor) {
                $valorLimpio = preg_replace('/\D/', '', $valor ?? '0');
                $totalOtrosCostos += (float) $valorLimpio;
            }
        }

        $totalDeuda =
            (float) $data['saldo_anterior']
            + (float) $data['matricula']
            + (float) $data['costos_academicos']
            + (float) $data['deudas']
            + (float) $data['otras_deudas']
            - (float) $data['abonos'];

        $ficha = FichaCostoEstudiante::updateOrCreate(
            [
                'student_id' => $student->id,
                'periodo_lectivo_id' => $student->periodo_lectivo_id,
            ],
            [
                'sede_id' => $student->sede_id,
                'tipo_pago' => $data['tipo_pago'] ?? 'PRIVADO',
                'mes_causado' => 'MARZO',
                'saldo_anterior' => $data['saldo_anterior'] ?? 0,
                'matricula' => $data['matricula'] ?? 0,
                'costos_academicos' => $data['costos_academicos'] ?? 0,
                'deudas' => $data['deudas'] ?? 0,
                'otras_deudas' => $data['otras_deudas'] ?? 0,
                'abonos' => $data['abonos'] ?? 0,
                'total_deuda' => max($totalDeuda, 0),
                'pension_inicial' => $data['pension_inicial'] ?? 0,
                'pagare_no' => $data['pagare_no'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
            ]
        );

        $sincronizador = app(SincronizacionCarteraEstudianteService::class);

        $sincronizador->sincronizarConceptoPorDescripcion(
            student: $student,
            descripcionBuscada: 'MATRICULA',
            nuevoValor: (float) $data['matricula'],
        );

        $sincronizador->sincronizarConceptoPorDescripcion(
            student: $student,
            descripcionBuscada: 'COSTOS ACADEMICOS',
            nuevoValor: (float) $data['costos_academicos'],
        );

        if ($ficha && $request->filled('pensiones')) {
            foreach ($request->pensiones as $mesNumero => $valor) {
                $valorLimpio = preg_replace('/\D/', '', $valor ?? '0');
                $valorDecimal = number_format((float) $valorLimpio, 2, '.', '');

                $pension = PensionEstudiante::where('ficha_costo_estudiante_id', $ficha->id)
                    ->where('mes_numero', $mesNumero)
                    ->first();

                if ($pension) {
                    $pension->update([
                        'valor_personalizado' => $valorDecimal,
                        'modificado_manual' => (float) $valorDecimal != (float) $pension->valor_base,
                    ]);
                    $sincronizador->sincronizarPension(
                        student: $student,
                        mesNumero: (int) $mesNumero,
                        nuevoValor: (float) $valorDecimal,
                    );
                }
            }
        }
        $asignacionCostos = AsignacionCostosEstudiante::query()
            ->where('student_id', $student->id)
            ->where('periodo_lectivo_id', $student->periodo_lectivo_id)
            ->first();

        if ($asignacionCostos) {
            $columnasPensiones = [
                2 => 'pension_febrero',
                3 => 'pension_marzo',
                4 => 'pension_abril',
                5 => 'pension_mayo',
                6 => 'pension_junio',
                7 => 'pension_julio',
                8 => 'pension_agosto',
                9 => 'pension_septiembre',
                10 => 'pension_octubre',
                11 => 'pension_noviembre',
            ];

            $valoresAsignados = [
                'saldo_anterior' => (float) $data['saldo_anterior'],
                'matricula' => (float) $data['matricula'],
                'costos_academicos' => (float) $data['costos_academicos'],
            ];

            foreach ($request->input('pensiones', []) as $mesNumero => $valor) {
                $mesNumero = (int) $mesNumero;

                if (! isset($columnasPensiones[$mesNumero])) {
                    continue;
                }

                $valorLimpio = preg_replace('/\D/', '', $valor ?? '0');

                $valoresAsignados[$columnasPensiones[$mesNumero]]
                    = (float) $valorLimpio;
            }

            $asignacionCostos->update($valoresAsignados);
        }
        if ($ficha && $request->filled('otros_costos')) {
            foreach ($request->otros_costos as $otroCostoId => $valor) {
                $valorLimpio = preg_replace('/\D/', '', $valor ?? '0');
                $valorDecimal = number_format((float) $valorLimpio, 2, '.', '');

                $otroCosto = OtroCostoEstudiante::where('id', $otroCostoId)
                    ->where('ficha_costo_estudiante_id', $ficha->id)
                    ->first();

                if ($otroCosto) {
                    $otroCosto->update([
                        'valor_personalizado' => $valorDecimal,
                        'modificado_manual' => (float) $valorDecimal != (float) $otroCosto->valor_base,
                    ]);

                    $sincronizador->sincronizarConcepto(
                        student: $student,
                        conceptoCobroId: (int) $otroCosto->concepto_cobro_id,
                        nuevoValor: (float) $valorDecimal,
                    );
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Actualizar otros costos causados
        |--------------------------------------------------------------------------
        */
        $otrosMovimientos = $request->input('otros_movimientos', []);

        foreach ($otrosMovimientos as $movimientoId => $valorIngresado) {
            $movimiento = MovimientoCarteraEstudiante::query()
                ->where('id', (int) $movimientoId)
                ->where('student_id', $student->id)
                ->where('periodo_lectivo_id', $student->periodo_lectivo_id)
                ->where('tipo_movimiento', 'causacion')
                ->where('tipo_concepto', 'no_obligatorio')
                ->where('estado', 'activo')
                ->first();

            if (! $movimiento) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | No modificar obligaciones con pagos confirmados
            |--------------------------------------------------------------------------
            */
            $tienePagoConfirmado = MovimientoCarteraEstudiante::query()
                ->where('movimiento_origen_id', $movimiento->id)
                ->where('tipo_movimiento', 'pago')
                ->where('estado', 'confirmado')
                ->exists();

            if ($tienePagoConfirmado) {
                continue;
            }

            $valorNuevo = $this->convertirMonedaAFloat($valorIngresado);

            $movimiento->update([
                'valor_personalizado' => $valorNuevo,
                'valor' => $valorNuevo,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Actualizar otros costos causados
        |--------------------------------------------------------------------------
        */
        $otrosCostos = $request->input('otros_costos', []);

        foreach ($otrosCostos as $movimientoId => $valorIngresado) {
            $movimiento = MovimientoCarteraEstudiante::query()
                ->where('id', (int) $movimientoId)
                ->where('student_id', $student->id)
                ->where(
                    'periodo_lectivo_id',
                    $student->periodo_lectivo_id
                )
                ->where('tipo_movimiento', 'causacion')
                ->where('tipo_concepto', 'no_obligatorio')
                ->where('estado', 'activo')
                ->first();

            if (! $movimiento) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Bloquear modificación cuando la obligación tenga pagos confirmados
            |--------------------------------------------------------------------------
            */
            

            $valorNuevo = $this->convertirMonedaAFloat(
                $valorIngresado
            );

            $movimiento->update([
                'valor_personalizado' => $valorNuevo,
                'valor' => $valorNuevo,
            ]);
        }
        
        return redirect()
            ->route('costos.estudiante', $student)
            ->with('success', 'Costos actualizados correctamente.');
    }

    


    public function asignarCostos(Student $student)
    {
        $student->load(['course']);

        $ficha = FichaCostoEstudiante::firstOrCreate(
            [
                'student_id' => $student->id,
                'periodo_lectivo_id' => $student->periodo_lectivo_id,
            ],
            [
                'sede_id' => $student->sede_id,
                'tipo_pago' => 'PRIVADO',
                'mes_causado' => 'MARZO',
            ]
        );

        $asignaciones = AsignacionConcepto::with('conceptoCobro')
            ->where('sede_id', $student->sede_id)
            ->where('periodo_lectivo_id', $student->periodo_lectivo_id)
            ->where('grado', $student->course?->grado)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $meses = [
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
        ];
        $valorMatricula = 0;
        $valorPension = 0;
        $valorCostosAcademicos = 0;
        foreach ($asignaciones as $asignacion) {
            $descripcion = Str::upper(Str::ascii($asignacion->conceptoCobro?->descripcion ?? ''));
            $valor = (int) $asignacion->tarifa_ordinaria;

            if (str_contains($descripcion, 'MATRICULA')) {
                $valorMatricula = $valor;
                continue;
            }

            if (str_contains($descripcion, 'PENSION')) {
                $valorPension = $valor;

                foreach ($meses as $numero => $mes) {
                    PensionEstudiante::updateOrCreate(
                        [
                            'ficha_costo_estudiante_id' => $ficha->id,
                            'mes_numero' => $numero,
                        ],
                        [
                            'mes' => $mes,
                            'valor_base' => $valorPension,
                            'valor_personalizado' => $valorPension,
                            'modificado_manual' => false,
                        ]
                    );
                }

                continue;
            }

            if (str_contains($descripcion, 'COSTOS ACADEMICOS')) {
                $valorCostosAcademicos = $valor;
                continue;
            }

            
            OtroCostoEstudiante::updateOrCreate(
                [
                    'ficha_costo_estudiante_id' => $ficha->id,
                    'concepto_cobro_id' => $asignacion->concepto_cobro_id,
                ],
                [
                    'nombre_concepto' => $asignacion->conceptoCobro?->descripcion ?? 'Concepto',
                    'valor_base' => $valor,
                    'valor_personalizado' => $valor,
                    'modificado_manual' => false,
                ]
            );
        }

        foreach ($meses as $numero => $mes) {
            CostoMoraEstudiante::firstOrCreate(
                [
                    'ficha_costo_estudiante_id' => $ficha->id,
                    'mes_numero' => $numero,
                ],
                [
                    'mes' => $mes,
                    'valor_mora' => 0,
                    'modificado_manual' => false,
                ]
            );
        }

        $totalDeuda =
            ($ficha->saldo_anterior ?? 0)
            + $valorMatricula
            + $valorCostosAcademicos
            + ($ficha->deudas ?? 0)
            + ($ficha->otras_deudas ?? 0)
            - ($ficha->abonos ?? 0);

        $ficha->update([
            'matricula' => $valorMatricula,
            'costos_academicos' => $valorCostosAcademicos,
            'pension_inicial' => $valorPension,
            'total_deuda' => max($totalDeuda, 0),
        ]);

        AsignacionCostosEstudiante::updateOrCreate(
            [
                'student_id' => $student->id,
                'periodo_lectivo_id' => $student->periodo_lectivo_id,
            ],
            [
                'sede_id' => $student->sede_id,

                'saldo_anterior' => (float) ($ficha->saldo_anterior ?? 0),

                'matricula' => $valorMatricula,
                'costos_academicos' => $valorCostosAcademicos,

                'pension_febrero' => $valorPension,
                'pension_marzo' => $valorPension,
                'pension_abril' => $valorPension,
                'pension_mayo' => $valorPension,
                'pension_junio' => $valorPension,
                'pension_julio' => $valorPension,
                'pension_agosto' => $valorPension,
                'pension_septiembre' => $valorPension,
                'pension_octubre' => $valorPension,
                'pension_noviembre' => $valorPension,

                'otros_costos_academicos' => 0,
            ]
        );

        return redirect()
            ->route('costos.estudiante', $student)
            ->with('success', 'Costos asignados correctamente.');
    }

    private function convertirMonedaAFloat(mixed $valor): float
    {
        if ($valor === null || trim((string) $valor) === '') {
            return 0;
        }

        $valorLimpio = str_replace(
            ['$', ' ', '.'],
            '',
            (string) $valor
        );

        $valorLimpio = str_replace(',', '.', $valorLimpio);

        return round((float) $valorLimpio, 2);
    }

}