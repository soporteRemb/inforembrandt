<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Contrato de servicios —
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
        Preparando contrato de servicios
    </h1>

    <p id="mensaje">
        Cargando la información del estudiante…
    </p>

    <p
        id="error"
        class="error"
    ></p>
</div>

<script src="{{ asset('vendor/pdf-lib/pdf-lib.min.js') }}"></script>

<script>
    const datosContrato = {
        plantillaUrl: @json($plantillaUrl),
        nombreArchivo: @json($nombreArchivo),
        campos: @json($campos),
    };
    const modoPaquete = new URLSearchParams(
        window.location.search
    ).get('modo') === 'paquete';

    async function generarContrato() {
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
                datosContrato.plantillaUrl,
                {
                    credentials: 'same-origin',
                    cache: 'no-store',
                }
            );

            if (!respuesta.ok) {
                throw new Error(
                    'No se encontró la plantilla del contrato.'
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

            /**
             * Desplaza físicamente un campo AcroForm.
             *
             * En PDF, disminuir Y mueve el campo hacia abajo.
             * Las medidas están expresadas en puntos PDF.
             */
            function desplazarCampo(nombreCampo, desplazamientoX = 0, desplazamientoY = 0) {
                try {
                    const campo = formulario.getField(nombreCampo);
                    const widgets = campo.acroField.getWidgets();

                    widgets.forEach((widget) => {
                        const rectangulo = widget.getRectangle();

                        widget.setRectangle({
                            x: rectangulo.x + desplazamientoX,
                            y: rectangulo.y + desplazamientoY,
                            width: rectangulo.width,
                            height: rectangulo.height,
                        });
                    });
                } catch (error) {
                    console.warn(
                        `No fue posible desplazar el campo: ${nombreCampo}`,
                        error
                    );
                }
            }

            /*
             * Helvetica admite los caracteres utilizados
             * normalmente en nombres y direcciones en español.
             */
            const fuente = await documentoPdf.embedFont(
                StandardFonts.Helvetica
            );

            const camposNoEncontrados = [];

            for (
                const [nombreCampo, valorCampo]
                of Object.entries(datosContrato.campos)
            ) {
                try {
                    const campo = formulario.getTextField(
                        nombreCampo
                    );

                    const valor = String(valorCampo ?? '');

                    campo.setText(valor);

                    /*
                    * Tamaño normal del contrato.
                    */
                    let tamanoFuente = 11;

                    /*
                    * Nombres del contrato.
                    *
                    * El estudiante ya llega abreviado desde el servicio
                    * cuando su nombre completo es excepcionalmente largo,
                    * por lo que no necesitamos reducir tanto la fuente.
                    */
                    if ([
                        'Estudiante',
                        'Nombre1',
                        'Nombre2',
                    ].includes(nombreCampo)) {
                        if (valor.length > 36) {
                            tamanoFuente = 9;
                        } else if (valor.length > 28) {
                            tamanoFuente = 10;
                        }
                    }

                    /*
                    * Correos.
                    *
                    * Se amplía moderadamente el campo, sin invadir
                    * la columna siguiente.
                    *
                    * Si aun así el correo es largo, se reduce
                    * progresivamente el tamaño de fuente.
                    */
                    if ([
                        'Correo',
                        'Correo_2',
                        'Correo_3',
                    ].includes(nombreCampo)) {
                        const widgets = campo.acroField.getWidgets();

                        for (const widget of widgets) {
                            const rectangulo = widget.getRectangle();

                            widget.setRectangle({
                                x: rectangulo.x,
                                y: rectangulo.y,
                                width: rectangulo.width + 35,
                                height: rectangulo.height,
                            });
                        }

                        if (valor.length > 42) {
                            tamanoFuente = 7;
                        } else if (valor.length > 34) {
                            tamanoFuente = 8;
                        } else if (valor.length > 26) {
                            tamanoFuente = 9;
                        } else {
                            tamanoFuente = 10;
                        }
                    }
                    /*
                    * Direcciones del contrato.
                    *
                    * Este formato dispone de poco espacio horizontal,
                    * especialmente en los bloques de madre y codeudor.
                    *
                    * Se amplían únicamente unos puntos y las direcciones
                    * largas reducen progresivamente su fuente.
                    */
                    if ([
                        'Dirección',
                        'Dirección_2',
                        'Dirección_3',
                    ].includes(nombreCampo)) {
                        let ampliacion = 0;

                        if (nombreCampo === 'Dirección') {
                            // Padre / acudiente principal:
                            // dispone de algo más de espacio.
                            ampliacion = 45;
                        }

                        if (nombreCampo === 'Dirección_2') {
                            // Madre:
                            // no invadir la columna del codeudor.
                            ampliacion = 75;
                        }

                        if (nombreCampo === 'Dirección_3') {
                            // Codeudor:
                            // puede crecer ligeramente hacia la derecha.
                            ampliacion = 58;
                        }

                        const widgets = campo.acroField.getWidgets();

                        for (const widget of widgets) {
                            const rectangulo = widget.getRectangle();

                            widget.setRectangle({
                                x: rectangulo.x,
                                y: rectangulo.y,
                                width: rectangulo.width + ampliacion,
                                height: rectangulo.height,
                            });
                        }

                        if (valor.length > 40) {
                            tamanoFuente = 7;
                        } else if (valor.length > 32) {
                            tamanoFuente = 8;
                        } else if (valor.length > 24) {
                            tamanoFuente = 9;
                        } else {
                            tamanoFuente = 10;
                        }
                    }
                    campo.setFontSize(tamanoFuente);
                } catch (campoError) {
                    camposNoEncontrados.push(nombreCampo);

                    console.warn(
                        `Campo no encontrado: ${nombreCampo}`,
                        campoError
                    );
                }
            }

            /*
            * Ajustes visuales específicos del contrato.
            * Un valor negativo en Y desplaza el campo hacia abajo.
            */

            // Correo de la madre
            desplazarCampo('Correo_2', 0, -3.5);

            // Fecha de firma
            desplazarCampo('Dias', 0, -2);
            desplazarCampo('Mes', 0, -2);
            desplazarCampo('Año', 0, -3);

            /*
             * Actualiza la apariencia visible de los valores.
             * No se utiliza flatten(), por lo que los campos
             * continúan siendo editables.
             */
            formulario.updateFieldAppearances(fuente);

            const pdfFinal = await documentoPdf.save({
                useObjectStreams: false,
                addDefaultPage: false,
                updateFieldAppearances: false,
            });
            if (modoPaquete) {
                window.parent.postMessage(
                    {
                        tipo: 'documento-pdf-generado',
                        documento: 'contrato',
                        bytes: Array.from(pdfFinal),
                    },
                    window.location.origin
                );

                titulo.textContent = 'Contrato preparado';

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

            const urlPdf = URL.createObjectURL(archivo);

            titulo.textContent = 'Contrato preparado';
            mensaje.textContent =
                'Abriendo el documento editable…';

            /*
             * Se reemplaza esta pantalla por el visor PDF.
             * El documento conserva sus campos AcroForm.
             */
            window.location.replace(urlPdf);

        } catch (excepcion) {
            console.error(excepcion);

            spinner.style.display = 'none';
            titulo.textContent =
                'No fue posible preparar el contrato';

            mensaje.style.display = 'none';
            error.style.display = 'block';
            error.textContent =
                excepcion.message
                ?? 'Ocurrió un error inesperado.';
        }
    }

    document.addEventListener(
        'DOMContentLoaded',
        generarContrato
    );
</script>

</body>
</html>