<?php

namespace App\Services\Financiero\Pagos;

use App\Models\AcuerdoPagoEstudiante;
use Illuminate\Support\Collection;

class AcuerdosPagoService
{
    public function listar(
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
    ): Collection {
        return AcuerdoPagoEstudiante::query()
            ->with([
                'registradoPor',
                'estadoModificadoPor',
                'evidencias',
            ])
            ->where('student_id', $studentId)
            ->where('sede_id', $sedeId)
            ->where(
                'periodo_lectivo_id',
                $periodoLectivoId
            )
            ->latest('fecha_compromiso')
            ->latest('id')
            ->get()
            ->map(function (
                AcuerdoPagoEstudiante $acuerdo
            ) {
                return [
                    'id' => (int) $acuerdo->id,

                    'fecha' =>
                        $acuerdo->fecha_compromiso
                            ?->format('d/m/Y'),

                    'fecha_iso' =>
                        $acuerdo->fecha_compromiso
                            ?->format('Y-m-d'),

                    'persona_acuerdo' =>
                        $acuerdo->persona_acuerdo
                        ?: 'No registrada',

                    'parentesco' =>
                        $acuerdo->parentesco
                        ?: null,

                    'realizado_por' =>
                        $acuerdo->registradoPor?->name
                        ?? $acuerdo->registradoPor?->nombre
                        ?? 'Usuario no disponible',

                    'estado_modificado_por' =>
                        $acuerdo->estadoModificadoPor?->name
                        ?? $acuerdo->estadoModificadoPor?->nombre
                        ?? null,

                    'estado_modificado_en' =>
                        $acuerdo->estado_modificado_en
                            ?->format('d/m/Y h:i a'),

                    'texto_acuerdo' =>
                        $acuerdo->texto_acuerdo,

                    'valor_comprometido' =>
                        (float) $acuerdo->valor_comprometido,

                    'estado' =>
                        $acuerdo->estado,

                    'estado_texto' =>
                        $this->estadoTexto(
                            $acuerdo->estado
                        ),

                    'cantidad_evidencias' =>
                        $acuerdo->evidencias->count(),

                    'evidencias' =>
                        $acuerdo->evidencias
                            ->map(function ($evidencia) {
                                return [
                                    'id' =>
                                        (int) $evidencia->id,

                                    'nombre_original' =>
                                        $evidencia
                                            ->nombre_original,

                                    'ruta' =>
                                        $evidencia->ruta,

                                    'tipo_archivo' =>
                                        $evidencia
                                            ->tipo_archivo,

                                    'tamano' =>
                                        (int) $evidencia
                                            ->tamano,
                                ];
                            })
                            
                            ->values()
                            ->toArray(),
                ];
            });
    }

    private function estadoTexto(
        ?string $estado
    ): string {
        return match ($estado) {
            AcuerdoPagoEstudiante::ESTADO_VIGENTE =>
                'Vigente',

            AcuerdoPagoEstudiante::ESTADO_CUMPLIDO =>
                'Cumplido',

            AcuerdoPagoEstudiante::ESTADO_INCUMPLIDO =>
                'Incumplido',

            AcuerdoPagoEstudiante::ESTADO_VENCIDO =>
                'Vencido',

            AcuerdoPagoEstudiante::ESTADO_ANULADO =>
                'Anulado',

            default =>
                ucfirst((string) $estado),
        };
    }
}