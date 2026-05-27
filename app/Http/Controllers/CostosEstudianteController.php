<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\FichaCostoEstudiante;
use App\Models\AsignacionConcepto;
use App\Models\PensionEstudiante;
use App\Models\OtroCostoEstudiante;
use App\Models\CostoMoraEstudiante;
use Illuminate\Support\Str;

class CostosEstudianteController extends Controller
{
    public function index(Student $student)
    {
        $student->load(['sede', 'periodoLectivo', 'course']);

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

        return view('costos-estudiante.index', [
            'student' => $student,
            'ficha' => $ficha,
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
            + (float) $totalOtrosCostos
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
                }
            }
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
                }
            }
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

        return redirect()
            ->route('costos.estudiante', $student)
            ->with('success', 'Costos asignados correctamente.');
    }

}