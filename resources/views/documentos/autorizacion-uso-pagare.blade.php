<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Autorización uso de pagaré —
        {{ $student->primer_nombre }}
        {{ $student->primer_apellido }}
    </title>

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
            width: min(460px, calc(100% - 32px));
            padding: 28px;
            text-align: center;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
        }

        .spinner {
            width: 38px;
            height: 38px;
            margin: 0 auto 18px;
            border: 4px solid #e2e8f0;
            border-top-color: #82211d;
            border-radius: 50%;
            animation: girar 0.8s linear infinite;
        }

        h1 {
            margin: 0;
            font-size: 18px;
            color: #1e293b;
        }

        p {
            margin: 9px 0 0;
            font-size: 14px;
            line-height: 1.5;
            color: #64748b;
        }

        .error {
            display: none;
            color: #991b1b;
        }

        @keyframes girar {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

<div class="estado">
    <div
        class="spinner"
        id="spinner"
    ></div>

    <h1 id="titulo">
        Preparando autorización de uso de pagaré
    </h1>

    <p id="mensaje">
        Cargando la información…
    </p>

    <p
        id="error"
        class="error"
    ></p>
</div>

<script src="{{ asset('vendor/pdf-lib/pdf-lib.min.js') }}"></script>

<script>
    const datosAutorizacion = {
        plantillaUrl: @json($plantillaUrl),
        nombreArchivo: @json($nombreArchivo),
        campos: @json($campos),
    };

    const modoPaquete = new URLSearchParams(
        window.location.search
    ).get('modo') === 'paquete';

    async function generarAutorizacion() {
        const spinner = document.getElementById('spinner');
        const titulo = document.getElementById('titulo');
        const mensaje = document.getElementById('mensaje');
        const error = document.getElementById('error');

        try {
            if (!window.PDFLib) {
                throw new Error(
                    'No fue posible cargar la librería PDF.'
                );
            }

            const respuesta = await fetch(
                datosAutorizacion.plantillaUrl,
                {
                    credentials: 'same-origin',
                    cache: 'no-store',
                }
            );

            if (!respuesta.ok) {
                throw new Error(
                    'No se encontró la plantilla de la autorización.'
                );
            }

            const plantillaBytes = await respuesta.arrayBuffer();

            const {
                PDFDocument,
                StandardFonts,
            } = window.PDFLib;

            const documentoPdf = await PDFDocument.load(
                plantillaBytes,
                {
                    ignoreEncryption: true,
                }
            );

            const formulario = documentoPdf.getForm();

            const fuente = await documentoPdf.embedFont(
                StandardFonts.Helvetica
            );

            const camposNoEncontrados = [];

            for (
                const [nombreCampo, valorCampo]
                of Object.entries(datosAutorizacion.campos)
            ) {
                try {
                    const campo = formulario.getTextField(
                        nombreCampo
                    );

                    const valor = String(
                        valorCampo ?? ''
                    );

                    campo.setText(valor);

                    /*
                     * Tamaño base: 10 pt.
                     *
                     * En los campos que pueden contener nombres
                     * largos se reduce progresivamente el tamaño
                     * para evitar desbordes.
                     */
                    let tamanoFuente = 10;

                    if ([
                        'Nombre1',
                        'Nombre2',
                        'Nombre',
                        'NOMBRE_2',
                    ].includes(nombreCampo)) {
                        if (valor.length > 36) {
                            tamanoFuente = 8;
                        } else if (valor.length > 28) {
                            tamanoFuente = 9;
                        }
                    }

                    /*
                    * El nombre del estudiante ya llega abreviado desde el servicio
                    * cuando el nombre completo es excepcionalmente largo.
                    */
                    if (nombreCampo === 'menores') {
                        if (valor.length > 34) {
                            tamanoFuente = 8;
                        } else if (valor.length > 28) {
                            tamanoFuente = 9;
                        }
                    }

                    /*
                    * Los correos suelen necesitar más espacio horizontal.
                    */
                    if ([
                        'Correo 1',
                        'Correo 2',
                    ].includes(nombreCampo)) {
                        if (valor.length > 32) {
                            tamanoFuente = 7;
                        } else if (valor.length > 26) {
                            tamanoFuente = 8;
                        } else {
                            tamanoFuente = 9;
                        }
                    }

                    if ([
                        'Correo 1',
                        'Correo 2',
                    ].includes(nombreCampo)) {
                        const widgets = campo.acroField.getWidgets();

                        if (widgets.length > 0) {
                            const widget = widgets[0];
                            const rectangulo = widget.getRectangle();

                            // Aprovechar el espacio disponible hacia la derecha.
                            // Mantener el correo dentro de su propia columna.
                            const anchoMaximo = 155;

                            widget.setRectangle({
                                x: rectangulo.x,
                                y: rectangulo.y,
                                width: anchoMaximo,
                                height: rectangulo.height,
                            });
                        }
                    }

                    /*
                    * El campo del mes es estrecho.
                    * Se reduce un punto para meses largos.
                    */
                    if (nombreCampo === 'Mes') {
                        tamanoFuente = 9;
                    }
                    /*
                    * Direcciones.
                    *
                    * En GA-F13 existe espacio suficiente hacia la derecha,
                    * por lo que ampliamos el campo y reducimos la fuente
                    * únicamente para direcciones largas.
                    */
                    if ([
                        'Dirección',
                        'Dirección_2',
                    ].includes(nombreCampo)) {

                        if (valor.length > 40) {
                            tamanoFuente = 7;
                        } else if (valor.length > 32) {
                            tamanoFuente = 8;
                        } else if (valor.length > 24) {
                            tamanoFuente = 9;
                        }
                    }
                    // Ajustar el tamaño de letra al ancho real del campo.
                    if (valor.trim() !== '') {
                        const widgets = campo.acroField.getWidgets();

                        if (widgets.length > 0) {
                            const rectangulo = widgets[0].getRectangle();

                            // Margen interno para evitar que el texto toque los bordes.
                            const anchoDisponible = Math.max(
                                1,
                                rectangulo.width - 6
                            );

                            // Reducir progresivamente hasta que el texto quepa.
                            while (
                                tamanoFuente > 6 &&
                                fuente.widthOfTextAtSize(
                                    valor,
                                    tamanoFuente
                                ) > anchoDisponible
                            ) {
                                tamanoFuente -= 0.5;
                            }
                        }
                    }

                    campo.setFontSize(tamanoFuente);

                } catch (campoError) {
                    camposNoEncontrados.push(
                        nombreCampo
                    );

                    console.warn(
                        `Campo no encontrado: ${nombreCampo}`,
                        campoError
                    );
                }
            }

            formulario.updateFieldAppearances(
                fuente
            );

            const pdfFinal = await documentoPdf.save({
                useObjectStreams: false,
                addDefaultPage: false,
                updateFieldAppearances: false,
            });

            if (modoPaquete) {
                window.parent.postMessage(
                    {
                        tipo: 'documento-pdf-generado',
                        documento: 'autorizacion_pagare',
                        bytes: Array.from(pdfFinal),
                    },
                    window.location.origin
                );

                titulo.textContent =
                    'Autorización de pagaré preparada';

                mensaje.textContent =
                    'Documento preparado para el paquete.';

                return;
            }

            const archivo = new Blob(
                [pdfFinal],
                {
                    type: 'application/pdf',
                }
            );

            const urlPdf = URL.createObjectURL(
                archivo
            );

            titulo.textContent =
                'Autorización preparada';

            mensaje.textContent =
                'Abriendo el documento editable…';

            window.location.replace(
                urlPdf
            );

        } catch (excepcion) {
            console.error(excepcion);

            spinner.style.display = 'none';

            titulo.textContent =
                'No fue posible preparar la autorización';

            mensaje.style.display = 'none';

            error.style.display = 'block';

            error.textContent =
                excepcion.message
                ?? 'Ocurrió un error inesperado.';
        }
    }

    document.addEventListener(
        'DOMContentLoaded',
        generarAutorizacion
    );
</script>

</body>
</html>