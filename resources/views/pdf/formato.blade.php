<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1e293b; }

        .header {
            display: table;
            width: 100%;
            border: 2px solid #82211d;
            border-radius: 4px;
            margin-bottom: 18px;
        }
        .header-logo { display: table-cell; width: 80px; padding: 10px; vertical-align: middle; text-align: center; border-right: 1px solid #e2e8f0; }
        .header-logo img { width: 60px; }
        .header-info  { display: table-cell; padding: 10px 16px; vertical-align: middle; }
        .header-info .colegio { font-size: 13pt; font-weight: bold; color: #82211d; }
        .header-info .sub     { font-size: 8pt; color: #64748b; margin-top: 2px; }
        .header-code  { display: table-cell; width: 120px; padding: 10px; vertical-align: middle; text-align: right; font-size: 8pt; color: #64748b; }

        .doc-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            color: #82211d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #82211d;
            padding-bottom: 6px;
            margin-bottom: 16px;
        }

        .student-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 16px;
        }
        .student-box table { width: 100%; border-collapse: collapse; }
        .student-box td { padding: 3px 6px; font-size: 9pt; }
        .student-box .lbl { color: #64748b; width: 130px; font-weight: bold; }

        .content-area {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 14px;
            margin-bottom: 16px;
            min-height: 260px;
        }
        .content-area p { margin-bottom: 10px; line-height: 1.6; text-align: justify; font-size: 9.5pt; }
        .content-area .field-line {
            border-bottom: 1px solid #94a3b8;
            display: block;
            margin: 6px 0 14px 0;
            min-height: 18px;
        }
        .content-area .field-label { font-size: 8pt; color: #64748b; }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 30px;
        }
        .sig-cell { display: table-cell; width: 33%; text-align: center; padding: 0 10px; }
        .sig-line  { border-top: 1px solid #1e293b; margin: 0 10px; padding-top: 5px; font-size: 8pt; color: #475569; }

        .footer {
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 7.5pt;
            color: #94a3b8;
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>

{{-- Encabezado --}}
<div class="header">
    <div class="header-logo">
        @if($logoB64)
            <img src="{{ $logoB64 }}" alt="Logo">
        @endif
    </div>
    <div class="header-info">
        <div class="colegio">Colegio Rembrandt</div>
        <div class="sub">{{ $student->sede?->nombre ?? 'Sede' }} &nbsp;·&nbsp; Año lectivo {{ $student->periodoLectivo?->nombre }}</div>
    </div>
    <div class="header-code">
        Código: {{ $student->codigo }}<br>
        Fecha: {{ $fecha }}
    </div>
</div>

{{-- Título del documento --}}
<div class="doc-title">{{ $doc['label'] }}</div>

{{-- Datos del estudiante --}}
<div class="student-box">
    <table>
        <tr>
            <td class="lbl">Estudiante:</td>
            <td>{{ $student->primer_apellido }} {{ $student->segundo_apellido }}, {{ $student->primer_nombre }} {{ $student->segundo_nombre }}</td>
            <td class="lbl">Documento:</td>
            <td>{{ $student->tipo_documento }} {{ $student->documento }}</td>
        </tr>
        <tr>
            <td class="lbl">Curso:</td>
            <td>{{ $student->course?->grado }}° — {{ $student->course?->curso }}</td>
            <td class="lbl">Sede:</td>
            <td>{{ $student->sede?->nombre }}</td>
        </tr>
    </table>
</div>

{{-- Contenido del formato --}}
<div class="content-area">
    @if($tipo === 'comportamiento')
        <p>El Colegio Rembrandt, en cumplimiento de su Manual de Convivencia, establece el presente formato de seguimiento comportamental del estudiante. Este documento registra los compromisos adquiridos por el acudiente y el estudiante en materia de convivencia escolar.</p>
        <p>El acudiente declara conocer y aceptar el Manual de Convivencia del Colegio Rembrandt, y se compromete a apoyar desde el hogar el proceso formativo integral del estudiante.</p>
        <span class="field-label">Observaciones:</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-label">Compromisos adquiridos:</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-line">&nbsp;</span>

    @elseif($tipo === 'pagare')
        <p>Yo, ________________________________, identificado(a) con documento No. _________________, actuando en calidad de acudiente del estudiante <strong>{{ $student->primer_nombre }} {{ $student->primer_apellido }}</strong>, me comprometo a cancelar a favor del Colegio Rembrandt los valores correspondientes al año lectivo {{ $student->periodoLectivo?->nombre }}, según el contrato de servicios educativos suscrito.</p>
        <p>El presente pagaré (GA-F09) es firmado de manera libre y voluntaria como garantía de las obligaciones adquiridas.</p>
        <span class="field-label">Valor total del año lectivo:</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-label">Forma de pago acordada:</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-label">Lugar y fecha:</span>
        <span class="field-line">&nbsp;</span>

    @elseif($tipo === 'autorizacion_consulta')
        <p>El suscrito acudiente autoriza al Colegio Rembrandt para consultar y reportar ante las centrales de riesgo y demás entidades competentes, la información relacionada con el cumplimiento de las obligaciones económicas adquiridas por concepto de servicios educativos (GA-F10).</p>
        <p>Esta autorización se otorga de conformidad con la Ley 1266 de 2008 y demás normas concordantes sobre protección de datos.</p>
        <span class="field-label">Nombre del acudiente:</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-label">Documento de identidad:</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-label">Teléfono de contacto:</span>
        <span class="field-line">&nbsp;</span>

    @elseif($tipo === 'autorizacion_pagare')
        <p>Mediante el presente documento (GA-F13), el acudiente autoriza expresamente al Colegio Rembrandt para hacer uso del pagaré suscrito como mecanismo de cobro en caso de incumplimiento de las obligaciones económicas derivadas de la prestación de servicios educativos durante el año lectivo {{ $student->periodoLectivo?->nombre }}.</p>
        <span class="field-label">Nombre del acudiente:</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-label">No. del pagaré:</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-label">Valor autorizado:</span>
        <span class="field-line">&nbsp;</span>

    @elseif($tipo === 'autorizacion_datos')
        <p>En cumplimiento de la Ley Estatutaria 1581 de 2012 y el Decreto 1377 de 2013, el Colegio Rembrandt solicita su autorización para el tratamiento de los datos personales del estudiante y su familia, con fines académicos, administrativos y de comunicación institucional (GA-F14).</p>
        <p>Los datos recolectados serán utilizados exclusivamente para los fines propios del servicio educativo y no serán cedidos a terceros sin su consentimiento previo.</p>
        <span class="field-label">Nombre del acudiente:</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-label">Documento de identidad:</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-label">Correo electrónico:</span>
        <span class="field-line">&nbsp;</span>

    @elseif($tipo === 'contrato_servicios')
        <p>Entre el Colegio Rembrandt, representado por su Rector(a), y el acudiente del estudiante <strong>{{ $student->primer_nombre }} {{ $student->primer_apellido }}</strong>, se celebra el presente Contrato de Prestación de Servicios Educativos para el año lectivo {{ $student->periodoLectivo?->nombre }}.</p>
        <p><strong>OBLIGACIONES DEL COLEGIO:</strong> Prestar el servicio educativo en las condiciones acordadas, garantizando la calidad académica y el bienestar del estudiante.</p>
        <p><strong>OBLIGACIONES DEL ACUDIENTE:</strong> Cancelar oportunamente las obligaciones económicas, apoyar el proceso formativo del estudiante y cumplir el Manual de Convivencia.</p>
        <span class="field-label">Valor de la matrícula:</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-label">Valor mensualidad:</span>
        <span class="field-line">&nbsp;</span>
    @endif
</div>

{{-- Firmas --}}
<div class="signatures">
    <div class="sig-cell">
        <div class="sig-line">
            Firma del acudiente<br>
            <span style="font-size:7.5pt;">C.C. ___________________</span>
        </div>
    </div>
    <div class="sig-cell">
        <div class="sig-line">
            Firma del estudiante<br>
            <span style="font-size:7.5pt;">Doc. ___________________</span>
        </div>
    </div>
    <div class="sig-cell">
        <div class="sig-line">
            Rector(a) / Secretaría<br>
            <span style="font-size:7.5pt;">Colegio Rembrandt</span>
        </div>
    </div>
</div>

{{-- Pie de página --}}
<div class="footer">
    <div class="footer-left">Colegio Rembrandt &nbsp;·&nbsp; {{ $student->sede?->nombre }} &nbsp;·&nbsp; Año {{ $student->periodoLectivo?->nombre }}</div>
    <div class="footer-right">Generado: {{ $fecha }} &nbsp;·&nbsp; {{ $revisor }}</div>
</div>

</body>
</html>
