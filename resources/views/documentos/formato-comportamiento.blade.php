<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Formato comportamiento —
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
        Preparando formato de comportamiento
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
    const datosFormato = {
        plantillaUrl: @json($plantillaUrl),
        nombreArchivo: @json($nombreArchivo),
        campos: @json($campos),
    };
    const modoPaquete = new URLSearchParams(
        window.location.search
    ).get('modo') === 'paquete';

    async function generarFormato() {
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
                datosFormato.plantillaUrl,
                {
                    credentials: 'same-origin',
                    cache: 'no-store',
                }
            );

            if (!respuesta.ok) {
                throw new Error(
                    'No se encontró la plantilla del formato.'
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
                of Object.entries(datosFormato.campos)
            ) {
                try {
                    const campo = formulario.getTextField(
                        nombreCampo
                    );

                    const valor = String(valorCampo ?? '');

                    campo.setText(valor);

                    /*
                    * Tamaño base: 10 pt.
                    * En campos de nombres largos reducimos progresivamente
                    * para evitar que el texto se salga del espacio.
                    */
                    let tamanoFuente = 10;

                    if ([
                        'Nombre',
                        'Estudiante',
                    ].includes(nombreCampo)) {
                        if (valor.length > 32) {
                            tamanoFuente = 8;
                        } else if (valor.length > 26) {
                            tamanoFuente = 9;
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
                        documento: 'comportamiento',
                        bytes: Array.from(pdfFinal),
                    },
                    window.location.origin
                );

                titulo.textContent = 'Formato preparado';

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

            titulo.textContent = 'Formato preparado';

            mensaje.textContent =
                'Abriendo el documento editable…';

            window.location.replace(urlPdf);

        } catch (excepcion) {
            console.error(excepcion);

            spinner.style.display = 'none';

            titulo.textContent =
                'No fue posible preparar el formato';

            mensaje.style.display = 'none';

            error.style.display = 'block';

            error.textContent =
                excepcion.message
                ?? 'Ocurrió un error inesperado.';
        }
    }

    document.addEventListener(
        'DOMContentLoaded',
        generarFormato
    );
</script>

</body>
</html>