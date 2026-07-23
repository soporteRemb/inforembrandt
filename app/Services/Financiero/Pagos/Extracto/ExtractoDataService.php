<?php

namespace App\Services\Financiero\Pagos\Extracto;

use App\Models\Student;
use App\Models\User;
use App\Services\Institucion\InstitucionService;
use App\Services\Financiero\Pagos\PagosService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExtractoDataService
{
    public function __construct(
        protected PagosService $pagosService,
        protected InstitucionService $institucionService,
    ) {
    }

    public function consultar(
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
        int $generadoPor,
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Estudiante
        |--------------------------------------------------------------------------
        */
        $student = Student::query()
            ->with([
                'course',
            ])
            ->whereKey($studentId)
            ->where('sede_id', $sedeId)
            ->where(
                'periodo_lectivo_id',
                $periodoLectivoId
            )
            ->first();

        if (! $student) {
            throw new ModelNotFoundException(
                'No se encontró el estudiante solicitado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Obligaciones pendientes
        |--------------------------------------------------------------------------
        */
        $obligaciones = $this->pagosService
            ->cartera()
            ->obtenerObligaciones(
                studentId: $studentId,
                sedeId: $sedeId,
                periodoLectivoId: $periodoLectivoId,
            )
            ->sortBy(function (array $obligacion) {
                return [
                    /*
                     * Primero las obligaciones obligatorias.
                     */
                    ($obligacion['obligatorio'] ?? false)
                        ? 0
                        : 1,

                    /*
                     * Dentro de cada grupo, primero las más antiguas.
                     */
                    $obligacion['fecha_vencimiento']
                        ? (string) $obligacion['fecha_vencimiento']
                        : '9999-12-31',

                    (int) (
                        $obligacion['mes_numero']
                        ?? 0
                    ),

                    (int) (
                        $obligacion['id']
                        ?? 0
                    ),
                ];
            })
            ->map(function (array $obligacion) {
                return [
                    'id' =>
                        (int) $obligacion['id'],

                    'concepto' =>
                        (string) (
                            $obligacion['concepto']
                            ?? 'Concepto sin nombre'
                        ),

                    'mes' =>
                        trim(
                            (string) (
                                $obligacion['mes']
                                ?? ''
                            )
                        ),

                    /*
                     * En el documento se mostrará como "Valor".
                     */
                    'valor' =>
                        round(
                            (float) (
                                $obligacion['valor_causado']
                                ?? 0
                            ),
                            2
                        ),

                    /*
                     * En el documento se mostrará como "Pagado".
                     */
                    'pagado' =>
                        round(
                            (float) (
                                $obligacion['valor_aplicado']
                                ?? 0
                            ),
                            2
                        ),

                    /*
                     * En el documento se mostrará como "Pendiente".
                     */
                    'pendiente' =>
                        round(
                            (float) (
                                $obligacion['saldo_pendiente']
                                ?? 0
                            ),
                            2
                        ),

                    /*
                     * Solo se conserva para ordenar.
                     * No se mostrará al acudiente.
                     */
                    'obligatorio' =>
                        (bool) (
                            $obligacion['obligatorio']
                            ?? false
                        ),
                ];
            })
            ->filter(
                fn (array $obligacion) =>
                    $obligacion['pendiente'] > 0
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Resumen utilizado únicamente para obtener saldo a favor
        |--------------------------------------------------------------------------
        */
        $resumen = $this->pagosService
            ->cartera()
            ->obtenerResumen(
                studentId: $studentId,
                sedeId: $sedeId,
                periodoLectivoId: $periodoLectivoId,
            );

        $totalObligaciones = round(
            (float) $obligaciones->sum(
                fn (array $obligacion) =>
                    $obligacion['pendiente']
            ),
            2
        );

        $saldoFavor = round(
            (float) (
                $resumen['saldo_favor']
                ?? 0
            ),
            2
        );

        $totalPendiente = max(
            0,
            round(
                $totalObligaciones - $saldoFavor,
                2
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Datos institucionales y auditoría
        |--------------------------------------------------------------------------
        */
        $institucion =
            $this->institucionService->datos(
                $sedeId
            );

        $usuario = User::query()
            ->find($generadoPor);

        $nombreEstudiante = trim(
            implode(
                ' ',
                array_filter([
                    $student->primer_nombre,
                    $student->segundo_nombre,
                    $student->primer_apellido,
                    $student->segundo_apellido,
                ])
            )
        );

        return [
            'institucion' => [
                'id' =>
                    $institucion['id'] ?? $sedeId,

                'nombre' =>
                    $institucion['nombre']
                    ?? 'Institución educativa',

                'direccion' =>
                    $institucion['direccion']
                    ?? null,

                'telefono' =>
                    $institucion['telefono']
                    ?? null,

                'email' =>
                    $institucion['email']
                    ?? null,

                'nit' =>
                    $institucion['nit']
                    ?? null,

                'logo' =>
                    $institucion['logo']
                    ?? null,

                'pie_documentos' =>
                    $institucion['pie_documentos']
                    ?? null,
            ],

            'estudiante' => [
                'id' =>
                    (int) $student->id,

                'nombre' =>
                    $nombreEstudiante,

                'codigo' =>
                    $student->codigo
                    ?: '—',

                'documento' =>
                    $student->documento
                    ?: '—',

                'grado' =>
                    $student->course?->grado
                    ?: $student->ultimo_grado
                    ?: '—',

                'curso' =>
                    $student->course?->curso
                    ?? $student->course?->descripcion
                    ?? '—',
            ],

            'fecha_corte' =>
                now()->format('d/m/Y'),

            'obligaciones' =>
                $obligaciones->toArray(),

            'cantidad_obligaciones' =>
                $obligaciones->count(),

            'total_obligaciones' =>
                $totalObligaciones,

            /*
             * El PDF solo mostrará esta línea cuando sea mayor que cero.
             */
            'saldo_favor' =>
                $saldoFavor,

            'total_pendiente' =>
                $totalPendiente,

            'generado_por' =>
                $usuario?->name
                ?? $usuario?->nombre
                ?? 'Usuario no disponible',

            'generado_en' =>
                now()->format('d/m/Y h:i a'),
        ];
    }
}