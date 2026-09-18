<?php

namespace App\Jobs;

use App\Models\FirmaElectronicaEstudiante;
use App\Services\FirmaElectronica\ProcesarDocumentoFirmadoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcesarDocumentoFirmadoJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 120;

    public array $backoff = [
        10,
        30,
        60,
        120,
    ];

    public function __construct(
        public int $firmaId
    ) {
    }

    public function handle(
        ProcesarDocumentoFirmadoService $procesador
    ): void {

        $firma = FirmaElectronicaEstudiante::find(
            $this->firmaId
        );

        /*
        |--------------------------------------------------------------------------
        | La solicitud pudo eliminarse mientras el trabajo estaba pendiente
        |--------------------------------------------------------------------------
        */

        if (! $firma) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Idempotencia
        |--------------------------------------------------------------------------
        */

        if ($firma->estado === 'firmada') {
            return;
        }

        $procesador->procesar(
            $firma
        );
    }

    public function failed(
        ?Throwable $exception
    ): void {

        $firma = FirmaElectronicaEstudiante::find(
            $this->firmaId
        );

        if (
            ! $firma
            || $firma->estado === 'firmada'
        ) {
            return;
        }

        $firma->forceFill([
            'estado' =>
                'firmada_pendiente_documento',

            'ultimo_error' =>
                $exception?->getMessage()
                ?: 'No fue posible recuperar el PDF firmado de Click & Sign.',
        ])->save();
    }
}