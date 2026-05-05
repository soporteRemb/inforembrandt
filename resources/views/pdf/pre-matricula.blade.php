<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; }

    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    .header-logo { width: 80px; vertical-align: middle; text-align: center; }
    .header-logo img { width: 75px; }
    .header-text { text-align: center; vertical-align: middle; font-style: italic; }
    .header-text h1 { font-size: 16pt; font-weight: bold; margin-bottom: 2px; }
    .header-text p { font-size: 8pt; margin: 1px 0; }

    .titulo { text-align: center; font-size: 18pt; font-weight: bold; font-style: italic; margin: 8px 0 6px; }

    .meta-row { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    .meta-row td { font-size: 10pt; font-weight: bold; font-style: italic; padding: 1px 0; }

    hr.line { border: none; border-top: 1px solid #000; margin: 4px 0; }
    hr.bold { border: none; border-top: 2px solid #000; margin: 4px 0; }

    .section-title { font-size: 10pt; font-weight: bold; font-style: italic; margin: 6px 0 3px; }

    .data-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    .data-table td { font-size: 10pt; font-style: italic; padding: 2px 4px 6px 0; vertical-align: top; }
    .data-table td.label { font-weight: bold; white-space: nowrap; }
    .data-table td.value { border-bottom: 1px solid #000; min-width: 80px; }

    .history-table { width: 100%; border-collapse: collapse; margin: 4px 0; }
    .history-table th { font-size: 10pt; font-weight: bold; font-style: italic; text-align: left; padding: 2px 0; border-bottom: 1px solid #000; }
    .history-table td { font-size: 10pt; font-style: italic; padding: 3px 0; }

    .guardian-table { width: 100%; border-collapse: collapse; margin: 4px 0; }
    .guardian-table th { font-size: 9pt; font-weight: bold; font-style: italic; text-align: left; padding: 2px 4px; border-bottom: 1px solid #000; }
    .guardian-table td { font-size: 9pt; font-style: italic; padding: 3px 4px; vertical-align: bottom; }
    .guardian-table td.firma { border-bottom: 1px solid #000; min-width: 100px; }

    .revisor { font-size: 9pt; font-style: italic; margin-top: 6px; }
    .footer { text-align: center; font-size: 8pt; font-style: italic; margin-top: 10px; }

    .full-line { border-bottom: 1px solid #000; display: block; min-height: 14px; }
</style>
</head>
<body>

{{-- ENCABEZADO --}}
<table class="header-table">
    <tr>
        <td class="header-logo">
            @if($logoB64)
                <img src="{{ $logoB64 }}">
            @endif
        </td>
        <td class="header-text">
            <h1>COLEGIO REMBRANDT</h1>
            <p><em>LA EXCELENCIA EDUCATIVA UN COMPROMISO DE TODOS</em></p>
            <p><em>Fundado en 1991</em></p>
            <p><em>DANE 311001087148 * NIT. 830.034.572-9</em></p>
            <p><em>LICENCIA DE FUNCIONAMIENTO</em></p>
            <p><em>Preescolar-Primaria 2254 del 30 de Junio de 2000 * Bachillerato Énfasis Comercial 7443 del 13 DE Novimebre de 1998</em></p>
            <p><em>Modificadas por la Resolución 10-813 del 30 de Novimebre de 2015</em></p>
            <p><em>Servicio de Educación de jóvenes y adultos 2235 del 31 de Julio de 2002</em></p>
            <p><em>Modificado por la Resolución 10-154 del 11 de Octubre de 2013</em></p>
        </td>
    </tr>
</table>

<div class="titulo">HOJA DE PRE-MATRICULA</div>

{{-- Fecha e ID --}}
<table class="meta-row">
    <tr>
        <td><em>FECHA PRE-MATRICULA: {{ \Carbon\Carbon::parse($fecha)->translatedFormat('d/M/Y') }}</em></td>
        <td style="text-align:right"><em>ID_ESTUDIANTE: {{ $student->codigo }}</em></td>
    </tr>
</table>
<hr class="bold">

{{-- DATOS PERSONALES --}}
<div class="section-title">DATOS PERSONALES</div>
<hr class="bold">

<table class="data-table">
    <tr>
        <td class="label" style="width:130px">TIPO_DOCUMENTO:</td>
        <td class="value" style="width:160px">{{ $student->tipo_documento }}</td>
        <td class="label" style="width:140px">No. IDENTIFICACION:</td>
        <td class="value" style="width:160px">{{ $student->documento }}</td>
        <td class="label" style="width:60px">ESTADO</td>
        <td class="value">{{ strtoupper($student->control == 'Estudiante nuevo' ? 'NUEVO' : ($student->control == 'Transferido' ? 'TRASLADO' : 'ANTIGUO')) }}</td>
    </tr>
</table>

<table class="data-table">
    <tr>
        <td class="label" style="width:160px">NOMBRES DEL ALUMNO:</td>
        <td class="value" style="width:220px">{{ $student->primer_nombre }} {{ $student->segundo_nombre }}</td>
        <td class="label" style="width:160px">APELLIDOS DEL ALUMNO:</td>
        <td class="value">{{ $student->primer_apellido }} {{ $student->segundo_apellido }}</td>
    </tr>
</table>

<table class="data-table">
    <tr>
        <td class="label" style="width:160px">LUGAR DE NACIMIENTO:</td>
        <td class="value" style="width:140px">{{ $student->ciudad_nacimiento ?? '*' }}</td>
        <td class="label" style="width:140px">FECHA_NACIMIENTO:</td>
        <td class="value" style="width:100px">{{ $student->fecha_nacimiento ? \Carbon\Carbon::parse($student->fecha_nacimiento)->format('d/m/Y') : '*' }}</td>
        <td class="label" style="width:40px">EDAD:</td>
        <td class="value" style="width:50px">{{ $student->edad }}</td>
        <td class="label" style="width:40px">SEXO:</td>
        <td class="value">{{ $student->sexo === 'Masculino' ? 'M' : 'F' }}</td>
    </tr>
</table>
<hr class="bold">

<table class="data-table">
    <tr>
        <td class="label" style="width:130px">GRADO A CURSAR</td>
        <td class="value" style="width:160px">{{ $student->course?->curso ?? '-' }}</td>
        <td class="label" style="width:160px">JORNADA A CURSAR:</td>
        <td class="value" style="width:130px">{{ strtoupper($student->course?->jornada ?? 'COMPLETA') }}</td>
        <td class="label" style="width:90px">CONDICION:</td>
        <td class="value">PRIVADO</td>
    </tr>
</table>
<hr class="bold">

{{-- HISTORIA ACADÉMICA --}}
<div class="section-title">HISTORIA ACADÉMICA</div>
<hr class="bold">
<table class="history-table">
    <tr>
        <th style="width:100px">GRADO</th>
        <th style="width:80px">AÑO</th>
        <th>INSTITUCION EDUCATIVA</th>
    </tr>
    @forelse($historial as $h)
    <tr>
        <td>{{ $h['grado'] }}</td>
        <td>{{ $h['anio'] }}</td>
        <td>{{ $h['institucion'] }}</td>
    </tr>
    @empty
    <tr><td colspan="3" style="padding:8px 0;">&nbsp;</td></tr>
    @endforelse
</table>
<hr class="bold">

{{-- DATOS DE PADRES Y ACUDIENTE --}}
@php
    $padre     = $student->guardians->where('tipo', 'padre')->first();
    $madre     = $student->guardians->where('tipo', 'madre')->first();
    $acudiente = $student->guardians->where('tipo', 'acudiente')->first();
@endphp

<table class="guardian-table">
    <tr>
        <th colspan="3">DATOS DE PADRES Y ACUDIENTE</th>
        <th style="text-align:right">Firmas</th>
    </tr>
    <tr>
        <th style="width:160px">NOMBRE DEL PADRE</th>
        <th style="width:160px">DIRECCIÓN</th>
        <th style="width:120px">TELÉFONO</th>
        <th></th>
    </tr>
    <tr>
        <td>{{ $padre?->nombre ?? '*' }}</td>
        <td>{{ $padre?->direccion ?? '*' }}</td>
        <td>{{ $padre?->telefono ?? '*' }}</td>
        <td class="firma"></td>
    </tr>
    <tr>
        <th>NOMBRE DEL MADRE</th>
        <th>DIRECCIÓN</th>
        <th>TELÉFONO</th>
        <th></th>
    </tr>
    <tr>
        <td>{{ $madre?->nombre ?? '*' }}</td>
        <td>{{ $madre?->direccion ?? '*' }}</td>
        <td>{{ $madre?->telefono ?? '*' }}</td>
        <td class="firma"></td>
    </tr>
    <tr>
        <th>NOMBRE DEL ACUDIENTE</th>
        <th>DIRECCIÓN</th>
        <th>TELÉFONO</th>
        <th></th>
    </tr>
    <tr>
        <td>{{ $acudiente?->nombre ?? '*' }}</td>
        <td>{{ $acudiente?->direccion ?? '*' }}</td>
        <td>{{ $acudiente?->telefono ?? '*' }}</td>
        <td class="firma"></td>
    </tr>
</table>
<hr class="bold">

<p class="revisor">(Revisó documentación {{ $revisor }})</p>

<div class="footer">
    <p><em>Carrera 123 No.63L-43 Teléfonos: 313 286 00 15 - 323 224 57 31 Engativá - Bogotá</em></p>
    <p><em>Email: infocolegiorembrandt@gmail.com - www.colegiorembrandt.edu.co</em></p>
</div>

</body>
</html>
