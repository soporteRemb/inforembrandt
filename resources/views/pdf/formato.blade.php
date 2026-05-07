<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9.5pt; color: #000; }

/* ── Encabezado institucional ── */
.header-table { width:100%; border-collapse:collapse; border:1.5px solid #000; margin-bottom:14px; }
.header-table td { padding:6px 8px; vertical-align:middle; }
.header-logo { width:70px; text-align:center; border-right:1px solid #000; }
.header-logo img { width:55px; }
.header-title { text-align:center; font-weight:bold; font-size:10.5pt; line-height:1.4; }
.header-escudo { width:70px; text-align:center; border-left:1px solid #000; }
.header-codes { border-top:1px solid #000; }
.header-codes td { font-size:8pt; text-align:center; padding:3px 8px; }

/* ── Cuerpo ── */
.doc-title { text-align:center; font-weight:bold; font-size:11pt; margin-bottom:14px; text-decoration:underline; }
p { margin-bottom:8px; line-height:1.55; text-align:justify; }
.bold { font-weight:bold; }
ul { margin:6px 0 8px 18px; }
ul li { margin-bottom:4px; line-height:1.5; }

/* ── Líneas de firma / campo ── */
.field-line { border-bottom:1px solid #000; display:inline-block; min-width:200px; }
.field-line-full { border-bottom:1px solid #000; display:block; width:100%; margin:2px 0 10px 0; height:16px; }

/* ── Tabla de cuotas pagaré ── */
.pagare-table { width:100%; border-collapse:collapse; margin:10px 0; font-size:8.5pt; }
.pagare-table td { border:1px solid #555; padding:3px 5px; }
.pagare-table .lbl { background:#f5f5f5; width:90px; }

/* ── Sección firmas ── */
.firmas-grid { width:100%; margin-top:20px; }
.firmas-grid td { vertical-align:top; padding:0 12px; width:33%; }
.firma-block { margin-bottom:16px; }
.firma-block .linea { border-top:1px solid #000; margin-bottom:3px; }
.firma-block .label { font-weight:bold; font-size:8.5pt; }
.firma-block .sub   { font-size:8pt; }
.firma-data { font-size:8.5pt; margin-top:4px; }
.firma-data span { display:block; border-bottom:1px solid #000; min-height:14px; margin-bottom:3px; }

/* ── Salto de página ── */
.page-break { page-break-after:always; }

/* ── Nota ── */
.nota { font-weight:bold; font-size:8.5pt; margin-top:14px; border-top:1px solid #ccc; padding-top:6px; }
</style>
</head>
<body>

@php
    $padre     = $student->guardians->firstWhere('tipo', 'padre');
    $madre     = $student->guardians->firstWhere('tipo', 'madre');
    $acudiente = $padre ?? $student->guardians->first();

    $grado     = $student->course ? $student->course->grado . '°' : '___';
    $curso     = $student->course?->curso ?? '';
    $anio      = $student->periodoLectivo?->nombre ?? '2026';
    $nombreEst = trim($student->primer_nombre . ' ' . $student->segundo_nombre . ' ' . $student->primer_apellido . ' ' . $student->segundo_apellido);
    $docEst    = $student->tipo_documento . ' ' . $student->documento;

    $nomAcud  = $acudiente?->nombre   ?? '';
    $ccAcud   = $acudiente?->documento ?? '';
    $dirAcud  = $acudiente?->direccion ?? '';
    $telAcud  = $acudiente?->telefono  ?? '';
    $corrAcud = $acudiente?->correo    ?? '';

    $nomMadre = $madre?->nombre   ?? '';
    $ccMadre  = $madre?->documento ?? '';
@endphp

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- CONTRATO DE SERVICIO EDUCATIVO (GA.F08) --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@if($tipo === 'contrato_servicios')

<table class="header-table">
  <tr>
    <td class="header-logo">@if($logoB64)<img src="{{ $logoB64 }}">@endif<div style="font-size:7pt;font-weight:bold;">COLEGIO<br>REMBRANDT</div></td>
    <td class="header-title">CONTRATO DE SERVICIO EDUCATIVO</td>
    <td class="header-escudo">@if($logoB64)<img src="{{ $logoB64 }}">@endif</td>
  </tr>
  <tr class="header-codes">
    <td colspan="3">
      <table style="width:100%;border-collapse:collapse;"><tr>
        <td style="text-align:center;">GA.F08</td>
        <td style="text-align:center;">Versión: 6</td>
        <td style="text-align:center;">Fecha: 25/11/2025</td>
      </tr></table>
    </td>
  </tr>
</table>

<p class="doc-title">CONTRATO DE COOPERACIÓN PARA PRESTAR UN SERVICIO EDUCATIVO</p>

<p>En cumplimiento de la Constitución Nacional, la Ley 115 de 1.994, decreto 1860 de 1.994 (Decreto 1075 de 2015) y demás normas concordantes y con el propósito de asegurar el derecho a la educación del estudiante <span class="field-line">{{ $nombreEst }}</span>, quien en adelante se denomina BENEFICIARIO- ESTUDIANTE; los suscritos <span class="field-line">{{ $nomAcud }}</span> Y <span class="field-line">{{ $nomMadre }}</span>, identificados como aparece al pie de nuestras firmas, actuando en calidad de padres y/o acudientes del BENEFICIARIO ESTUDIANTE, respecto del servicio de educación contratada, por una parte, quienes para los efectos del presente pacto se denominan EL CONTRATANTE y de la otra la Sociedad <span class="bold">COLEGIO REMBRANDT - REMBRANDTEC LTDA</span>, representada por la señora <span class="bold">ESMERALDA VILLAMIZAR JAIMES</span>, con domicilio en Bogotá, mayor de edad, identificada con la cédula de ciudadanía número 52.192.437, establecimiento educativo de carácter privado, debidamente aprobado para los niveles de Educación Preescolar, Básica Primaria, Básica Secundaria, Media Académica con Énfasis en Comerciales y Servicio de Aprendizaje para jóvenes y Adultos SEDA- certificado bajo la Norma ISO:9001 2015, otorgando el titulo de Bachiller Académico, que en adelante y para los efectos del presente se llamará EL CONTRATISTA o COLEGIO, de mutuo acuerdo hemos resuelto celebrar el presente <span class="bold">CONTRATO DE COOPERACION EDUCATIVA O DE SERVICIO EDUCATIVO</span>, el cual se rige por las siguientes cláusulas o en su defecto por la ley Civil Colombiana.</p>

<p><span class="bold">PRIMERA: DEFINICIÓN DEL CONTRATO.-</span> Este es un contrato de cooperación educativa o de prestación de servicio educativo, obedece a las disposiciones Constitucionales en las cuales se establece una responsabilidad compartida de la educación, en donde concurren obligaciones de los educadores, los educandos, los padres y/o acudientes a hacer efectivas las prestaciones del servicio público educativo como función social, por parte de los Colegios Privados de manera que el incumplimiento de cualquiera de las obligaciones adquiridas por los contratantes hace imposible la consecución del fin común. Por tanto, las obligaciones que se adquieren en el presente contrato son correlativas y esenciales para el logro de los objetivos educacionales y por ende de los fines establecidos en el artículo 5º de la ley 115 de 1994 recogida en el Decreto 1075 de 2015.</p>

<p><span class="bold">SEGUNDA: OBJETO:</span> El objeto del presente contrato es el de conseguir la recíproca complementación de esfuerzos entre los estudiantes, padres y/o acudientes del BENEFICIARIO - ESTUDIANTE y el COLEGIO, para obtener una formación y rendimiento académico satisfactorio del programa curricular correspondiente al grado <span class="field-line">{{ $grado }}</span> a cursar durante el año lectivo {{ $anio }}, en orden de conseguir su educación y formación integral.</p>

<p><span class="bold">TERCERA: OBLIGACIONES ESENCIALES DE LAS PARTES.-</span> Por ser este contrato de cooperación educativa, tendiente al cumplimiento del fin común consistente en la educación del BENEFICIARIO – ESTUDIANTE, son obligaciones esenciales sin cuyo cumplimiento continuado se considera interrumpido, las siguientes: A) Por parte del BENEFICIARIO – ESTUDIANTE: Asistir puntualmente a las clases o actividades académicas programadas dentro de los horarios y actividades exigidas y cumplir con las normas que regulan su situación académica, disciplinaria y de comportamiento, dentro y fuera de la institución, en especial lo dispuesto en el Manual de Convivencia en acatamiento de la Ley 1620 de 2013 y su reglamentación. B) POR PARTE DEL COLEGIO: Impartir y administrar el proceso de enseñanza contratada por intermedio de los docentes al servicio del plantel. C) POR PARTE DE LOS PADRES Y/O ACUDIENTES: El pago oportuno del costo del servicio educativo–pensión mensual- debe ser hecho dentro de los primeros cinco (05) días de cada período mensual.</p>

<p><span class="bold">CUARTA: DURACIÓN.</span> Este contrato tiene vigencia de (1) año lectivo o académico, contado a partir del 01 de febrero hasta el 30 de noviembre del año <span class="bold">{{ $anio }}</span>, pero su ejecución será sucesiva por períodos mensuales.</p>

<p><span class="bold">QUINTA: PENSION.-</span> Por su parte, el padre de familia o acudiente o responsable del BENEFICIARIO – ESTUDIANTE, se compromete a cancelar una pensión anual pagadera en (10) cuotas mensuales, dentro de los (05) cinco primeros días de cada mes como retribución y colaboración pecuniaria del financiamiento del objeto propuesto en la cláusula segunda, dichas condiciones se encuentran estipuladas en el Manual de Convivencia, tarifas determinadas de acuerdo a lo establecido en la Resolución 019805 de 30 de septiembre de 2025 del Ministerio de Educación Nacional. <span class="bold">PARAGRAFO PRIMERO:</span> El padre de familia o acudiente que no cancele el monto de la pensión dentro de los 05 primeros días de cada mes se obliga reconocer y pagar una sanción por pagos extemporáneos de $20.000 para preescolar y básica primaria, $25.000 para básica secundaria y $30.000 para educación media. <span class="bold">PARAGRAFO SEGUNDO:</span> Si el Padre de Familia presenta mora de más de 90 días en el pago de la pensión, esta cartera será enviada a la casa de cobranza correspondiente.</p>

<p><span class="bold">SEXTA: OBLIGACIONES DEL CONTRATISTA O COLEGIO:</span> Constituyen obligaciones del Colegio inherentes al cumplimiento del presente pacto a favor del BENEFICIARIO – ESTUDIANTE y de los padres o acudientes, las de prestar en forma regular el servicio educativo contratado, bajo la figura de forma presencial.</p>

<p><span class="bold">SÉPTIMA: OBLIGACIONES DE LOS PADRES-CONTRATANTE.-</span> Los Padres se obligan desde el momento en que vinculan al BENEFICIARIO – ESTUDIANTE a: 1) Matricularlo en los días señalados por el Colegio. 2) Velar por el proceso armónico y coherente de la formación y educación integral del BENEFICIARIO – ESTUDIANTE. 3) Asistir a entrevistas cuando sean llamados por las directivas del Colegio o docentes. 4) Prestar la mayor colaboración posible a las directivas y profesores. 5) Dotar al BENEFICIARIO-ESTUDIANTE de los implementos, útiles y libros necesarios. 6) Velar por la permanente asistencia del BENEFICIARIO – ESTUDIANTE a las clases. 7) Cancelar las pensiones en los cinco (05) primeros días de cada período mensual.</p>

<p><span class="bold">OCTAVA: DERECHOS DE LOS PADRES Y/ACUDIENTES o CONTRATANTE.-</span> Constituyen derechos de los Padres o Acudientes los de exigir la regular prestación de los servicios educativos contratados y que este se ajuste a los programas oficiales y tengan el nivel y calidad académicos prescrito por la ley.</p>

<p><span class="bold">NOVENA: CERTIFICADOS Y MATRÍCULAS.-</span> El cumplimiento del presente contrato dará derecho a la obtención del certificado sobre la actividad académica del BENEFICIARIO – ESTUDIANTE y la renovación de la matrícula en el grado siguiente cuando el estudiante sea promovido.</p>

<p><span class="bold">DÉCIMA: MANUAL DE CONVIVENCIA.-</span> El Manual de Convivencia del Colegio, se considera incorporado al presente contrato y por tanto es aceptado en todas sus partes por los Padres y/o Acudientes y por el BENEFICIARIO – ESTUDIANTE.</p>

<p><span class="bold">DÉCIMA PRIMERA:</span> Los suscritos Padres y/o Acudientes autorizan al Colegio para que pueda reportar a una CENTRAL DE RIESGOS la morosidad cuando esta sea superior a sesenta (60) días.</p>

<p><span class="bold">DÉCIMA SEGUNDA: OTROS ASPECTOS DE INTERÉS:</span> 1- TRATAMIENTO DE DATOS PERSONALES. En cumplimiento de la Ley 1581 de 2012, EL CONTRATISTA informa que está comprometido en efectuar un correcto uso y tratamiento de los datos personales. 2- CIRCUITOS CERRADOS Y SISTEMAS DE CONTROL DE ACCESO. Los suscritos autorizan el uso de los datos recaudados mediante medios tecnológicos. 3- DERECHOS DE IMAGEN, AUTOR Y OTROS. Los padres y/o acudientes autorizan al Colegio para hacer uso de los derechos de imagen del estudiante para fines pedagógicos e institucionales.</p>

<p><span class="bold">DÉCIMA TERCERA:</span> Se acuerda que cuando el alumno se matricule y no pueda asistir desde un principio a clases, los padres de familia deben informarlo por medio escrito antes que se inicien las labores escolares y en este caso tienen derecho a que se le devuelva el 30% del valor de la matrícula.</p>

<p><span class="bold">DÉCIMA CUARTA:</span> El Colegio se reserva el derecho de contratar los servicios de alguna firma de COBRANZAS PREJURÍDICAS Y JURÍDICAS para hacer efectivo el pago de las obligaciones económicas, cuando la morosidad sea de NOVENTA (90) días o superior.</p>

<p><span class="bold">DÉCIMA QUINTA: COSTO PERIÓDICOS OBLIGATORIOS:</span> Los costos obligatorios a pagar para el período lectivo {{ $anio }}, de acuerdo a la resolución 1725 del 7 de octubre del 2024 son los derechos de grado para el grado once y la plataforma de comunicaciones que deben ser cancelados con la matrícula.</p>

<p><span class="bold">DÉCIMA SEXTA:</span> No se permite que los padres de familia realicen cobros o actividades que involucren el Colegio Rembrandt sin autorización.</p>

<p><span class="bold">DÉCIMA SEPTIMA: GUIAS Y PROPUESTA PEDAGÓGICA:</span> Las guías y propuesta pedagógica de preescolar hasta el grado once, son autoría del Colegio Rembrandt, hechos en material fungible y de un solo uso, los cuales deben ser cancelados en su totalidad en el momento de la matrícula.</p>

<p style="margin-top:14px;">En constancia se firma el presente contrato en todas sus partes en la ciudad de Bogotá, a los <span class="field-line" style="min-width:40px;">&nbsp;&nbsp;&nbsp;</span> días del mes de <span class="field-line" style="min-width:100px;">&nbsp;</span> de {{ $anio }}.</p>

<table style="width:100%;margin-top:24px;border-collapse:collapse;">
  <tr>
    <td style="width:50%;padding-right:20px;vertical-align:top;">
      <p class="bold">FIRMA PADRES Y/0 ACUDIENTE</p><br>
      <div class="firma-data">
        <div style="border-top:1px solid #000;margin-top:30px;margin-bottom:3px;"></div>
        <span class="bold">NOMBRE DEL PADRE Y/0 ACUDIENTE</span><br>
        <span>C.C. No. <span class="field-line" style="min-width:120px;">{{ $ccAcud }}</span></span><br>
        <span>Dirección: <span class="field-line" style="min-width:150px;">{{ $dirAcud }}</span></span><br>
        <span>Teléfono: <span class="field-line" style="min-width:120px;">{{ $telAcud }}</span></span><br>
        <span>Correo: <span class="field-line" style="min-width:150px;">{{ $corrAcud }}</span></span>
      </div>
      <br><br>
      <div class="firma-data">
        <div style="border-top:1px solid #000;margin-top:30px;margin-bottom:3px;"></div>
        <span class="bold">NOMBRE DE LA MADRE</span><br>
        <span>C.C. No. <span class="field-line" style="min-width:120px;">{{ $ccMadre }}</span></span><br>
        <span>Dirección: <span class="field-line" style="min-width:150px;">&nbsp;</span></span><br>
        <span>Teléfono: <span class="field-line" style="min-width:120px;">&nbsp;</span></span><br>
        <span>Correo: <span class="field-line" style="min-width:150px;">&nbsp;</span></span>
      </div>
    </td>
    <td style="width:50%;padding-left:20px;vertical-align:top;">
      <p class="bold">COLEGIO REMBRANDT</p><br>
      <div class="firma-data">
        <div style="border-top:1px solid #000;margin-top:30px;margin-bottom:3px;"></div>
        <span class="bold">ESMERALDA VILLAMIZAR</span><br>
        <span>Representante Legal</span>
      </div>
      <br><br>
      <div class="firma-data">
        <div style="border-top:1px solid #000;margin-top:30px;margin-bottom:3px;"></div>
        <span class="bold">NOMBRE DEL CODEUDOR</span><br>
        <span>C.C. No. <span class="field-line" style="min-width:120px;">&nbsp;</span></span><br>
        <span>Dirección: <span class="field-line" style="min-width:150px;">&nbsp;</span></span><br>
        <span>Teléfono: <span class="field-line" style="min-width:120px;">&nbsp;</span></span><br>
        <span>Correo: <span class="field-line" style="min-width:150px;">&nbsp;</span></span>
      </div>
    </td>
  </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- FORMATO COMPORTAMIENTO (CV-F07) --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif($tipo === 'comportamiento')

<table class="header-table">
  <tr>
    <td class="header-logo">@if($logoB64)<img src="{{ $logoB64 }}">@endif<div style="font-size:7pt;font-weight:bold;">COLEGIO<br>REMBRANDT</div></td>
    <td class="header-title">CONSENTIMIENTO INFORMADO PARA<br>LA GESTIÓN DE CONDUCTAS INADECUADAS</td>
    <td class="header-escudo">@if($logoB64)<img src="{{ $logoB64 }}">@endif</td>
  </tr>
  <tr class="header-codes">
    <td colspan="3">
      <table style="width:100%;border-collapse:collapse;"><tr>
        <td style="text-align:center;">CV-F07</td>
        <td style="text-align:center;">Versión: 0</td>
        <td style="text-align:center;">Fecha: 15/11/2025</td>
      </tr></table>
    </td>
  </tr>
</table>

<p>Yo, <span class="field-line" style="min-width:220px;">{{ $nomAcud }}</span>, identificado con cédula de ciudadanía No. <span class="field-line" style="min-width:130px;">{{ $ccAcud }}</span>, en calidad de padre o tutor del estudiante <span class="field-line" style="min-width:220px;">{{ $nombreEst }}</span>, identificado con documento No. <span class="field-line" style="min-width:120px;">{{ $docEst }}</span> del grado <span class="field-line" style="min-width:80px;">{{ $grado }} - {{ $curso }}</span>, solicito que se implementen las siguientes medidas para mejorar la conducta y el rendimiento académico de mi Hijo: ya que los docentes se desgastan mucho en estos temas afectando el programa académico y demás compañeros.</p>

<ul>
  <li>Si mi hijo/hija llega tarde, mal presentado, no cumple con el uniforme de diario o sudadera, llega sucio o con otras prendas, o accesorios, maquillaje, es encontrado con celular dentro de clases, o no deja el celular guardado a la 1 hora de clase, se me llame inmediatamente para que venga a recogerlo.</li>
  <li>Esta medida se aplicará después de tres llamados de atención previos debidamente reportada en la plataforma de comunicaciones y será reflejada en la nota de convivencia de mi hijo, afectando su rendimiento en todas las asignaturas.</li>
  <li>Entendemos que esta medida es para el bien de la formación y la educación de mi hijo, y que se busca promover la sana convivencia y el respeto por las normas de la institución.</li>
</ul>

<br><br><br>
<p><span class="bold">Firma del Padre o Tutor:</span> <span class="field-line" style="min-width:220px;">&nbsp;</span></p>
<br>
<p><span class="bold">Fecha:</span> <span class="field-line" style="min-width:120px;">&nbsp;</span></p>
<br><br>
<p class="nota">Nota: Este consentimiento informado debe ser firmado por el padre o tutor del estudiante y debe ser archivado en el expediente del estudiante.</p>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- PAGARÉ (GA-F09) --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif($tipo === 'pagare')

<table class="header-table">
  <tr>
    <td class="header-logo">@if($logoB64)<img src="{{ $logoB64 }}">@endif<div style="font-size:7pt;font-weight:bold;">COLEGIO<br>REMBRANDT</div></td>
    <td class="header-title">PAGARÉ DE GARANTÍA Y CARTA DE INSTRUCCIONES</td>
    <td class="header-escudo">@if($logoB64)<img src="{{ $logoB64 }}">@endif</td>
  </tr>
  <tr class="header-codes">
    <td colspan="3">
      <table style="width:100%;border-collapse:collapse;"><tr>
        <td style="text-align:center;">GA-F09</td>
        <td style="text-align:center;">Versión: 4</td>
        <td style="text-align:center;">Fecha: 25/11/2025</td>
      </tr></table>
    </td>
  </tr>
</table>

<p class="doc-title">TITULO VALOR PAGARE</p>

<p>Por la suma de $<span class="field-line" style="min-width:160px;">&nbsp;</span></p>
<p>Vencimiento(s) <span class="field-line" style="min-width:200px;">&nbsp;</span></p>
<p>Ciudad donde se efectúa el pago: Bogotá D.C.,</p>
<p>Intereses durante el plazo <span class="field-line" style="min-width:180px;">&nbsp;</span></p>
<p>(Nosotros) <span class="field-line" style="min-width:200px;">{{ $nomAcud }}</span> y <span class="field-line" style="min-width:200px;">{{ $nomMadre }}</span>, mayor(es) de edad, con domicilio en la ciudad de Bogotá, actuando en mi(nuestro) propio nombre, declaro(amos) que en virtud del presente título valor pagaré(mos) solidaria e incondicionalmente, a la orden del <span class="bold">COLEGIO REMBRANDT – REMBRANDTEC LTDA.</span>, representada por la señora <span class="bold">ESMERALDA VILLAMIZAR JAIMES</span> o quien represente sus derechos, en la ciudad y fecha de vencimiento arriba indicados o en las fechas de amortización y valores que a continuación se señalan:</p>

<table class="pagare-table">
  @for($i=1; $i<=10; $i++)
  <tr>
    <td class="lbl">La suma de $</td>
    <td><span class="field-line" style="min-width:120px;">&nbsp;</span></td>
    <td>el día</td><td><span class="field-line" style="min-width:40px;">&nbsp;</span></td>
    <td>del mes</td><td><span class="field-line" style="min-width:90px;">&nbsp;</span></td>
    <td>de 20</td><td><span class="field-line" style="min-width:30px;">&nbsp;</span></td>
  </tr>
  @endfor
</table>

<p>En caso de mora pagaré(mos) intereses a la máxima tasa permitida por la Ley comercial y autorizados por la Superfinanciera de Colombia y en caso de cobro judicial o extrajudicial, será(n) de mi (nuestra) cuenta las costas de cobranza y honorarios de abogado. Nos permitimos autorizarlos expresamente para llenar el presente pagaré otorgado en su favor, en los espacios dejados en blanco y correspondiente a la fecha de vencimiento, cuantía e intereses de las obligaciones a nuestro cargo surgidas del contrato de servicio educativo pactado.</p>

<p>Para constancia y validez se firma en la ciudad de Bogotá, a los <span class="field-line" style="min-width:40px;">&nbsp;</span> días del mes de <span class="field-line" style="min-width:100px;">&nbsp;</span> de 20<span class="field-line" style="min-width:30px;">&nbsp;</span>.</p>
<p>Aceptamos:</p>

<table style="width:100%;margin-top:14px;border-collapse:collapse;">
  <tr>
    <td style="width:33%;vertical-align:top;padding-right:10px;">
      <div style="border-top:1px solid #000;margin-top:40px;margin-bottom:4px;"></div>
      <p class="bold">NOMBRE PADRE Y/O ACUDIENTE</p>
      <p>C.C. <span class="field-line" style="min-width:120px;">{{ $ccAcud }}</span></p>
      <p>DIRECCIÓN <span class="field-line" style="min-width:120px;">{{ $dirAcud }}</span></p>
      <p>TELÉFONOS <span class="field-line" style="min-width:120px;">{{ $telAcud }}</span></p>
    </td>
    <td style="width:33%;vertical-align:top;padding:0 10px;">
      <div style="border-top:1px solid #000;margin-top:40px;margin-bottom:4px;"></div>
      <p class="bold">NOMBRE MADRE Y/O ACUDIENTE</p>
      <p>C.C. <span class="field-line" style="min-width:120px;">{{ $ccMadre }}</span></p>
      <p>DIRECCIÓN <span class="field-line" style="min-width:120px;">&nbsp;</span></p>
      <p>TELÉFONOS <span class="field-line" style="min-width:120px;">&nbsp;</span></p>
    </td>
    <td style="width:33%;vertical-align:top;padding-left:10px;">
      <div style="border-top:1px solid #000;margin-top:40px;margin-bottom:4px;"></div>
      <p class="bold">NOMBRE CODEUDOR</p>
      <p>C.C. <span class="field-line" style="min-width:120px;">&nbsp;</span></p>
      <p>DIRECCIÓN <span class="field-line" style="min-width:120px;">&nbsp;</span></p>
      <p>TELÉFONOS <span class="field-line" style="min-width:120px;">&nbsp;</span></p>
    </td>
  </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- AUTORIZACIÓN CONSULTA Y REPORTE (GA.F10) --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif($tipo === 'autorizacion_consulta')

<table class="header-table">
  <tr>
    <td class="header-logo">@if($logoB64)<img src="{{ $logoB64 }}">@endif<div style="font-size:7pt;font-weight:bold;">COLEGIO<br>REMBRANDT</div></td>
    <td class="header-title">AUTORIZACIÓN DE CONSULTA Y REPORTE<br>A CENTRALES DE RIESGOS</td>
    <td class="header-escudo">@if($logoB64)<img src="{{ $logoB64 }}">@endif</td>
  </tr>
  <tr class="header-codes">
    <td colspan="3">
      <table style="width:100%;border-collapse:collapse;"><tr>
        <td style="text-align:center;">GA.F10</td>
        <td style="text-align:center;">Versión: 2</td>
        <td style="text-align:center;">Fecha: 30/11/2023</td>
      </tr></table>
    </td>
  </tr>
</table>

<p>Lea cuidadosamente la siguiente cláusula y pregunte lo que no comprenda:</p>
<p>El abajo firmante, en su propio nombre, declara que la información suministrada es verídica y da su consentimiento expreso e irrevocable al <span class="bold">COLEGIO REMBRANDT – REMBRANDTEC LTDA</span>, como acreedor del crédito o servicio solicitado, para:</p>

<ul>
  <li>Consultar, en cualquier tiempo, en <span class="bold">CENTRALES DE RIESGOS</span> o en cualquier otra base de datos manejada por un operador, toda la información relevante para conocer su desempeño como deudor, su capacidad de pago, la viabilidad para entablar o mantener con él una relación contractual, o para incluirlo en actividades como la realización de campañas de mercadeo, ofrecimiento de productos y publicidad en general.</li>
  <li>Reportar a <span class="bold">CENTRALES DE RIESGOS</span> o a cualquier otra base de datos tratados o sin tratar, sobre el cumplimiento o incumplimiento de sus obligaciones crediticias, sus deberes legales de contenido patrimonial, sus datos de ubicación y contacto, sus solicitudes de crédito, así como otra información ateniente a sus relaciones comerciales, financieras y en general socioeconómicas.</li>
</ul>

<p>La autorización anterior no impedirá al abajo firmante ejercer el derecho de corroborar en cualquier tiempo en la entidad, en CENTRALES DE RIESGOS o en la central de información de riesgo, que la información suministrada es veraz, completa, exacta y actualizada.</p>

<p>Nombre de la empresa: <span class="bold">COLEGIO REMBRANDT – REMBRANDTEC LTDA.</span></p>
<p>Nombre del Representante Legal: <span class="bold">ESMERALDA VILLAMIZAR JAIMES</span></p>
<p>Firma del Representante Legal: <span class="field-line" style="min-width:200px;">&nbsp;</span></p>

<p>Declaro bajo mi responsabilidad que la información consignada en este formato es veraz y que se ha recibido o conocido en el sitio web en el que están los documentos anexados al contrato.</p>

<p>Ciudad <span class="field-line" style="min-width:180px;">Bogotá</span> Fecha <span class="field-line" style="min-width:40px;">&nbsp;</span>/<span class="field-line" style="min-width:40px;">&nbsp;</span>/<span class="field-line" style="min-width:40px;">&nbsp;</span></p>

<br>
<p><span class="bold">Firma:</span> <span class="field-line" style="min-width:200px;">&nbsp;</span></p>
<p><span class="bold">Nombre del Padre o Acudiente:</span> <span class="field-line" style="min-width:200px;">{{ $nomAcud }}</span></p>
<p><span class="bold">Cédula:</span> <span class="field-line" style="min-width:130px;">{{ $ccAcud }}</span></p>
<p><span class="bold">Teléfono fijo:</span> <span class="field-line" style="min-width:130px;">&nbsp;</span> <span class="bold">Móvil:</span> <span class="field-line" style="min-width:110px;">{{ $telAcud }}</span></p>
<p><span class="bold">Dirección:</span> <span class="field-line" style="min-width:300px;">{{ $dirAcud }}</span></p>
<p><span class="bold">Empresa donde Labora:</span> <span class="field-line" style="min-width:250px;">&nbsp;</span></p>
<p><span class="bold">Teléfonos empresa:</span> <span class="field-line" style="min-width:150px;">&nbsp;</span></p>

<br>
<p><span class="bold">Firma:</span> <span class="field-line" style="min-width:200px;">&nbsp;</span></p>
<p><span class="bold">Nombre del Deudor:</span> <span class="field-line" style="min-width:200px;">&nbsp;</span></p>
<p><span class="bold">Cédula:</span> <span class="field-line" style="min-width:130px;">&nbsp;</span></p>
<p><span class="bold">Teléfono fijo:</span> <span class="field-line" style="min-width:130px;">&nbsp;</span> <span class="bold">Móvil:</span> <span class="field-line" style="min-width:110px;">&nbsp;</span></p>
<p><span class="bold">Dirección:</span> <span class="field-line" style="min-width:300px;">&nbsp;</span></p>
<p><span class="bold">Empresa donde Labora:</span> <span class="field-line" style="min-width:250px;">&nbsp;</span></p>
<p><span class="bold">Teléfonos empresa:</span> <span class="field-line" style="min-width:150px;">&nbsp;</span></p>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- AUTORIZACIÓN USO DE PAGARÉ (GA.F13) --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif($tipo === 'autorizacion_pagare')

<table class="header-table">
  <tr>
    <td class="header-logo">@if($logoB64)<img src="{{ $logoB64 }}">@endif<div style="font-size:7pt;font-weight:bold;">COLEGIO<br>REMBRANDT</div></td>
    <td class="header-title">AUTORIZACIÓN USO DE PAGARE</td>
    <td class="header-escudo">@if($logoB64)<img src="{{ $logoB64 }}">@endif</td>
  </tr>
  <tr class="header-codes">
    <td colspan="3">
      <table style="width:100%;border-collapse:collapse;"><tr>
        <td style="text-align:center;">GA.F13</td>
        <td style="text-align:center;">Versión: 2</td>
        <td style="text-align:center;">Fecha: 11/11/2025</td>
      </tr></table>
    </td>
  </tr>
</table>

<p>Bogotá D.C. <span class="field-line" style="min-width:40px;">&nbsp;</span> del mes de <span class="field-line" style="min-width:100px;">&nbsp;</span> de {{ $anio }}</p>
<br>
<p>Señores:<br><span class="bold">COLEGIO REMBRANDT LTDA</span><br>Att. Directivos<br>Ciudad.</p>
<br>
<p>Los suscritos <span class="field-line" style="min-width:200px;">{{ $nomAcud }}</span> Y <span class="field-line" style="min-width:200px;">{{ $nomMadre }}</span>, mayores de edad, con domicilio y residencia en Bogotá, identificados como aparece al pie de nuestras correspondientes firmas, en calidad de contratantes, como padres y/o acudientes de los menores <span class="field-line" style="min-width:200px;">{{ $nombreEst }}</span>, de manera atenta nos permitimos autorizarlos de manera expresa, libre y voluntaria para que usen y dispongan, si a ello hubiere lugar, de los títulos valores pagarés y de las cartas de instrucción para llenar los pagarés entregados al colegio en atención al contrato de servicio educativo celebrado en el año {{ $anio }}, y sea o pueda ser utilizados con iguales propósitos para el cumplimiento de obligaciones surgidas de los contratos de prestación de servicio educativo.</p>

<p>Del mismo modo nos permitimos ratificar la autorización dada al colegio para el uso y tratamiento de datos personales de que trata la Ley 1581 de 2012, reglamentada por el Decreto 1377 de 2013. Por tanto, la autorización otorgada al colegio podrá seguir siendo usada en iguales términos y fines a la ya conferida, sin restricciones.</p>

<p>Todo lo anterior habida cuenta que nuestros hijos ya citados han estado vinculados como alumnos del colegio y los suscritos, hemos firmado pagarés, carta de instrucción para llenar el pagaré y autorización para el manejo o tratamiento de datos personales.</p>

<p>Para constancia y validez firmamos, los <span class="field-line" style="min-width:40px;">&nbsp;</span> días del mes de <span class="field-line" style="min-width:100px;">&nbsp;</span> de {{ $anio }}.</p>

<table style="width:100%;margin-top:24px;border-collapse:collapse;">
  <tr>
    <td style="width:50%;vertical-align:top;padding-right:20px;">
      <div style="border-top:1px solid #000;margin-top:40px;margin-bottom:4px;"></div>
      <p><span class="bold">NOMBRE:</span> <span class="field-line" style="min-width:160px;">{{ $nomAcud }}</span></p>
      <p><span class="bold">C.C. No.</span> <span class="field-line" style="min-width:120px;">{{ $ccAcud }}</span></p>
      <p><span class="bold">Dirección:</span> <span class="field-line" style="min-width:150px;">{{ $dirAcud }}</span></p>
      <p><span class="bold">Tel:</span> <span class="field-line" style="min-width:120px;">{{ $telAcud }}</span></p>
      <p><span class="bold">Correo:</span> <span class="field-line" style="min-width:150px;">{{ $corrAcud }}</span></p>
    </td>
    <td style="width:50%;vertical-align:top;padding-left:20px;">
      <div style="border-top:1px solid #000;margin-top:40px;margin-bottom:4px;"></div>
      <p><span class="bold">NOMBRE:</span> <span class="field-line" style="min-width:160px;">{{ $nomMadre }}</span></p>
      <p><span class="bold">C.C. No.</span> <span class="field-line" style="min-width:120px;">{{ $ccMadre }}</span></p>
      <p><span class="bold">Dirección:</span> <span class="field-line" style="min-width:150px;">&nbsp;</span></p>
      <p><span class="bold">Tel:</span> <span class="field-line" style="min-width:120px;">&nbsp;</span></p>
      <p><span class="bold">Correo:</span> <span class="field-line" style="min-width:150px;">&nbsp;</span></p>
    </td>
  </tr>
</table>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- AUTORIZACIÓN TRATAMIENTO DE DATOS (GA-F14) --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif($tipo === 'autorizacion_datos')

<table class="header-table">
  <tr>
    <td class="header-logo">@if($logoB64)<img src="{{ $logoB64 }}">@endif<div style="font-size:7pt;font-weight:bold;">COLEGIO<br>REMBRANDT</div></td>
    <td class="header-title">AUTORIZACIÓN TRATAMIENTO DE DATOS<br>PERSONALES</td>
    <td class="header-escudo">@if($logoB64)<img src="{{ $logoB64 }}">@endif</td>
  </tr>
  <tr class="header-codes">
    <td colspan="3">
      <table style="width:100%;border-collapse:collapse;"><tr>
        <td style="text-align:center;">GA-F14</td>
        <td style="text-align:center;">Versión: 1</td>
        <td style="text-align:center;">Fecha: 30/11/2023</td>
      </tr></table>
    </td>
  </tr>
</table>

<p>Bogotá, <span class="field-line" style="min-width:30px;">&nbsp;</span> de <span class="field-line" style="min-width:100px;">&nbsp;</span> de {{ $anio }}</p>
<br>
<p>Señores<br><span class="bold">COLEGIO REMBRANDT</span><br>Att. Directivos<br>Bogotá</p>
<br>
<p><span class="bold">Ref. Autorización para el tratamiento de datos.</span></p>
<br>
<p>El suscrito(a) <span class="field-line" style="min-width:220px;">{{ $nomAcud }}</span>, mayor de edad, domiciliado(a) en Bogotá, identificado(a) como aparece al pie de mi firma, actuando en calidad de padre/madre y/o acudiente del menor(res) <span class="field-line" style="min-width:250px;">{{ $nombreEst }}</span>, de manera atenta me permito manifestarle lo siguiente:</p>

<p>Teniendo en cuenta que el <span class="bold">COLEGIO REMBRANDT LTDA</span>, en desarrollo de la actividad académica, ha celebrado con el suscrito y entrado en ejecución contratos de prestación de servicio educativo y otros, donde se ha recogido información la que mantiene en archivos, incluyendo datos personales nuestros, como del menor o menores antes relacionado, dado la existencia de contrato de prestación de servicio educativo(s) y en virtud de lo establecido en la Ley 1581 de 2012 reglamentada por el Decreto 1377 de 2013, que buscan desarrollar el derecho constitucional que tienen todas las personas a conocer, actualizar, autorizar y rectificar las informaciones que se han recogido sobre ellas en bases de datos o archivos; de manera atenta me(nos) permito(timos) autorizarlos como titulares de la información para que la existente y la que suministre o pueda ser recolectada sea registrada en su base de datos y pueda ser utilizada o tratada, además de lo permitido por la ley, para las siguientes finalidades:</p>

<p>Para mantener comunicación constante con nosotros en cumplimiento de las obligaciones académicas y económicas.</p>
<p>Para el ejercicio de su objeto social y realizar las gestiones necesarias para dar cumplimiento a las obligaciones inherentes a los servicios educativos y complementarios contratados con la Institución.</p>
<p>Realizar actividades de facturación, cobranzas, recaudo, consultas, verificación control, prevención de fraudes, así como cualquier otra actividad relacionada con el servicio educativo y complementarios.</p>
<p>De otra parte manifestó que se conocen los derechos que tengo como titular de la información, incluyendo la posibilidad de solicitar la supresión de datos, para lo cual cuento con su dirección física, electrónica y teléfono.</p>
<p>El Colegio debe garantizar la seguridad, transparencia y correcto uso de la información que reposa en sus bases de datos, la cual podremos verificar y actualizar en cualquier momento como titular(es).</p>

<br>
<p>Cordialmente,</p>
<br><br>
<div style="border-top:1px solid #000;width:240px;margin-bottom:4px;"></div>
<p><span class="bold">NOMBRE:</span> <span class="field-line" style="min-width:180px;">{{ $nomAcud }}</span></p>
<p><span class="bold">C.C. No:</span> <span class="field-line" style="min-width:150px;">{{ $ccAcud }}</span></p>

@endif

</body>
</html>
