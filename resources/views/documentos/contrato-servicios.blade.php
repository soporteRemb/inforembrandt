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

                    campo.setText(
                        String(valorCampo ?? '')
                    );

                    /*
                    * Tamaño de letra de los campos del formulario.
                    * El PDF original utiliza una letra pequeña; la aumentamos
                    * aproximadamente un punto para mejorar la lectura.
                    */
                    campo.setFontSize(11);
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