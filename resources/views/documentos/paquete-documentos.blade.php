<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>{{ $nombreArchivo }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #334155;
        }

        .estado {
            text-align: center;
        }

        .estado h2 {
            margin: 0 0 8px;
            font-size: 18px;
        }

        .estado p {
            margin: 0;
            font-size: 14px;
            color: #64748b;
        }

        iframe {
            display: none;
        }
    </style>
</head>

<body>

<div class="estado">
    <h2 id="titulo">
        Preparando documentos...
    </h2>

    <p id="mensaje">
        Preparando los documentos del estudiante.
    </p>
</div>

<iframe
    src="{{ route('students.pdf.formato', [
        'student' => $student,
        'tipo' => 'contrato_servicios',
        'modo' => 'paquete',
    ]) }}"
></iframe>

<iframe
    src="{{ route('students.pdf.formato', [
        'student' => $student,
        'tipo' => 'comportamiento',
        'modo' => 'paquete',
    ]) }}"
></iframe>

<iframe
    src="{{ route('students.pdf.formato', [
        'student' => $student,
        'tipo' => 'pagare',
        'modo' => 'paquete',
    ]) }}"
></iframe>

<iframe
    src="{{ route('students.pdf.formato', [
        'student' => $student,
        'tipo' => 'autorizacion_consulta',
        'modo' => 'paquete',
    ]) }}"
></iframe>

<iframe
    src="{{ route('students.pdf.formato', [
        'student' => $student,
        'tipo' => 'autorizacion_pagare',
        'modo' => 'paquete',
    ]) }}"
></iframe>

<iframe
    src="{{ route('students.pdf.formato', [
        'student' => $student,
        'tipo' => 'autorizacion_datos',
        'modo' => 'paquete',
    ]) }}"
></iframe>

<script>
    const nombreArchivo = @json($nombreArchivo);

    const documentosRecibidos = {
        contrato: null,
        comportamiento: null,
        pagare: null,
        autorizacion_consulta: null,
        autorizacion_pagare: null,
        autorizacion_datos: null,
    };

    window.addEventListener(
        'message',
        (evento) => {
            if (
                evento.origin
                !== window.location.origin
            ) {
                return;
            }

            const datos = evento.data ?? {};

            if (
                datos.tipo
                !== 'documento-pdf-generado'
            ) {
                return;
            }

            if (
                !Object.prototype.hasOwnProperty.call(
                    documentosRecibidos,
                    datos.documento
                )
            ) {
                return;
            }

            documentosRecibidos[
                datos.documento
            ] = new Uint8Array(
                datos.bytes
            );

            console.log(
                `${datos.documento} recibido:`,
                documentosRecibidos[
                    datos.documento
                ].length,
                'bytes'
            );

            comprobarDocumentos();
        }
    );

    function comprobarDocumentos() {
        const completos = Object.values(
            documentosRecibidos
        ).every(
            documento => documento !== null
        );

        if (!completos) {
            return;
        }

        document.getElementById(
            'titulo'
        ).textContent =
            'Documentos preparados';

        document.getElementById(
            'mensaje'
        ).textContent =
            'Los documentos fueron generados correctamente.';

        console.log(
            'DOCUMENTOS PREPARADOS:',
            {
                contrato:
                    documentosRecibidos
                        .contrato.length,

                comportamiento:
                    documentosRecibidos
                        .comportamiento.length,

                nombreArchivo,
            }
        );
        
        unirDocumentos();
    }

    async function unirDocumentos() {
        const formulario = new FormData();

        formulario.append(
            '_token',
            '{{ csrf_token() }}'
        );

        formulario.append(
            'contrato',
            new Blob(
                [
                    documentosRecibidos
                        .contrato
                ],
                {
                    type: 'application/pdf',
                }
            ),
            'contrato.pdf'
        );

        formulario.append(
            'comportamiento',
            new Blob(
                [
                    documentosRecibidos
                        .comportamiento
                ],
                {
                    type: 'application/pdf',
                }
            ),
            'comportamiento.pdf'
        );

        formulario.append(
            'pagare',
            new Blob(
                [
                    documentosRecibidos
                        .pagare
                ],
                {
                    type: 'application/pdf',
                }
            ),
            'pagare.pdf'
        );

        formulario.append(
            'autorizacion_consulta',
            new Blob(
                [
                    documentosRecibidos
                        .autorizacion_consulta
                ],
                {
                    type: 'application/pdf',
                }
            ),
            'autorizacion-consulta.pdf'
        );

        formulario.append(
            'autorizacion_pagare',
            new Blob(
                [
                    documentosRecibidos
                        .autorizacion_pagare
                ],
                {
                    type: 'application/pdf',
                }
            ),
            'autorizacion-pagare.pdf'
        );

        formulario.append(
            'autorizacion_datos',
            new Blob(
                [
                    documentosRecibidos
                        .autorizacion_datos
                ],
                {
                    type: 'application/pdf',
                }
            ),
            'autorizacion-datos.pdf'
        );

        document.getElementById(
            'titulo'
        ).textContent =
            'Uniendo documentos...';

        document.getElementById(
            'mensaje'
        ).textContent =
            'Preparando el archivo final editable.';

        try {
            const respuesta = await fetch(
                @json(
                    route(
                        'students.pdf.paquete.unir',
                        [
                            'student' => $student,
                        ]
                    )
                ),
                {
                    method: 'POST',
                    body: formulario,
                    credentials: 'same-origin',
                }
            );

            if (!respuesta.ok) {
                const texto = await respuesta.text();

                console.error(
                    'Error del servidor:',
                    texto
                );

                throw new Error(
                    'No fue posible unir los documentos.'
                );
            }

            const blobFinal =
                await respuesta.blob();

            const urlPdf =
                URL.createObjectURL(
                    blobFinal
                );

            document.getElementById(
                'titulo'
            ).textContent =
                'Documento preparado';

            document.getElementById(
                'mensaje'
            ).textContent =
                'Abriendo el paquete de documentos...';

            window.location.replace(
                urlPdf
            );
        } catch (error) {
            console.error(error);

            document.getElementById(
                'titulo'
            ).textContent =
                'No fue posible generar el paquete';

            document.getElementById(
                'mensaje'
            ).textContent =
                'Revise la consola para ver el detalle.';
        }
    }
</script>

</body>
</html>