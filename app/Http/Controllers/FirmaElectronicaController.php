<?php

namespace App\Http\Controllers;

use App\Models\FirmaElectronicaEstudiante;
use App\Models\Student;
use App\Services\FirmaElectronica\AplanarPdfService;
use App\Services\FirmaElectronica\ClickAndSignService;
use App\Services\FirmaElectronica\PrepararFirmantesService;
use App\Exceptions\EnvioFirmaInciertoException;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use RuntimeException;
use Throwable;

class FirmaElectronicaController extends Controller
{
    public function datosAcudiente(
        Student $student,
        PrepararFirmantesService $firmantes
    ): JsonResponse {
        try {
            $datos = $firmantes
                ->datosAcudienteParaFirma($student);

            return response()->json([
                'ok' => true,
                'datos' => $datos,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => $e->getMessage(),
            ], 422);
        }
    }

    public function prepararEnvio(
        Request $request,
        Student $student,
        PrepararFirmantesService $firmantes,
        AplanarPdfService $aplanador,
        ClickAndSignService $clickAndSign
    ): JsonResponse {
                if (! config('services.click_and_sign.enabled')) {
            return response()->json([
                'ok' => false,
                'mensaje' =>
                    'El envío real a Click & Sign está deshabilitado en este entorno.',
                'estado' => 'deshabilitado',
            ], 503);
        }
        $request->validate([
            'correo' => [
                'required',
                'email',
                'max:255',
            ],
            'telefono' => [
                'required',
                'regex:/^3\d{9}$/',
            ],
            'pdf' => [
                'required',
                'file',
                'mimes:pdf',
                'max:25600',
            ],
        ], [
            'correo.required' =>
                'Debe indicar el correo electrónico para la firma.',

            'correo.email' =>
                'El correo electrónico no tiene un formato válido.',

            'telefono.required' =>
                'Debe indicar el celular que recibirá el SMS y el código OTP.',

            'telefono.regex' =>
                'El celular debe tener 10 dígitos y comenzar por 3.',

            'pdf.required' =>
                'Debe seleccionar el documento unificado.',

            'pdf.mimes' =>
                'El documento unificado debe ser un archivo PDF.',

            'pdf.max' =>
                'El PDF no puede superar los 25 MB.',
        ]);

        /*
        * No permitimos iniciar otra solicitud mientras
        * exista una firma activa para el estudiante.
        */
        $firmaActiva = $student
            ->firmasElectronicas()
            ->whereIn(
                'estado',
                [
                    'pendiente',
                    'enviando',
                    'en_proceso',
                    'envio_incierto',
                    'firmada_pendiente_documento',
                ]
            )
            ->exists();

        if ($firmaActiva) {
            return response()->json([
                'ok' => false,
                'mensaje' =>
                    'El estudiante ya tiene un proceso de firma electrónica activo.',
            ], 422);
        }

        /*
        * Nombre y documento salen siempre de la base
        * de datos. Correo y celular corresponden
        * únicamente a esta solicitud.
        */
        try {
            $firmante = $firmantes
                ->prepararAcudienteParaEnvio(
                    $student,
                    (string) $request->input('correo'),
                    (string) $request->input('telefono')
                );
        } catch (RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => $e->getMessage(),
            ], 422);
        }

        $requestId = Str::uuid()->toString();

        $contractId =
            'REM-'
            . $student->id
            . '-'
            . Str::uuid()->toString();

        $archivo = $request->file('pdf');

        $nombreOriginal =
            $archivo->getClientOriginalName();

        $configId = (int) config(
            'services.click_and_sign.config_id'
        );

        /*
        * Registramos la operación antes de comunicarnos
        * con el proveedor.
        */
        $firma = FirmaElectronicaEstudiante::create([
            'student_id' => $student->id,
            'iniciada_por' => auth()->id(),
            'proveedor' => 'click_and_sign',
            'estado' => 'enviando',
            'request_id' => $requestId,
            'contract_id' => $contractId,
            'config_id' => $configId,
            'nombre_archivo_original' => $nombreOriginal,
            'iniciada_en' => now(),
        ]);

        $directorioTemporal = storage_path(
            'app/private/tmp_firma_electronica/'
            . Str::uuid()
        );

        try {
            File::ensureDirectoryExists(
                $directorioTemporal
            );

            $rutaOriginal =
                $directorioTemporal
                . DIRECTORY_SEPARATOR
                . 'documento-original.pdf';

            $archivo->move(
                $directorioTemporal,
                'documento-original.pdf'
            );

            /*
            * Click & Sign recibe únicamente la copia
            * aplanada. El PDF editable nunca se guarda
            * como matrícula electrónica.
            */
            $rutaAplanada = $aplanador->aplanar(
                $rutaOriginal,
                $directorioTemporal
            );

            /*
            * A partir de esta llamada se crea el proceso
            * real en Click & Sign y pueden enviarse las
            * notificaciones configuradas.
            */
            $respuesta = $clickAndSign->enviarFirma(
                [
                    $firmante['click_and_sign'],
                ],
                $rutaAplanada,
                $nombreOriginal,
                $requestId,
                $contractId
            );

            $signatureId = (string) (
                $respuesta['signature']['signature_id']
                ?? ''
            );

            $firma->refresh();

            $datosActualizacion = [
                'signature_id' => $signatureId,
                'respuesta_inicio' => $respuesta,
                'ultimo_error' => null,
            ];

            /*
            * Solo avanzamos de "enviando" a "en_proceso".
            * Si un callback llegó antes y ya cambió el estado,
            * nunca lo hacemos retroceder.
            */
            if ($firma->estado === 'enviando') {
                $datosActualizacion['estado'] =
                    'en_proceso';
            }

            $firma->update(
                $datosActualizacion
            );

            return response()->json([
                'ok' => true,
                'mensaje' =>
                    'Los documentos fueron enviados correctamente para firma electrónica.',
                'estado' => 'en_proceso',
            ]);

        } catch (EnvioFirmaInciertoException $e) {
            $firma->refresh();

        /*
        * Solo marcamos el envío como incierto si todavía
        * estamos en el estado local "enviando".
        *
        * Si un callback ya confirmó que Click & Sign creó
        * la operación, ese estado tiene prioridad.
        */
        if ($firma->estado === 'enviando') {
            $firma->update([
                'estado' => 'envio_incierto',
                'ultimo_error' => $e->getMessage(),
            ]);
        }

            report($e);

            return response()->json([
                'ok' => false,
                'mensaje' =>
                    'No fue posible confirmar si Click & Sign recibió la solicitud. No vuelva a enviarla por ahora.',
                'estado' => 'envio_incierto',
            ], 503);
        } catch (Throwable $e) {
            $firma->refresh();

            /*
            * Un callback recibido desde Click & Sign tiene
            * prioridad sobre un error tardío del proceso HTTP.
            */
            if ($firma->estado === 'enviando') {
                $firma->update([
                    'estado' => 'error',
                    'ultimo_error' => $e->getMessage(),
                ]);
            }

            report($e);

            return response()->json([
                'ok' => false,
                'mensaje' =>
                    'No fue posible iniciar el proceso de firma electrónica.',
                'estado' => 'error',
            ], 500);
        } finally {
            if (
                File::isDirectory(
                    $directorioTemporal
                )
            ) {
                File::deleteDirectory(
                    $directorioTemporal
                );
            }
        }
    }
}