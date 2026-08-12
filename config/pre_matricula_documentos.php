<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Documentos guía para matrícula
    |--------------------------------------------------------------------------
    |
    | Ninguno de estos documentos es obligatorio para enviar la
    | pre-matrícula.
    |
    | La familia adjunta los documentos que tenga disponibles.
    | El colegio podrá completar posteriormente los documentos faltantes.
    |
    | El código de cada documento debe mantenerse estable porque será el
    | valor almacenado en pre_matricula_documentos.tipo_documento.
    |
    */

    'tipos' => [

        'paz_salvo_original' => [
            'nombre' => 'Paz y salvo Original',
        ],

        'entrevista_admision' => [
            'nombre' => 'Entrevista de admisión',
        ],

        'entrevista_orientacion_rectoria_direccion' => [
            'nombre' => 'Entrevista de Orientación, Rectoría y Director Administrativa',
        ],

        'examen_resultados' => [
            'nombre' => 'Examen y resultados',
        ],

        'orden_matricula' => [
            'nombre' => 'Orden de matrícula',
        ],

        'pago_matricula' => [
            'nombre' => 'Pago matrícula',
        ],

        'registro_civil' => [
            'nombre' => 'Fotocopia registro civil de nacimiento legible',
        ],

        'tarjeta_identidad' => [
            'nombre' => 'Fotocopia de la tarjeta de identidad 7 años cumplidos en adelante',
        ],

        'certificado_medico' => [
            'nombre' => 'Certificado médico del estudiante no mayor a 30 días',
        ],

        'carnet_vacunas' => [
            'nombre' => 'Fotocopia del carnet de vacunas para grados pre-jardín a primero',
        ],

        'afiliacion_eps_sisben' => [
            'nombre' => 'Certificado de afiliación del niño a la EPS y/o Sisbén',
        ],

        'retiro_simat' => [
            'nombre' => 'Constancia de retiro del estudiante del SIMAT, expedido por el colegio que procede',
        ],

        'servicio_social' => [
            'nombre' => 'Certificado de servicio Social',
        ],

        'documento_padres' => [
            'nombre' => 'Copia del documento de identidad de los padres de familia',
        ],

        'documento_acudiente' => [
            'nombre' => 'Copia del documento de identidad del Acudiente',
        ],

        'documento_codeudor' => [
            'nombre' => 'Copia del documento de identidad del Codeudor cuando aplique',
        ],

        'certificados_estudios_anteriores' => [
            'nombre' => 'Certificados de estudios años anteriores',
        ],

        'certificado_laboral_padres' => [
            'nombre' => 'Certificado laboral de los padres de familia no mayor a 30 días',
        ],

        'certificado_laboral_codeudor' => [
            'nombre' => 'Certificado laboral del Codeudor no mayor a 30 días si aplica',
        ],

        'recibo_publico_domicilio' => [
            'nombre' => 'Copia del recibo público del domicilio del padre de familia y Codeudor si aplica',
        ],

        'hoja_matricula' => [
            'nombre' => 'Hoja de Matrícula',
        ],

        'matricula_electronica' => [
            'nombre' => 'Click and sign matrícula electrónica soporte firma electrónica (contrato, pagaré, tratamiento de datos, consulta de centrales de riesgos y RAE).',
        ],

    ],

    'tipos_formulario' => [

        'registro_civil' => [
            'nombre' => 'Fotocopia registro civil de nacimiento legible',
        ],

        'tarjeta_identidad' => [
            'nombre' => 'Fotocopia de la tarjeta de identidad 7 años cumplidos en adelante',
        ],

        'certificado_medico' => [
            'nombre' => 'Certificado médico del estudiante no mayor a 30 días',
        ],

        'carnet_vacunas' => [
            'nombre' => 'Fotocopia del carnet de vacunas para grados pre-jardín a primero',
        ],

        'afiliacion_eps_sisben' => [
            'nombre' => 'Certificado de afiliación del niño a la EPS y/o Sisbén',
        ],

        'retiro_simat' => [
            'nombre' => 'Constancia de retiro del estudiante del SIMAT, expedido por el colegio que procede',
        ],

        'documento_padres' => [
            'nombre' => 'Copia del documento de identidad de los padres de familia',
        ],

        'documento_acudiente' => [
            'nombre' => 'Copia del documento de identidad del Acudiente',
        ],

        'documento_codeudor' => [
            'nombre' => 'Copia del documento de identidad del Codeudor cuando aplique',
        ],

        'certificado_laboral_padres' => [
            'nombre' => 'Certificado laboral de los padres de familia no mayor a 30 días',
        ],

        'certificado_laboral_codeudor' => [
            'nombre' => 'Certificado laboral del Codeudor no mayor a 30 días si aplica',
        ],

        'recibo_publico_domicilio' => [
            'nombre' => 'Copia del recibo público del domicilio del padre de familia y Codeudor si aplica',
        ],

    ],

];