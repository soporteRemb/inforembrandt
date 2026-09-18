<?php

namespace App\Http\Controllers;

use App\Models\FirmaElectronicaEstudiante;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcesarDocumentoFirmadoJob;

class ClickAndSignCallbackController extends Controller
{
    public function firma(Request $request): Response
    {
        /*
         * Click & Sign envía el callback mediante POST
         * application/x-www-form-urlencoded.
         */
        $signatureId = trim(
            (string) $request->input('signature_id', '')
        );

        $contractId = trim(
            (string) $request->input('contract_id', '')
        );

        $estado = trim(
            (string) $request->input('status', '')
        );

        $fechaEstado = trim(
            (string) $request->input('status_date', '')
        );

        /*
         * Si la notificación no tiene los datos mínimos,
         * respondemos 204 para evitar reintentos inútiles,
         * pero dejamos constancia en el log.
         */
        if (
            $signatureId === ''
            || $contractId === ''
            || $estado === ''
        ) {
            Log::warning(
                'Callback Click & Sign incompleto.',
                [
                    'signature_id' =>
                        $signatureId !== '',

                    'contract_id' =>
                        $contractId !== '',

                    'status' =>
                        $estado !== '',
                ]
            );

            return response('', 204);
        }

        /*
         * Contract ID es generado por nuestro sistema.
         * Lo utilizamos para localizar la operación.
         */
        $firma = FirmaElectronicaEstudiante::query()
            ->where(
                'contract_id',
                $contractId
            )
            ->first();

        if (! $firma) {
            Log::warning(
                'Callback Click & Sign sin operación local.',
                [
                    'contract_id' => $contractId,
                    'signature_id' => $signatureId,
                    'status' => $estado,
                ]
            );

            return response('', 204);
        }

        /*
        * Si ya conocemos el signature_id, debe coincidir
        * exactamente con el recibido.
        */
        if (
            $firma->signature_id !== null
            && $firma->signature_id !== $signatureId
        ) {
            Log::warning(
                'Callback Click & Sign con signature_id diferente.',
                [
                    'firma_id' => $firma->id,
                    'contract_id' => $contractId,
                ]
            );

            return response('', 204);
        }

        /*
        * El callback puede llegar antes de que la respuesta
        * de START_SIGNATURE haya terminado de procesarse.
        * Como contract_id sí fue generado previamente por
        * Rembrandt, podemos registrar aquí el signature_id
        * si todavía estaba vacío.
        */
        if ($firma->signature_id === null) {
            $firma->signature_id = $signatureId;
        }

        /*
        * Estados oficiales documentados por Click & Sign.
        *
        * "signed" confirma que la firma terminó, pero todavía
        * no consideramos terminada la operación local hasta
        * almacenar el PDF definitivo.
        *
        * "stamp_generated" indica que el documento firmado
        * ya está disponible. En ese momento se encola su
        * recuperación y almacenamiento definitivo.
        */
        switch ($estado) {
            case 'new':
            case 'ready':
                if (
                    in_array(
                        $firma->estado,
                        [
                            'enviando',
                            'envio_incierto',
                            'pendiente',
                        ],
                        true
                    )
                ) {
                    $firma->estado = 'en_proceso';
                }
                break;

            case 'signed':
                /*
                |--------------------------------------------------------------------------
                | Firma completada en Click & Sign
                |--------------------------------------------------------------------------
                |
                | Solo avanzamos operaciones que todavía pertenecen al flujo activo.
                | Un callback tardío no puede revivir una operación que ya terminó
                | rechazada, expirada, cancelada o con error.
                |
                */

                if (
                    in_array(
                        $firma->estado,
                        [
                            'pendiente',
                            'enviando',
                            'envio_incierto',
                            'en_proceso',
                            'firmada_pendiente_documento',
                        ],
                        true
                    )
                ) {
                    $firma->estado =
                        'firmada_pendiente_documento';
                }

                break;

            case 'stamp_generated':
                /*
                |--------------------------------------------------------------------------
                | El documento firmado ya está disponible en Click & Sign
                |--------------------------------------------------------------------------
                |
                | El trabajo se despachará después de guardar este callback,
                | garantizando que signature_id y el estado recibido ya estén
                | persistidos antes de que un worker pueda procesarlo.
                |
                */

                break;

            case 'evidence_generated':
                /*
                * La evidencia es independiente del documento
                * firmado y no cambia nuestro estado local.
                */
                break;

            case 'declined':
                if ($firma->estado !== 'firmada') {
                    $firma->estado = 'rechazada';
                    $firma->rechazada_en = now();
                }
                break;

            case 'expired':
                if ($firma->estado !== 'firmada') {
                    $firma->estado = 'expirada';
                    $firma->expirada_en = now();
                }
                break;

            case 'failed':
            case 'otp_max_retries':
            case 'access_max_retries':
                if ($firma->estado !== 'firmada') {
                    $firma->estado = 'error';
                    $firma->ultimo_error =
                        'Click & Sign informó el estado: '
                        . $estado;
                }
                break;

            case 'cancelled':
                if ($firma->estado !== 'firmada') {
                    $firma->estado = 'cancelada';
                }
                break;

            case 'severally_level_completed':
                /*
                * No aplica al flujo actual de un único
                * Acudiente obligatorio. Lo registramos,
                * pero no modificamos el estado local.
                */
                break;

            default:
                Log::warning(
                    'Callback Click & Sign con estado no reconocido.',
                    [
                        'firma_id' => $firma->id,
                        'status' => $estado,
                    ]
                );
                break;
        }

        /*
        * Guardamos también los datos del último callback.
        * Esto nos deja trazabilidad sin almacenar información
        * sensible innecesaria.
        */
        $firma->respuesta_estado = [
            'signature_id' => $signatureId,
            'contract_id' => $contractId,
            'status' => $estado,
            'status_date' => $fechaEstado,
        ];

        $firma->save();

        if ($estado === 'stamp_generated') {
            ProcesarDocumentoFirmadoJob::dispatch(
                $firma->id
            );
        }

        Log::info(
            'Callback Click & Sign procesado.',
            [
                'firma_id' => $firma->id,
                'signature_id' => $signatureId,
                'contract_id' => $contractId,
                'status' => $estado,
                'estado_local' => $firma->estado,
            ]
        );

        /*
         * Click & Sign solo necesita un código HTTP
         * satisfactorio. No procesa el cuerpo.
         */
        return response('', 204);
    }
}