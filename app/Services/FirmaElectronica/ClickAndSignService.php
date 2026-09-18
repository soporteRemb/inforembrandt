<?php

namespace App\Services\FirmaElectronica;

use App\Exceptions\EnvioFirmaInciertoException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClickAndSignService
{
    private string $url;
    private string $user;
    private string $apiKey;

    public function __construct()
    {
        $this->url = rtrim(
            (string) config(
                'services.click_and_sign.url'
            ),
            '/'
        );

        $this->user = (string) config(
            'services.click_and_sign.user'
        );

        $this->apiKey = (string) config(
            'services.click_and_sign.api_key'
        );
    }

    public function crearConfiguracion(): array
    {
        $this->validarCredenciales();

        $nombreEmpresa = (string) config(
            'services.click_and_sign.company_name'
        );

        $nitEmpresa = (string) config(
            'services.click_and_sign.company_vat'
        );

        $remitenteSms = (string) config(
            'services.click_and_sign.sms_sender',
            'Rembrandt'
        );

        $callbackUrl = trim(
            (string) config(
                'services.click_and_sign.callback_url'
            )
        );

        if (
            $callbackUrl === ''
            || ! str_starts_with(
                $callbackUrl,
                'https://'
            )
        ) {
            throw new RuntimeException(
                'Click & Sign requiere una URL HTTPS válida para el callback de firma.'
            );
        }

        if (
            $nombreEmpresa === ''
            || $nitEmpresa === ''
            || $remitenteSms === ''
        ) {
            throw new RuntimeException(
                'Los datos institucionales de Click & Sign están incompletos.'
            );
        }

        $respuesta = Http::withHeaders([
            'Authorization' =>
                'x-api-key ' . $this->apiKey,

            'Accept' =>
                'application/json',

            'Content-Type' =>
                'application/json; charset=utf-8',
        ])
        ->timeout(30)
        ->post(
            $this->url . '/set_config',
            [
                'request' => 'SET_CONFIG',

                'user' => $this->user,

                'config' => [
                    'name' =>
                        'Rembrandt Matricula OTP',

                    'expire_lapse' =>
                        72,
                    'auto_cancel' =>
                        'Y',

                    'signature_cb_url' =>
                        $callbackUrl,

                    'default_sms_sender' =>
                        $remitenteSms,

                    'default_email_from_name' =>
                        'Colegio Rembrandt',

                    'registered_company_name' =>
                        $nombreEmpresa,

                    'registered_company_vat_number' =>
                        $nitEmpresa,

                    'registered_langs' =>
                        'ES',

                    'lang' =>
                        'ES',

                    'sms' => [
                        [
                            'registered' => 'N',
                            'type' => 'start',
                            'text' =>
                                'Tiene documentos de matricula pendientes de firma. Acceda aqui: #url#',
                        ],
                        [
                            'registered' => 'Y',
                            'type' => 'otp',
                            'text' =>
                                'Su codigo para firmar los documentos de matricula es #otp#',
                        ],
                    ],

                    'email' => [
                        [
                            'registered' => 'N',
                            'type' => 'start',
                            'subject' =>
                                'Documentos de matricula pendientes de firma',
                            'body_free_text' =>
                                'Tiene documentos de matricula pendientes de firma. Acceda al proceso mediante el enlace de este correo.',
                        ],
                    ],

                    'landing' => [
                        'signature_type' =>
                            'on_sign',

                        'signature_on_sign_required_elements' => [
                            'otp' =>
                                'Y',

                            'otp_length' =>
                                6,

                            'otp_max_retries' =>
                                3,

                            'otp_sending' =>
                                'on_click',
                        ],

                        'enable_button' =>
                            'on_eof',
                    ],
                ],
            ]
        );

        if (! $respuesta->successful()) {
            throw new RuntimeException(
                'Click & Sign respondió con HTTP '
                . $respuesta->status()
                . '.'
            );
        }

        $datos = $respuesta->json();

        if (
            ! is_array($datos)
            || (int) ($datos['code'] ?? 0) !== 200
        ) {
            throw new RuntimeException(
                'Click & Sign rechazó SET_CONFIG: '
                . (
                    $datos['status']
                    ?? 'respuesta desconocida'
                )
            );
        }

        if (
            empty(
                $datos['config']['config_id']
            )
        ) {
            throw new RuntimeException(
                'Click & Sign no devolvió config_id.'
            );
        }

        return $datos;
    }

    public function prepararSolicitudFirma(
        array $firmantes,
        string $rutaPdf,
        string $nombreArchivo,
        string $requestId,
        string $contractId
    ): array {
        $configId = (int) config(
            'services.click_and_sign.config_id'
        );

        if ($configId <= 0) {
            throw new RuntimeException(
                'Click & Sign no tiene config_id configurado.'
            );
        }

        if (! is_file($rutaPdf)) {
            throw new RuntimeException(
                'No se encontró el PDF que se debe enviar a Click & Sign.'
            );
        }

        if ($firmantes === []) {
            throw new RuntimeException(
                'La solicitud de firma no tiene firmantes.'
            );
        }

        $contenido = file_get_contents(
            $rutaPdf
        );

        if ($contenido === false || $contenido === '') {
            throw new RuntimeException(
                'No fue posible leer el PDF que se debe enviar.'
            );
        }

        $signatories = [];

        foreach ($firmantes as $firmante) {
            $signatories[] = [
                'phone' => $firmante['phone'],
                'email' => $firmante['email'],
                'name' => $firmante['name'],
                'id_number' => $firmante['id_number'],
            ];
        }

        return [
            'request' => 'START_SIGNATURE',
            'request_id' => $requestId,
            'user' => $this->user,

            'signature' => [
                'config_id' => $configId,
                'contract_id' => $contractId,

                'level' => [
                    [
                        'level_order' => 0,

                        /*
                        * Firma mancomunada:
                        * deben firmar todos los firmantes
                        * definidos en este nivel.
                        *
                        * Actualmente será 1 (Acudiente).
                        */
                        'required_signatories_to_complete_level' =>
                            count($signatories),

                        'signatories' => $signatories,
                    ],
                ],

                'file' => [
                    [
                        'filename' => $nombreArchivo,

                        'content' => $this->codificarArchivo(
                            $contenido
                        ),

                        'sign_on_landing' => 'Y',
                    ],
                ],
            ],
        ];
    }

    public function enviarFirma(
        array $firmantes,
        string $rutaPdf,
        string $nombreArchivo,
        string $requestId,
        string $contractId
    ): array {
        $this->validarCredenciales();

        $payload = $this->prepararSolicitudFirma(
            $firmantes,
            $rutaPdf,
            $nombreArchivo,
            $requestId,
            $contractId
        );

        /*
        |--------------------------------------------------------------------------
        | START_SIGNATURE
        |--------------------------------------------------------------------------
        |
        | Si se pierde la conexión o Click & Sign responde con un error 5xx,
        | no podemos saber con certeza si la firma alcanzó a ser creada.
        | Por eso estos casos se consideran "envío incierto" y NO deben
        | reintentarse automáticamente.
        |
        */

        try {
            $respuesta = Http::withHeaders([
                'Authorization' =>
                    'x-api-key ' . $this->apiKey,

                'Accept' =>
                    'application/json',

                'Content-Type' =>
                    'application/json; charset=utf-8',
            ])
            ->timeout(60)
            ->post(
                $this->url . '/start_signature',
                $payload
            );
        } catch (ConnectionException $e) {
            throw new EnvioFirmaInciertoException(
                'No fue posible confirmar la respuesta de START_SIGNATURE.',
                0,
                $e
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Error temporal o incierto del proveedor
        |--------------------------------------------------------------------------
        */

        if ($respuesta->serverError()) {
            throw new EnvioFirmaInciertoException(
                'Click & Sign respondió con HTTP '
                . $respuesta->status()
                . ' durante START_SIGNATURE.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Error conocido de la petición
        |--------------------------------------------------------------------------
        */

        if (! $respuesta->successful()) {
            throw new RuntimeException(
                'Click & Sign respondió con HTTP '
                . $respuesta->status()
                . '.'
            );
        }

        $datos = $respuesta->json();

        /*
        |--------------------------------------------------------------------------
        | Respuesta funcional de Click & Sign
        |--------------------------------------------------------------------------
        */

        if (
            ! is_array($datos)
            || (int) ($datos['code'] ?? 0) !== 200
            || ($datos['status'] ?? '') !== 'Success'
        ) {
            throw new RuntimeException(
                'Click & Sign rechazó START_SIGNATURE: '
                . (
                    $datos['status']
                    ?? 'respuesta desconocida'
                )
            );
        }

        if (
            empty(
                $datos['signature']['signature_id']
            )
        ) {
            throw new RuntimeException(
                'Click & Sign no devolvió signature_id.'
            );
        }

        return $datos;
    }

    public function obtenerListaArchivos(
        string $signatureId,
        ?string $fileGroup = null
    ): array {
        $this->validarCredenciales();

        $signatureId = trim($signatureId);

        if ($signatureId === '') {
            throw new RuntimeException(
                'No se recibió signature_id para consultar los archivos.'
            );
        }

        $payload = [
            'request' => 'GET_FILE_LIST',
            'user' => $this->user,
            'signature_id' => $signatureId,
        ];

        if (
            $fileGroup !== null
            && trim($fileGroup) !== ''
        ) {
            $payload['file_group'] = trim(
                $fileGroup
            );
        }

        $respuesta = Http::withHeaders([
            'Authorization' =>
                'x-api-key ' . $this->apiKey,

            'Accept' =>
                'application/json',

            'Content-Type' =>
                'application/json; charset=utf-8',
        ])
        ->timeout(30)
        ->post(
            $this->url . '/get_file_list',
            $payload
        );

        if (! $respuesta->successful()) {
            throw new RuntimeException(
                'Click & Sign respondió con HTTP '
                . $respuesta->status()
                . ' al consultar los archivos.'
            );
        }

        $datos = $respuesta->json();

        if (
            ! is_array($datos)
            || (int) ($datos['code'] ?? 0) !== 200
            || ($datos['status'] ?? '') !== 'Success'
        ) {
            throw new RuntimeException(
                'Click & Sign rechazó GET_FILE_LIST: '
                . (
                    $datos['status']
                    ?? 'respuesta desconocida'
                )
            );
        }

        if (
            ! isset($datos['file_list']['files'])
            || ! is_array(
                $datos['file_list']['files']
            )
        ) {
            throw new RuntimeException(
                'Click & Sign no devolvió una lista de archivos válida.'
            );
        }

        return $datos;
    }

    public function obtenerArchivo(
        int $fileId
    ): string {
        $this->validarCredenciales();

        if ($fileId <= 0) {
            throw new RuntimeException(
                'El file_id de Click & Sign no es válido.'
            );
        }

        $respuesta = Http::withHeaders([
            'Authorization' =>
                'x-api-key ' . $this->apiKey,

            'Accept' =>
                'application/octet-stream',

            'Content-Type' =>
                'application/json; charset=utf-8',
        ])
        ->timeout(60)
        ->post(
            $this->url . '/get_file',
            [
                'request' => 'GET_FILE',
                'user' => $this->user,
                'file_id' => $fileId,
            ]
        );

        if (! $respuesta->successful()) {
            throw new RuntimeException(
                'Click & Sign respondió con HTTP '
                . $respuesta->status()
                . ' al descargar el archivo.'
            );
        }

        $contenido = $respuesta->body();

        if ($contenido === '') {
            throw new RuntimeException(
                'Click & Sign devolvió un archivo vacío.'
            );
        }

        if (! str_starts_with($contenido, '%PDF-')) {
            throw new RuntimeException(
                'Click & Sign no devolvió un archivo PDF válido.'
            );
        }

        return $contenido;
    }

    private function codificarArchivo(
        string $contenido
    ): string {
        return rtrim(
            strtr(
                base64_encode($contenido),
                '+/',
                '-_'
            ),
            '='
        );
    }

    private function validarCredenciales(): void
    {
        if (
            $this->url === ''
            || $this->user === ''
            || $this->apiKey === ''
        ) {
            throw new RuntimeException(
                'La configuración de Click & Sign está incompleta.'
            );
        }
    }
}