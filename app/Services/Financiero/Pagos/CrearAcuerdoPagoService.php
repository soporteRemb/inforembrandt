<?php

namespace App\Services\Financiero\Pagos;

use App\Models\AcuerdoPagoEstudiante;
use App\Models\EvidenciaAcuerdoPago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class CrearAcuerdoPagoService
{
    public function crear(
        int $studentId,
        int $sedeId,
        int $periodoLectivoId,
        string $personaAcuerdo,
        ?string $parentesco,
        string $fechaCompromiso,
        ?float $valorComprometido,
        string $textoAcuerdo,
        int $registradoPor,
        array $evidencias = [],
    ): AcuerdoPagoEstudiante {
        $archivosGuardados = [];

        try {
            return DB::transaction(function () use (
                $studentId,
                $sedeId,
                $periodoLectivoId,
                $personaAcuerdo,
                $parentesco,
                $fechaCompromiso,
                $valorComprometido,
                $textoAcuerdo,
                $registradoPor,
                $evidencias,
                &$archivosGuardados,
            ) {
                $acuerdo = AcuerdoPagoEstudiante::create([
                    'sede_id' => $sedeId,
                    'periodo_lectivo_id' => $periodoLectivoId,
                    'student_id' => $studentId,

                    'texto_acuerdo' => trim($textoAcuerdo),
                    'persona_acuerdo' => trim($personaAcuerdo),

                    'parentesco' => filled($parentesco)
                        ? trim((string) $parentesco)
                        : null,

                    'fecha_compromiso' => $fechaCompromiso,

                    'valor_comprometido' =>
                        $valorComprometido !== null
                            ? round($valorComprometido, 2)
                            : null,

                    'estado' =>
                        AcuerdoPagoEstudiante::ESTADO_VIGENTE,

                    'registrado_por' => $registradoPor,
                    'registrado_en' => now(),
                ]);

                foreach ($evidencias as $archivo) {
                    if (! $archivo instanceof TemporaryUploadedFile) {
                        continue;
                    }

                    $extension = strtolower(
                        $archivo->getClientOriginalExtension()
                    );

                    $nombreArchivo = sprintf(
                        '%s-%s.%s',
                        now()->format('YmdHis'),
                        Str::uuid(),
                        $extension
                    );

                    $directorio = sprintf(
                        'acuerdos-pago/%d/%d',
                        $studentId,
                        $acuerdo->id
                    );

                    $ruta = $archivo->storeAs(
                        $directorio,
                        $nombreArchivo,
                        'public'
                    );

                    $archivosGuardados[] = $ruta;

                    EvidenciaAcuerdoPago::create([
                        'acuerdo_pago_estudiante_id' =>
                            $acuerdo->id,

                        'nombre_original' =>
                            $archivo->getClientOriginalName(),

                        'ruta' => $ruta,

                        'tipo_archivo' =>
                            $archivo->getMimeType(),

                        'tamano' =>
                            $archivo->getSize(),

                        'cargado_por' =>
                            $registradoPor,

                        'cargado_en' =>
                            now(),
                    ]);
                }

                return $acuerdo->fresh([
                    'registradoPor',
                    'evidencias',
                ]);
            }, 3);
        } catch (Throwable $exception) {
            foreach ($archivosGuardados as $ruta) {
                Storage::disk('public')->delete($ruta);
            }

            throw $exception;
        }
    }
}