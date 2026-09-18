<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Documentos — {{ $student->primer_nombre }} {{ $student->primer_apellido }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8fafc; color: #1e293b; }

        .topbar {
            background: #82211d;
            color: white;
            padding: 14px 32px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .topbar .logo { font-size: 1.3rem; font-weight: 800; letter-spacing: -0.5px; }
        .topbar .sub  { font-size: 0.85rem; opacity: 0.85; }

        .container { max-width: 860px; margin: 32px auto; padding: 0 16px; }

        .student-card {
            background: white;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .student-avatar {
            width: 60px; height: 60px;
            background: #fecaca;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 700; color: #c1121f;
            flex-shrink: 0;
            overflow: hidden;
        }
        .student-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .student-info h2 { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
        .student-info p  { font-size: 0.85rem; color: #64748b; margin-top: 2px; }
        .student-badge   { margin-left: auto; background: #f1f5f9; padding: 4px 12px; border-radius: 99px; font-size: 0.8rem; color: #475569; font-weight: 600; }

        .section-title {
            font-size: 0.78rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 10px;
        }

        .doc-list { background: white; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); overflow: hidden; }

        .doc-header {
            background: #fecaca;
            padding: 12px 20px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid #fca5a5;
        }
        .doc-header span { font-weight: 700; color: #991b1b; font-size: 0.92rem; }
        .badge-pend { background: #fca5a5; color: #7f1d1d; padding: 2px 10px; border-radius: 99px; font-size: 0.78rem; font-weight: 700; }

        .doc-row {
            display: flex;
            align-items: center;
            padding: 11px 20px;
            border-bottom: 1px solid #f1f5f9;
            gap: 12px;
        }
        .doc-row:last-child { border-bottom: none; }
        .doc-row:hover { background: #fafafa; }

        /* Botón PDF */
        .doc-btn {
            border: none; border-radius: 7px;
            padding: 6px 14px;
            font-weight: 600; font-size: 0.82rem;
            cursor: pointer;
            white-space: nowrap;
            display: inline-flex; align-items: center; gap: 5px;
            transition: opacity 0.15s;
            text-decoration: none;
        }
        .doc-btn:hover { opacity: 0.82; }

        /* Acciones de firma */
        .package-actions {
            margin-top: 14px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .package-btn {
            min-height: 46px;
            border: 1px solid #a52a24;
            border-radius: 9px;
            background: #a52a24;
            color: white;
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;

            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            cursor: pointer;
            transition:
                background 0.15s,
                opacity 0.15s,
                transform 0.15s;
        }

        .package-btn:hover {
            background: #92231f;
        }

        .package-btn:active {
            transform: translateY(1px);
        }

        .package-btn.disabled {
            background: #e2e8f0;
            border-color: #cbd5e1;
            color: #94a3b8;
            cursor: not-allowed;
        }

        .package-btn.disabled:hover {
            background: #e2e8f0;
        }

        /* Toggle checkbox */
        .toggle-wrap {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .toggle-btn {
            width: 34px; height: 20px;
            border-radius: 99px;
            border: none;
            cursor: pointer;
            position: relative;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        .toggle-btn::after {
            content: '';
            position: absolute;
            top: 3px; left: 3px;
            width: 14px; height: 14px;
            border-radius: 50%;
            background: white;
            transition: transform 0.2s;
        }
        .toggle-btn.off { background: #cbd5e1; }
        .toggle-btn.on  { background: #16a34a; }
        .toggle-btn.on::after { transform: translateX(14px); }

        .doc-meta { font-size: 0.75rem; text-align: right; }
        .doc-meta .done { color: #16a34a; font-weight: 600; display: block; }
        .doc-meta .who  { color: #94a3b8; display: block; }
        .doc-meta .pend { color: #94a3b8; }

        .spinner { display: inline-block; width: 12px; height: 12px; border: 2px solid currentColor; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Modal de envío para firma electrónica */
        .signature-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.48);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 9998;
        }

        .signature-modal-backdrop.open {
            display: flex;
        }

        .signature-modal {
            width: 100%;
            max-width: 560px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
            overflow: hidden;
        }

        .signature-modal-header {
            padding: 18px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .signature-modal-header h3 {
            font-size: 1rem;
            color: #1e293b;
        }

        .signature-modal-close {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 7px;
            background: #f1f5f9;
            color: #64748b;
            cursor: pointer;
            font-size: 1.1rem;
        }

        .signature-modal-body {
            padding: 20px;
        }

        .signature-help {
            padding: 10px 12px;
            margin-bottom: 18px;
            border-radius: 8px;
            background: #f8fafc;
            color: #64748b;
            font-size: 0.78rem;
            line-height: 1.45;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .signature-field {
            margin-bottom: 14px;
        }

        .signature-field.full {
            grid-column: 1 / -1;
        }

        .signature-field label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.76rem;
            font-weight: 700;
            color: #475569;
        }

        .signature-field input {
            width: 100%;
            min-height: 40px;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: white;
            color: #1e293b;
            font-size: 0.84rem;
            outline: none;
        }

        /* Texto de ejemplo: más claro que un dato real */
        #firmaCorreo::placeholder,
        #firmaTelefono::placeholder {
            color: #c4cad3;
            opacity: 1;
        }

        .signature-field input:focus {
            border-color: #a52a24;
            box-shadow: 0 0 0 2px rgba(165, 42, 36, 0.08);
        }
        .signature-field input[readonly] {
            background: #f8fafc;
            color: #64748b;
        }

        .signature-file {
            padding: 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            background: #fafafa;
        }

        .signature-file input {
            border: none;
            padding: 0;
            min-height: auto;
            background: transparent;
        }

        .signature-modal-footer {
            padding: 14px 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .signature-cancel {
            min-height: 40px;
            padding: 0 16px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: white;
            color: #475569;
            font-weight: 700;
            cursor: pointer;
        }

        .signature-submit {
            min-height: 40px;
            padding: 0 18px;
            border: 1px solid #a52a24;
            border-radius: 8px;
            background: #a52a24;
            color: white;
            font-weight: 700;
            cursor: pointer;
        }

        .signature-submit:hover {
            background: #92231f;
        }

        @media (max-width: 600px) {
            .signature-grid {
                grid-template-columns: 1fr;
            }
        }
        .toast {
            position: fixed; bottom: 24px; right: 24px;
            background: #1e293b; color: white;
            padding: 10px 18px; border-radius: 8px;
            font-size: 0.85rem; font-weight: 500;
            opacity: 0; transition: opacity 0.3s;
            pointer-events: none;
            z-index: 9999;
        }
        .toast.show { opacity: 1; }
    </style>
</head>
<body>

<div class="topbar">
    <div>
        <div class="logo">Colegio Rembrandt</div>
        <div class="sub">Gestión de documentos de matrícula</div>
    </div>
    <div style="margin-left:auto; font-size:0.82rem; opacity:0.85;">
        Usuario: {{ auth()->user()->name }}
    </div>
</div>

<div class="container">

    {{-- Tarjeta del estudiante --}}
    <div class="student-card">
        <div class="student-avatar">
            @if($student->foto)
                <img src="{{ asset('storage/' . $student->foto) }}" alt="foto">
            @else
                {{ strtoupper(substr($student->primer_nombre, 0, 1)) }}{{ strtoupper(substr($student->primer_apellido, 0, 1)) }}
            @endif
        </div>
        <div class="student-info">
            <h2>{{ $student->primer_nombre }} {{ $student->segundo_nombre }} {{ $student->primer_apellido }} {{ $student->segundo_apellido }}</h2>
            <p>
                {{ $student->tipo_documento }} {{ $student->documento }}
                &nbsp;·&nbsp; {{ $student->course?->grado }}° — {{ $student->course?->curso }}
                &nbsp;·&nbsp; {{ $student->periodoLectivo?->nombre }}
            </p>
        </div>
        <span class="student-badge">Código: {{ $student->codigo }}</span>
    </div>

    {{-- Lista de documentos --}}
    <div class="section-title">Documentos del proceso de matrícula</div>

    <div class="doc-list">
        <div class="doc-header">
            <span style="display:flex;align-items:center;gap:7px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="17" height="17"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                Documentos pendientes
            </span>
            <span class="badge-pend" id="badge-count"></span>
        </div>

        <div id="doc-rows">
            @php
                $todos      = \App\Models\StudentDocumento::todos();
                $generados  = $student->documentos()->get()->keyBy('tipo');
            @endphp

            @foreach($todos as $tipo => $doc)
            @php
                $registro = $generados[$tipo] ?? null;

                /*
                * Si existe el documento final de matrícula firmado,
                * los seis documentos que lo componen se consideran
                * entregados.
                */
                $entregadoPorFirma =
                    $documentoFirma !== null;

                $entregado =
                    $entregadoPorFirma
                    || $registro !== null;
            @endphp

            <div
                class="doc-row"
                id="row-{{ $tipo }}"
                data-generado="{{ $entregado ? 'true' : 'false' }}"
            >

                {{-- Botón que abre el PDF --}}
                <a
                    href="{{ in_array($tipo, [
                            'contrato_servicios',
                            'comportamiento',
                            'pagare',
                            'autorizacion_consulta',
                            'autorizacion_pagare',
                            'autorizacion_datos',
                        ])
                        ? route('students.pdf.formato', [
                            'student' => $student,
                            'tipo' => $tipo,
                        ])
                        : asset('formatos/' . rawurlencode($doc['archivo']))
                    }}"
                    target="_blank"
                    class="doc-btn"
                    style="
                        background: {{ $doc['color'] }};
                        color: {{ $doc['text'] }};
                        border: 1px solid {{ $doc['border'] }};
                    "
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        width="13"
                        height="13"
                        style="flex-shrink:0"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"
                        />
                    </svg>

                    {{ $doc['label'] }}
                </a>

                {{-- Toggle + meta --}}
                <div class="toggle-wrap">
                    <div class="doc-meta" id="meta-{{ $tipo }}">
                        @if($entregadoPorFirma)

                            <span class="done">
                                ✓ Entregado
                            </span>

                            <span class="who">
                                Documento de matrícula firmado
                            </span>

                        @elseif($registro)

                            <span class="done">
                                ✓ Entregado
                            </span>

                            <span class="who">
                                {{ \Carbon\Carbon::parse(
                                    $registro->generado_at
                                )->format('d/m/Y H:i') }}
                                ·
                                {{ $registro->generado_por }}
                            </span>

                        @else

                            <span class="pend">
                                Pendiente
                            </span>

                        @endif
                    </div>
                    <button
                        class="toggle-btn {{ $entregado ? 'on' : 'off' }}"
                        id="toggle-{{ $tipo }}"
                        @if(!$entregadoPorFirma)
                            onclick="toggleDoc('{{ $tipo }}')"
                        @endif
                        @if($entregadoPorFirma)
                            disabled
                            title="Entregado mediante documento de matrícula firmado"
                        @else
                            title="{{ $registro
                                ? 'Desmarcar como entregado'
                                : 'Marcar como entregado'
                            }}"
                        @endif
                    >
                    </button>
                </div>

            </div>
            @endforeach
                </div>
    </div>

    {{-- Acciones de firma de documentos --}}
    <div class="package-actions">

        <a
            href="{{ route('students.pdf.paquete', [
                'student' => $student,
            ]) }}"
            target="_blank"
            class="package-btn"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                width="18"
                height="18"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"
                />
            </svg>

            Unificar documentos para firma
        </a>

        @php
            $firmaActiva = $firmaElectronica
                && in_array(
                    $firmaElectronica->estado,
                    [
                        'pendiente',
                        'enviando',
                        'en_proceso',
                        'envio_incierto',
                        'firmada_pendiente_documento',
                    ],
                    true
                );

            $bloquearEnvioFirma =
                $documentoFirma !== null
                || $firmaActiva;
        @endphp

        <button
            type="button"
            class="package-btn"
            @if(!$bloquearEnvioFirma)
                onclick="abrirModalFirma()"
            @endif
            @disabled($bloquearEnvioFirma)
            title="{{
                $documentoFirma
                    ? 'La matrícula electrónica ya fue incorporada'
                    : (
                        $firmaActiva
                            ? 'Ya existe un proceso de firma electrónica activo'
                            : 'Enviar documentos para firma electrónica'
                    )
            }}"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                width="18"
                height="18"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"
                />
            </svg>

            @if($documentoFirma)
                Firma completada
            @elseif($firmaActiva)
                Firma en proceso
            @else
                Enviar para firmar
            @endif
        </button>

    </div>

    @if($documentoFirma)

        {{-- TARJETA VERDE ACTUAL --}}
        <div
            style="
                margin-top: 14px;
                padding: 12px 16px;
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
                border-radius: 9px;
                display: flex;
                align-items: center;
                gap: 12px;
            "
        >
            <div style="flex:1; min-width:0;">
                <div
                    style="
                        font-size: 0.82rem;
                        font-weight: 700;
                        color: #166534;
                    "
                >
                    ✓ Documento de matrícula firmado
                </div>

                <div
                    style="
                        margin-top: 2px;
                        font-size: 0.75rem;
                        color: #64748b;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    "
                    title="{{ $documentoFirma->nombre_original }}"
                >
                    {{ $documentoFirma->nombre_original }}
                </div>
            </div>

            <a
                href="{{ route(
                    'estudiantes.documentos.archivos.ver',
                    $documentoFirma
                ) }}"
                target="_blank"
                class="doc-btn"
                style="
                    background: white;
                    color: #166534;
                    border: 1px solid #86efac;
                "
            >
                Ver PDF
            </a>
        </div>

    @elseif(
        $firmaElectronica
        && in_array(
            $firmaElectronica->estado,
            [
                'pendiente',
                'enviando',
                'en_proceso',
                'firmada_pendiente_documento',
            ],
            true
        )
    )

        <div
            style="
                margin-top: 14px;
                padding: 12px 16px;
                background: #fffbeb;
                border: 1px solid #fde68a;
                border-radius: 9px;
                display: flex;
                align-items: center;
                gap: 12px;
            "
        >
            <div
                style="
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    background: #f59e0b;
                    flex: 0 0 auto;
                "
            ></div>

            <div style="flex:1; min-width:0;">
                <div
                    style="
                        font-size: 0.82rem;
                        font-weight: 700;
                        color: #92400e;
                    "
                >
                    En proceso de firma
                </div>

                <div
                    style="
                        margin-top: 2px;
                        font-size: 0.75rem;
                        color: #78716c;
                    "
                >
                    @if(
                        $firmaElectronica
                        && $firmaElectronica->estado === 'firmada_pendiente_documento'
                    )
                        La firma electrónica fue completada. El documento firmado
                        está pendiente de ser incorporado al expediente del estudiante.
                    @else
                        El documento fue enviado y está pendiente de completar
                        las firmas electrónicas.
                    @endif
                </div>
            </div>
        </div>

    @elseif(
        $firmaElectronica
        && $firmaElectronica->estado === 'envio_incierto'
    )

        <div
            style="
                margin-top: 14px;
                padding: 12px 16px;
                background: #fff7ed;
                border: 1px solid #fed7aa;
                border-radius: 9px;
                display: flex;
                align-items: center;
                gap: 12px;
            "
        >
            <div
                style="
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    background: #ea580c;
                    flex: 0 0 auto;
                "
            ></div>

            <div style="flex:1; min-width:0;">
                <div
                    style="
                        font-size: 0.82rem;
                        font-weight: 700;
                        color: #9a3412;
                    "
                >
                    Estado del envío por verificar
                </div>

                <div
                    style="
                        margin-top: 2px;
                        font-size: 0.75rem;
                        color: #78716c;
                    "
                >
                    No fue posible confirmar la respuesta de Click & Sign.
                    No realice un nuevo envío hasta verificar el estado
                    de esta solicitud.
                </div>
            </div>
        </div>

    @endif

</div>

<div class="toast" id="toast"></div>

<div
    class="signature-modal-backdrop"
    id="modalFirma"
    onclick="cerrarModalFirmaDesdeFondo(event)"
>
    <div
        class="signature-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="tituloModalFirma"
    >
        <div class="signature-modal-header">
            <h3 id="tituloModalFirma">
                Enviar documentos para firma
            </h3>

            <button
                type="button"
                class="signature-modal-close"
                onclick="cerrarModalFirma()"
                title="Cerrar"
            >
                ×
            </button>
        </div>

        <div class="signature-modal-body">

            <div class="signature-help">
                El acudiente recibirá el enlace de firma por
                correo electrónico y SMS. El proceso requerirá
                un código enviado al celular.
            </div>

            <div class="signature-grid">

                <div class="signature-field">
                    <label for="firmaNombre">
                        Acudiente
                    </label>

                    <input
                        type="text"
                        id="firmaNombre"
                        readonly
                        placeholder="Se cargará automáticamente"
                    >
                </div>

                <div class="signature-field">
                    <label for="firmaDocumento">
                        Documento
                    </label>

                    <input
                        type="text"
                        id="firmaDocumento"
                        readonly
                        placeholder="Se cargará automáticamente"
                    >
                </div>

                <div class="signature-field">
                    <label for="firmaCorreo">
                        Correo para la firma
                    </label>

                    <input
                        type="email"
                        id="firmaCorreo"
                        autocomplete="off"
                        placeholder="Ej. correo@ejemplo.com"
                    >
                </div>

                <div class="signature-field">
                    <label for="firmaTelefono">
                        Celular para SMS / OTP
                    </label>

                    <input
                        type="tel"
                        id="firmaTelefono"
                        autocomplete="off"
                        placeholder="Ej. 3001234567"
                    >
                </div>

                <div class="signature-field full">
                    <label for="firmaPdf">
                        Documento unificado
                    </label>

                    <div class="signature-file">
                        <input
                            type="file"
                            id="firmaPdf"
                            accept="application/pdf,.pdf"
                        >
                    </div>
                </div>

            </div>
        </div>

        <div class="signature-modal-footer">
            <button
                type="button"
                class="signature-cancel"
                onclick="cerrarModalFirma()"
            >
                Cancelar
            </button>

            <button
                type="button"
                class="signature-submit"
                onclick="prepararEnvioFirma()"
            >
                Enviar para firmar
            </button>
        </div>
    </div>
</div>

<script>
const studentId = {{ $student->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const userName  = '{{ auth()->user()->name }}';

async function abrirModalFirma() {
    const modal = document.getElementById('modalFirma');

    const nombre = document.getElementById('firmaNombre');
    const documento = document.getElementById('firmaDocumento');
    const correo = document.getElementById('firmaCorreo');
    const telefono = document.getElementById('firmaTelefono');

    /*
     * Limpiamos los datos de una apertura anterior.
     */
    nombre.value = '';
    documento.value = '';
    correo.value = '';
    telefono.value = '';

    nombre.placeholder = 'Cargando...';
    documento.placeholder = 'Cargando...';

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';

    try {
        const respuesta = await fetch(
            `/students/${studentId}/firma-electronica/acudiente`,
            {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            }
        );

        const datos = await respuesta.json();

        if (! respuesta.ok || ! datos.ok) {
            throw new Error(
                datos.mensaje
                || 'No fue posible cargar los datos del acudiente.'
            );
        }

        nombre.value = datos.datos.nombre ?? '';
        documento.value = datos.datos.documento ?? '';
        correo.value = datos.datos.correo ?? '';
        telefono.value = datos.datos.telefono ?? '';

        nombre.placeholder = '';
        documento.placeholder = '';

    } catch (error) {
        nombre.placeholder = 'No disponible';
        documento.placeholder = 'No disponible';

        showToast(
            error.message
            || 'No fue posible cargar los datos del acudiente.'
        );
    }
}

function cerrarModalFirma() {
    const modal = document.getElementById('modalFirma');

    modal.classList.remove('open');
    document.body.style.overflow = '';
}

function cerrarModalFirmaDesdeFondo(event) {
    if (event.target.id === 'modalFirma') {
        cerrarModalFirma();
    }
}

async function prepararEnvioFirma() {
    const correo = document.getElementById('firmaCorreo');
    const telefono = document.getElementById('firmaTelefono');
    const pdf = document.getElementById('firmaPdf');

    const boton = document.querySelector('.signature-submit');

    const archivo = pdf.files[0] ?? null;

    if (! correo.value.trim()) {
        showToast('Debe indicar el correo electrónico para la firma.');
        correo.focus();
        return;
    }

    if (! telefono.value.trim()) {
        showToast('Debe indicar el celular para SMS / OTP.');
        telefono.focus();
        return;
    }

    if (! /^3\d{9}$/.test(telefono.value.trim())) {
        showToast(
            'El celular debe tener 10 dígitos y comenzar por 3.'
        );
        telefono.focus();
        return;
    }

    if (! archivo) {
        showToast('Debe seleccionar el documento unificado.');
        pdf.focus();
        return;
    }

    if (
        archivo.type !== 'application/pdf'
        && ! archivo.name.toLowerCase().endsWith('.pdf')
    ) {
        showToast('El documento unificado debe ser un archivo PDF.');
        pdf.focus();
        return;
    }

    const datos = new FormData();

    datos.append(
        'correo',
        correo.value.trim()
    );

    datos.append(
        'telefono',
        telefono.value.trim()
    );

    datos.append(
        'pdf',
        archivo
    );

    const textoOriginal = boton.textContent;

    boton.disabled = true;
    boton.innerHTML =
        '<span class="spinner"></span> Preparando envío...';

    try {
        const respuesta = await fetch(
            `/students/${studentId}/firma-electronica/preparar`,
            {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: datos,
            }
        );

        let resultado = {};

        try {
            resultado = await respuesta.json();
        } catch (error) {
            throw new Error(
                'El servidor devolvió una respuesta no válida.'
            );
        }

        if (! respuesta.ok || ! resultado.ok) {
            throw new Error(
                resultado.mensaje
                || 'No fue posible preparar el envío para firma.'
            );
        }

        cerrarModalFirma();

        showToast(
            resultado.mensaje
            || 'Documentos enviados correctamente para firma.'
        );

    } catch (error) {
        showToast(
            error.message
            || 'No fue posible preparar el envío para firma.'
        );
    } finally {
        boton.disabled = false;
        boton.textContent = textoOriginal;
    }
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        cerrarModalFirma();
    }
});

function updateBadge() {
    const pendientes = document.querySelectorAll('.doc-row[data-generado="false"]').length;
    const badge = document.getElementById('badge-count');
    badge.textContent = pendientes + ' pendiente' + (pendientes !== 1 ? 's' : '');
    badge.style.display = pendientes === 0 ? 'none' : '';
}

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2800);
}

async function toggleDoc(tipo) {
    const toggleBtn = document.getElementById('toggle-' + tipo);
    const row       = document.getElementById('row-' + tipo);
    const meta      = document.getElementById('meta-' + tipo);

    toggleBtn.innerHTML = '<span class="spinner"></span>';
    toggleBtn.disabled  = true;

    try {
        const res  = await fetch(`/students/${studentId}/documentos/${tipo}/toggle`, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
        });
        const data = await res.json();

        if (data.estado === 'marcado') {
            row.dataset.generado  = 'true';
            toggleBtn.className   = 'toggle-btn on';
            const ahora = new Date().toLocaleString('es-CO', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
            meta.innerHTML = `<span class="done">✓ Entregado</span><span class="who">${ahora} · ${userName}</span>`;
            showToast('✓ Marcado como entregado');
        } else {
            row.dataset.generado  = 'false';
            toggleBtn.className   = 'toggle-btn off';
            meta.innerHTML        = '<span class="pend">Pendiente</span>';
            showToast('Desmarcado');
        }
    } catch(e) {
        showToast('Error al guardar');
    }

    toggleBtn.innerHTML = '';
    toggleBtn.disabled  = false;
    updateBadge();
}

document.addEventListener('DOMContentLoaded', updateBadge);
</script>
</body>
</html>
