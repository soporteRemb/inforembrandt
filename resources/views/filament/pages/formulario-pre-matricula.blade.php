<x-filament-panels::page>

    @php
        $esUsuarioTemporal = auth()->user()?->hasRole('temporal') ?? false;
    @endphp

    @if($esUsuarioTemporal)
        <style>
            /*
            |--------------------------------------------------------------------------
            | Vista exclusiva para usuarios temporales
              esta pantalla la ve la persona que va a llenar el formulario
            |--------------------------------------------------------------------------
            |
            | Se oculta únicamente la navegación lateral.
            | Se conserva la barra superior con:
            | - nombre de la sede y período;
            | - fecha y hora;
            | - botón de perfil;
            | - opción para cerrar sesión.
            |
            */

            .fi-sidebar {
                display: none !important;
            }

            .fi-main-ctn {
                margin-left: 0 !important;
            }

            .fi-main {
                width: 100% !important;
            }
            /* Ocultar breadcrumb */
            .fi-breadcrumbs {
                display: none !important;
            }

            /* Eliminar el espacio superior que deja el breadcrumb */
            .fi-header {
                padding-top: 0 !important;
            }
        </style>
    @endif







    <style>
        .fi-main {
            max-width: none !important;
        }

        .prematricula-page,
        .prematricula-page * {
            box-sizing: border-box;
        }

        .prematricula-page {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 14px;
            color: #253047;
        }

        .prematricula-header {
            min-height: 170px;
            padding: 18px 34px;
            display: grid;
            grid-template-columns: 210px minmax(0, 1fr) 210px;
            align-items: center;
            gap: 24px;
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(91, 22, 26, .38);
            border-radius: 14px;
            background:
                radial-gradient(
                    circle at 50% -80%,
                    rgba(255, 255, 255, .08),
                    transparent 58%
                ),
                linear-gradient(
                    115deg,
                    #64181c 0%,
                    #822126 46%,
                    #68171b 100%
                );
            box-shadow:
                0 9px 24px rgba(54, 15, 18, .17),
                inset 0 1px 0 rgba(255, 255, 255, .08);
            color: #ffffff;
        }

        .prematricula-header::before,
        .prematricula-header::after {
            content: "";
            position: absolute;
            pointer-events: none;
            border-radius: 999px;
            background: rgba(255, 255, 255, .025);
        }

        .prematricula-header::before {
            width: 520px;
            height: 130px;
            right: -170px;
            top: -65px;
            transform: rotate(-9deg);
        }

        .prematricula-header::after {
            width: 430px;
            height: 105px;
            left: -150px;
            bottom: -62px;
            transform: rotate(11deg);
        }

        .prematricula-header-logo,
        .prematricula-header-content {
            position: relative;
            z-index: 1;
        }

        .prematricula-header-logo {
            min-width: 0;
            height: 128px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .prematricula-header-logo-left {
            border-right: 1px solid rgba(225, 185, 79, .72);
            padding-right: 26px;
        }

        .prematricula-header-logo-right {
            border-left: 1px solid rgba(225, 185, 79, .72);
            padding-left: 26px;
        }

        .prematricula-header-logo img {
            display: block;
            max-width: 100%;
            object-fit: contain;
            filter: drop-shadow(0 5px 7px rgba(0, 0, 0, .18));
        }

        .prematricula-header-logo-left img {
            width: 128px;
            max-height: 122px;
        }

        .prematricula-header-logo-right img {
            width: 150px;
            max-height: 145px;
        }

        .prematricula-header-content {
            min-width: 0;
            padding: 0 12px;
            text-align: center;
        }

        .prematricula-header-content h1 {
            margin: 0;
            color: #ffffff;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(28px, 2.2vw, 38px);
            line-height: 1.08;
            font-weight: 500;
            letter-spacing: -.35px;
            text-shadow: 0 2px 3px rgba(37, 9, 11, .23);
        }

        .prematricula-header-content p {
            max-width: 720px;
            margin: 30px auto 0;
            color: rgba(255, 255, 255, .93);
            font-size: 14px;
            line-height: 1.45;
        }



        .prematricula-card {
            min-width: 0;
            overflow: hidden;
            border: 1px solid #dde3ea;
            border-radius: 11px;
            background: #ffffff;
            box-shadow: 0 3px 13px rgba(15, 23, 42, .055);
        }

        .prematricula-card-header {
            min-height: 48px;
            padding: 13px 20px 9px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #c9141d;
        }


        .prematricula-card-header h2 {
            margin: 0;
            font-size: 17px;
            line-height: 1.2;
            font-weight: 800;
        }

        .prematricula-card-body {
            padding: 2px 20px 20px;
        }

        .prematricula-student-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0;
        }

        .prematricula-student-column {
            min-width: 0;
            padding: 0 26px;
            display: flex;
            flex-direction: column;
            gap: 11px;
        }

        .prematricula-student-column:first-child {
            padding-left: 0;
        }

        .prematricula-student-column:last-child {
            padding-right: 0;
        }

        .prematricula-student-column + .prematricula-student-column {
            border-left: 1px dashed #d8dee7;
        }

        .prematricula-responsables-grid {
            display: grid;
            grid-template-columns: .95fr .95fr 1.08fr;
            gap: 12px;
            align-items: stretch;
        }

        .prematricula-responsables-grid .prematricula-card {
            height: 100%;
        }

        .prematricula-responsables-grid .prematricula-card-body {
            padding-top: 3px;
        }

        .prematricula-two-columns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 11px 14px;
        }

        .prematricula-field {
            min-width: 0;
        }

        .prematricula-field-full {
            grid-column: 1 / -1;
        }

        .prematricula-field label,
        .prematricula-group-label {
            display: block;
            margin: 0 0 5px;
            font-size: 12px;
            line-height: 1.2;
            font-weight: 700;
            color: #334155;
        }

        .prematricula-required {
            color: #dc2626;
        }

        .prematricula-input,
        .prematricula-select {
            width: 100%;
            height: 39px;
            padding: 0 11px;
            border: 1px solid #d5dce5;
            border-radius: 6px;
            background-color: #ffffff;
            color: #1e293b;
            font-size: 13px;
            outline: none;
            transition:
                border-color .15s ease,
                box-shadow .15s ease,
                background-color .15s ease;
        }

        .prematricula-select {
            padding-right: 38px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
        }

        .prematricula-select::-ms-expand {
            display: none;
        }

        .prematricula-input::placeholder {
            color: #9aa4b2;
        }

        .prematricula-input:focus,
        .prematricula-select:focus {
            border-color: #c71922;
            box-shadow: 0 0 0 3px rgba(199, 25, 34, .08);
        }

        .prematricula-input[readonly],
        .prematricula-input:disabled,
        .prematricula-select:disabled {
            background: #f3f4f6;
            color: #64748b;
            cursor: not-allowed;
        }

        .prematricula-help {
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            line-height: 1.25;
            color: #536075;
        }

        .prematricula-help svg {
            width: 13px;
            height: 13px;
            flex: 0 0 13px;
            color: #2563eb;
        }

        .prematricula-origin-options {
            min-height: 37px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px 18px;
        }

        .prematricula-origin-option {
            padding: 0;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: 0;
            background: transparent;
            color: #475569;
            font-size: 12px;
            cursor: pointer;
        }

        .prematricula-origin-dot {
            width: 15px;
            height: 15px;
            display: inline-grid;
            place-items: center;
            border: 1.5px solid #94a3b8;
            border-radius: 999px;
            background: #ffffff;
        }

        .prematricula-origin-dot::after {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: transparent;
        }

        .prematricula-origin-option.is-active {
            color: #b91c1c;
            font-weight: 700;
        }

        .prematricula-origin-option.is-active .prematricula-origin-dot {
            border-color: #e11d2e;
        }

        .prematricula-origin-option.is-active .prematricula-origin-dot::after {
            background: #e11d2e;
        }

        .prematricula-notice {
            min-height: 58px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid #efc56b;
            border-radius: 8px;
            background: #fff9eb;
        }

        .prematricula-notice-icon {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            color: #ffffff;
            background: #f2a700;
        }

        .prematricula-notice-icon svg {
            width: 21px;
            height: 21px;
        }

        .prematricula-notice strong {
            display: block;
            margin-bottom: 2px;
            font-size: 13px;
            color: #2f3747;
        }

        .prematricula-notice p {
            margin: 0;
            font-size: 12px;
            line-height: 1.35;
            color: #3f4756;
        }

        .prematricula-actions {
            padding-top: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 7px;
        }

        .prematricula-submit{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:10px;

            min-width:310px;
            height:46px;

            background:#c1121f;
            color:#fff;

            border:none;
            border-radius:10px;

            font-size:16px;
            font-weight:500;

            cursor:pointer;
            transition:.18s ease;
        }

        .prematricula-submit:hover{
            background:#a80f1b;
        }

        .prematricula-submit:active{
            transform:translateY(1px);
        }

        .prematricula-submit svg{
            width:18px;
            height:18px;
        }

        .prematricula-privacy {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 11px;
            color: #697386;
            text-align: center;
        }

        .prematricula-privacy svg {
            width: 14px;
            height: 14px;
        }

        .prematricula-privacy a {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #2359a8;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .prematricula-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 100;
            padding: 22px;
            display: grid;
            place-items: center;
            background: rgba(15, 23, 42, .48);
            backdrop-filter: blur(2px);
        }
        

        .prematricula-modal {
            width: min(460px, 100%);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 13px;
            background: #ffffff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .25);
        }

        .prematricula-modal-body {
            padding: 27px 28px 22px;
            text-align: center;
        }

        .prematricula-modal-body h3 {
            margin: 0;
            color: #0f172a;
            font-size: 20px;
            line-height: 1.25;
            font-weight: 700;
        }

        .prematricula-modal-description {
            margin: 9px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.45;
        }

        .prematricula-modal-success {
            max-width: 450px;
        }

        .prematricula-modal-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 14px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background: #fff1f2;
            color: #c71922;
        }

        .prematricula-modal-icon svg {
            width: 25px;
            height: 25px;
        }

        .prematricula-modal h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            color: #172033;
        }

        .prematricula-modal p {
            margin: 9px 0 0;
            font-size: 13px;
            line-height: 1.5;
            color: #64748b;
        }

        .prematricula-modal-warning {
            margin-top: 14px;
            padding: 10px 12px;
            border: 1px solid #fed7aa;
            border-radius: 7px;
            background: #fff7ed;
            color: #9a3412;
            font-size: 12px;
            line-height: 1.4;
        }

        .prematricula-modal-actions {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 9px;
            border-top: 1px solid #e8edf3;
            background: #fafbfc;
        }

        .prematricula-modal-button {
            min-height: 37px;
            padding: 0 15px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .prematricula-modal-cancel {
            border: 1px solid #d8dee7;
            background: #ffffff;
            color: #475569;
        }

        .prematricula-modal-confirm {
            border: 1px solid #b91c1c;
            background: #c71922;
            color: #ffffff;
            white-space: nowrap;
        }

        .prematricula-card-title{
            display:flex;
            align-items:center;
            gap:12px;
            margin-bottom:18px;
        }

        .prematricula-card-number{
            width:30px;
            height:30px;

            display:flex;
            align-items:center;
            justify-content:center;

            border-radius:50%;

            background:#fdeaea;
            color:#b91c1c;

            font-size:15px;
            font-weight:700;

            flex-shrink:0;
        }

        .prematricula-card-title h3{
            margin:0;
            font-size:28px;
            font-weight:700;
            color:#c1121f;
        }


        .prematricula-modal-warning p {
            margin: 0;
            line-height: 1.45;
        }

        .prematricula-modal-warning p + p {
            margin-top: 10px;
        }


        .prematricula-error {
            display: block;
            margin-top: 5px;
            color: #dc2626;
            font-size: 10px;
            line-height: 1.3;
        }

        .prematricula-responsable-error {
            margin-top: 12px;
            padding: 10px 14px;
            border: 1px solid #fecaca;
            border-radius: 8px;
            background: #fff1f2;
            color: #b91c1c;
            font-size: 12px;
            line-height: 1.4;
        }


        .prematricula-success-modal {
            max-width: 470px;
        }

        .prematricula-success-icon {
            width: 52px;
            height: 52px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #ecfdf3;
            color: #15803d;
        }

        .prematricula-success-icon svg {
            width: 28px;
            height: 28px;
        }

        .prematricula-success-message {
            margin-top: 18px;
            padding: 14px 16px;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            background: #f0fdf4;
            color: #475569;
            font-size: 13px;
            line-height: 1.5;
        }

        .prematricula-success-message p {
            margin: 0;
        }

        .prematricula-success-message p + p {
            margin-top: 10px;
        }


        .prematricula-modal-process {
            margin-top: 12px;
            padding: 12px 14px;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            background: #f0fdf4;
            color: #475569;
            font-size: 13px;
            line-height: 1.45;
        }

        .prematricula-modal-process p {
            margin: 0;
        }




        @media (max-width: 1250px) {
            .prematricula-student-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 20px 0;
            }

            .prematricula-student-column:nth-child(3) {
                padding-left: 0;
                border-left: 0;
            }

            .prematricula-responsables-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .prematricula-responsables-grid .prematricula-card:last-child {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 850px) {
            .prematricula-header {
                grid-template-columns: 115px minmax(0, 1fr) 115px;
                min-height: 150px;
                padding: 18px;
                gap: 15px;
            }

            .prematricula-header-logo {
                height: 105px;
            }

            .prematricula-header-logo-left {
                padding-right: 15px;
            }

            .prematricula-header-logo-right {
                padding-left: 15px;
            }

            .prematricula-header-logo-left img {
                width: 90px;
                max-height: 94px;
            }

            .prematricula-header-logo-right img {
                width: 102px;
                max-height: 105px;
            }

            .prematricula-header-content {
                padding: 0 5px;
                text-align: center;
            }

            .prematricula-header-content p {
                margin-left: auto;
                margin-right: auto;
            }

            .prematricula-student-grid,
            .prematricula-responsables-grid {
                grid-template-columns: 1fr;
            }

            .prematricula-student-column {
                padding: 17px 0 0;
                border-left: 0 !important;
                border-top: 1px dashed #d8dee7;
            }

            .prematricula-student-column:first-child {
                padding-top: 0;
                border-top: 0;
            }

            .prematricula-responsables-grid .prematricula-card:last-child {
                grid-column: auto;
            }
        }

        @media (max-width: 560px) {

            .prematricula-header {
                grid-template-columns: 78px minmax(0, 1fr) 78px;
                min-height: 145px;
                padding: 15px 10px;
                gap: 8px;
            }

            .prematricula-header-logo {
                height: 88px;
            }

            .prematricula-header-logo-left {
                padding-right: 8px;
            }

            .prematricula-header-logo-right {
                padding-left: 8px;
            }

            .prematricula-header-logo-left img {
                width: 65px;
                max-height: 70px;
            }

            .prematricula-header-logo-right img {
                width: 70px;
                max-height: 76px;
            }

            .prematricula-header-content h1 {
                font-size: 24px;
            }

            .prematricula-header-content p {
                margin-top: 8px;
                font-size: 11px;
                line-height: 1.35;
            }

            .prematricula-header-email {
                margin-top: 6px;
                font-size: 10px;
                gap: 4px;
            }

            .prematricula-header-email svg {
                width: 13px;
                height: 13px;
            }

            .prematricula-header {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .prematricula-header-content {
                text-align: center;
            }

            .prematricula-header-content h1 {
                font-size: 27px;
            }

            .prematricula-header-content p {
                font-size: 13px;
            }

            .prematricula-header-email {
                font-size: 13px;
            }

            .prematricula-two-columns {
                grid-template-columns: 1fr;
            }

            .prematricula-submit {
                width: 100%;
                min-width: 0;
            }

            .prematricula-card-body {
                padding-left: 15px;
                padding-right: 15px;
            }

            .prematricula-card-header {
                padding-left: 15px;
                padding-right: 15px;
            }

            .prematricula-notice {
                align-items: flex-start;
                padding: 14px;
                gap: 12px;
            }

            .prematricula-aviso-texto {
                min-width: 0;
                flex: 1;
            }

            .prematricula-notice p,
            .prematricula-notice strong {
                max-width: 100%;
                overflow-wrap: anywhere;
                word-break: normal;
            }


            
        }



        /* =========================================================
        DOCUMENTOS PARA MATRÍCULA
        ========================================================= */

        .prematricula-documentos-intro {
            margin-bottom: 20px;
            padding: 14px 18px;
            border: 1px solid #bfdbfe;
            border-radius: 9px;
            background: #f8fbff;
        }

        .prematricula-documentos-intro-title {
            margin-bottom: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #2563eb;
            font-size: 13px;
            font-weight: 800;
        }

        .prematricula-documentos-intro-title svg {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
        }

        .prematricula-documentos-pasos {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .prematricula-documentos-paso {
            min-width: 0;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .prematricula-documentos-paso-numero {
            width: 25px;
            height: 25px;
            flex: 0 0 25px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background: #2563eb;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
        }

        .prematricula-documentos-paso-contenido {
            min-width: 0;
        }

        .prematricula-documentos-paso-contenido strong {
            display: block;
            margin-bottom: 3px;
            color: #2563eb;
            font-size: 12px;
            font-weight: 800;
        }

        .prematricula-documentos-paso-contenido p {
            margin: 0;
            color: #475569;
            font-size: 11px;
            line-height: 1.4;
        }


        /* FILA DE CARGA */

        .prematricula-documentos-form {
            display: grid;
            grid-template-columns:
                minmax(280px, 1fr)
                minmax(300px, 1fr)
                auto;
            gap: 12px;
            align-items: start;
        }

        .prematricula-documentos-form .prematricula-field {
            display: flex;
            flex-direction: column;
        }

        .prematricula-documentos-form .prematricula-field > label {
            min-height: 18px;
        }

        .prematricula-documentos-form .prematricula-error {
            min-height: 16px;
            margin-top: 4px;
        }

        .prematricula-documento-button {
            min-height: 39px;
            margin-top: 20px;

            padding: 0 17px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            border: 1px solid #b91c1c;
            border-radius: 7px;
            background: #c71922;
            color: #ffffff;

            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        .prematricula-documento-button:hover {
            background: #a80f1b;
        }

        .prematricula-documento-button:disabled {
            opacity: .65;
            cursor: wait;
        }


        /* GALERÍA */

        .prematricula-documentos-adjuntos {
            margin-top: 20px;
            padding: 14px;
            border: 1px dashed #cbd5e1;
            border-radius: 9px;
            background: #ffffff;
        }

        .prematricula-documentos-adjuntos-titulo {
            margin: 0 0 13px;
            color: #334155;
            font-size: 13px;
            font-weight: 800;
        }

        .prematricula-documentos-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
        }

        .prematricula-documento-card {
            min-width: 0;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 2px 6px rgba(15, 23, 42, .05);
        }

        .prematricula-documento-preview {
            height: 105px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .prematricula-documento-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .prematricula-documento-preview-generico {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            color: #64748b;
            font-size: 10px;
        }

        .prematricula-documento-preview-generico svg {
            width: 32px;
            height: 32px;
        }

        .prematricula-documento-preview-pdf {
            color: #b91c1c;
        }

        .prematricula-documento-body {
            padding: 8px 9px 9px;
        }

        .prematricula-documento-tipo {
            display: block;
            overflow: hidden;
            color: #1e293b;
            font-size: 10px;
            line-height: 1.3;
            font-weight: 700;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .prematricula-documento-nombre {
            display: block;
            margin-top: 4px;
            overflow: hidden;
            color: #94a3b8;
            font-size: 9px;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .prematricula-documentos-vacio {
            min-height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 11px;
            text-align: center;
        }


        /* NOTA FINAL */

        .prematricula-documentos-nota {
            margin-top: 18px;
            padding: 11px 14px;
            display: flex;
            align-items: flex-start;
            gap: 9px;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            background: #eff6ff;
            color: #475569;
            font-size: 11px;
            line-height: 1.45;
        }

        .prematricula-documentos-nota svg {
            width: 17px;
            height: 17px;
            flex: 0 0 17px;
            color: #3b82f6;
        }


        /* RESPONSIVE */

        @media (max-width: 1050px) {
            .prematricula-documentos-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 850px) {
            .prematricula-documentos-pasos {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .prematricula-documento-button {
                margin-top: 0;
            }
            .prematricula-documentos-form {
                grid-template-columns: 1fr 1fr;
            }

            .prematricula-documento-button {
                grid-column: 1 / -1;
                width: 100%;
            }

            .prematricula-documentos-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 560px) {
            .prematricula-documentos-intro {
                padding: 13px;
            }

            .prematricula-documentos-form {
                grid-template-columns: 1fr;
            }

            .prematricula-documento-button {
                grid-column: auto;
            }

            .prematricula-documentos-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .prematricula-documento-preview {
                height: 100px;
            }

            .prematricula-file-button {
            min-width: 135px;
            padding: 0 10px;
        }

        .prematricula-file-button span {
            font-size: 10px;
        }

        .prematricula-file-name {
            padding: 0 10px;
            font-size: 10px;
        }
        }

        /* Celulares pequeños */
        @media (max-width: 400px) {
            .prematricula-documentos-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* =========================================================
        SELECTOR PERSONALIZADO DE ARCHIVO
        ========================================================= */

        .prematricula-file-control {
            min-height: 39px;
            display: flex;
            align-items: stretch;
            overflow: hidden;

            border: 1px solid #cbd5e1;
            border-radius: 7px;

            background: #ffffff;

            transition:
                border-color .15s ease,
                box-shadow .15s ease;
        }

        .prematricula-file-control:focus-within {
            border-color: #c71922;
            box-shadow: 0 0 0 1px rgba(199, 25, 34, .08);
        }


        /* INPUT REAL: SIGUE EXISTIENDO PARA LIVEWIRE */

        .prematricula-file-input {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;

            overflow: hidden;
            clip: rect(0, 0, 0, 0);

            white-space: nowrap;
            border: 0;
        }


        /* BOTÓN VISUAL */

        .prematricula-file-button {
            min-width: 155px;
            padding: 0 14px;

            display: inline-flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 7px;

            border-right: 1px solid #e2e8f0;

            background: #f8fafc;
            color: #475569;

            font-size: 11px;
            font-weight: 700;
            line-height: 1;

            cursor: pointer;
            white-space: nowrap;

            transition:
                background .15s ease,
                color .15s ease;
        }

        .prematricula-file-button:hover {
            background: #fff1f2;
            color: #b91c1c;
        }

        .prematricula-file-button svg {
            display: block !important;
            width: 15px !important;
            height: 15px !important;
            min-width: 15px;
            flex: 0 0 15px;
            margin: 0 !important;
        }

        .prematricula-file-button span {
            display: inline-block;
            line-height: 1;
            margin: 0;
        }


        /* NOMBRE DEL ARCHIVO */

        .prematricula-file-name {
            min-width: 0;
            flex: 1;

            padding: 0 13px;

            display: flex;
            align-items: center;

            color: #94a3b8;

            font-size: 11px;
        }

        .prematricula-file-name span {
            display: block;

            width: 100%;
            overflow: hidden;

            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .prematricula-file-name.tiene-archivo {
            color: #334155;
            font-weight: 500;
        }


        /* MENSAJE DURANTE CARGA TEMPORAL LIVEWIRE */

        .prematricula-file-loading {
            margin-top: 5px;

            color: #64748b;

            font-size: 10px;
        }


        .prematricula-documento-preview-link {
            width: 100%;
            height: 100%;

            display: flex;
            align-items: center;
            justify-content: center;

            text-decoration: none;
            cursor: pointer;
        }

        .prematricula-documento-preview-link img {
            width: 100%;
            height: 100%;
            object-fit: cover;

            transition: transform .18s ease;
        }

        .prematricula-documento-preview-link:hover img {
            transform: scale(1.035);
        }

        .prematricula-documento-preview-generico:hover {
            background: #f1f5f9;
        }


        /* =========================================================
        ACCIONES DE CADA DOCUMENTO
        ========================================================= */

        .prematricula-documento-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;

            border-top: 1px solid #e2e8f0;
        }

        .prematricula-documento-action {
            min-height: 30px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;

            border: 0;
            background: #ffffff;

            color: #475569;

            font-family: inherit;
            font-size: 9px;
            font-weight: 700;

            text-decoration: none;
            cursor: pointer;

            transition:
                background .15s ease,
                color .15s ease;
        }

        .prematricula-documento-action:first-child {
            border-right: 1px solid #e2e8f0;
        }

        .prematricula-documento-action:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .prematricula-documento-action svg {
            width: 13px;
            height: 13px;
            flex: 0 0 13px;
        }

        .prematricula-documento-action-delete {
            color: #dc2626;
        }

        .prematricula-documento-action-delete:hover {
            background: #fff1f2;
            color: #b91c1c;
        }


        /* =========================================================
        MODAL INICIAL DE DOCUMENTOS
        ========================================================= */

        .prematricula-modal-documentos {
            width: min(760px, calc(100% - 30px));
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }

        .prematricula-modal-icon-documentos {
            background: #eff6ff;
            color: #2563eb;
        }

        .prematricula-modal-documentos-info {
            margin-top: 16px;
            padding: 14px 16px;

            border: 1px solid #bfdbfe;
            border-radius: 9px;

            background: #f8fbff;

            text-align: left;
        }

        .prematricula-modal-documentos-info strong {
            display: block;

            margin-bottom: 7px;

            color: #1e3a8a;

            font-size: 13px;
            font-weight: 800;
        }

        .prematricula-modal-documentos-info p {
            margin: 0 0 7px;

            color: #475569;

            font-size: 12px;
            line-height: 1.5;
        }

        .prematricula-modal-documentos-info p:last-child {
            margin-bottom: 0;
        }

        .prematricula-modal-documentos-recordatorio {
            margin-top: 14px;
            padding: 10px 12px;

            display: flex;
            align-items: flex-start;
            gap: 8px;

            border-radius: 8px;

            background: #f8fafc;

            color: #64748b;

            font-size: 11px;
            line-height: 1.4;

            text-align: left;
        }

        .prematricula-modal-documentos-recordatorio svg {
            width: 17px;
            height: 17px;

            flex: 0 0 17px;

            color: #3b82f6;
        }

        @media (max-width: 560px) {
            .prematricula-modal-documentos
            .prematricula-modal-actions {
                flex-direction: column-reverse;
            }

            .prematricula-modal-documentos
            .prematricula-modal-button {
                width: 100%;
            }
        }

        .prematricula-modal-documentos-intro {
            margin-bottom: 12px !important;
        }

        .prematricula-modal-documentos-lista {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px 20px;

            margin-top: 12px;
        }

        .prematricula-modal-documentos-grupo {
            min-width: 0;
        }

        .prematricula-modal-documentos-grupo > span {
            display: block;
            margin-bottom: 7px;

            color: #1e3a8a;
            font-size: 13px;
            font-weight: 800;
        }

        .prematricula-modal-documentos-grupo ul {
            margin: 0;
            padding-left: 17px;
        }

        .prematricula-modal-documentos-grupo li {
            margin-bottom: 4px;

            color: #475569;
            font-size: 12px;
            line-height: 1.4;
        }

        .prematricula-modal-documentos-grupo li:last-child {
            margin-bottom: 0;
        }

        .prematricula-modal-documentos-final {
            margin: 13px 0 0 !important;
            padding-top: 10px;

            border-top: 1px solid #dbeafe;

            color: #475569;
            font-size: 11px !important;
            line-height: 1.45 !important;
            font-weight: 600;
        }

        @media (max-width: 560px) {
            .prematricula-modal-documentos-lista {
                grid-template-columns: 1fr;
                gap: 12px;
            }
        }


        .prematricula-modal-documentos-resumen {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;

            margin-top: 12px;
        }

        .prematricula-modal-documentos-resumen-item {
            min-width: 0;
        }

        .prematricula-modal-documentos-resumen-titulo {
            display: block;

            margin-bottom: 7px;

            color: #1e3a8a;
            font-size: 13px;
            font-weight: 800;
        }

        .prematricula-modal-documentos-resumen-item ul {
            margin: 0;
            padding-left: 17px;
        }

        .prematricula-modal-documentos-resumen-item li {
            margin-bottom: 5px;

            color: #475569;
            font-size: 12px;
            line-height: 1.4;
        }

        .prematricula-modal-documentos-resumen-item li:last-child {
            margin-bottom: 0;
        }

        @media (max-width: 560px) {
            .prematricula-modal-documentos-resumen {
                grid-template-columns: 1fr;
                gap: 14px;
            }
        }
    </style>


    {{-- =========================================================
        CONTENEDOR PRINCIPAL
    ========================================================== --}}
    <div class="prematricula-page">

        {{-- =====================================================
            ENCABEZADO INSTITUCIONAL
        ====================================================== --}}
        <section class="prematricula-header">

            <div class="prematricula-header-logo prematricula-header-logo-left">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="Logo Colegio Rembrandt"
                >
            </div>

            <div class="prematricula-header-content">
                <h1>Inscripción a Colegio Rembrandt</h1>

                <p>
                    Bienvenido al proceso de inscripción del colegio.
                    La información registrada permitirá inscribir al estudiante
                    como aspirante dentro del proceso de admisión.
                </p>

                
            </div>

            <div class="prematricula-header-logo prematricula-header-logo-right">
                <img
                    src="{{ asset('images/logo-rembrandt.png') }}"
                    alt="Escudo Colegio Rembrandt"
                >
            </div>

        </section>


        {{-- =====================================================
            TARJETA: DATOS DEL ESTUDIANTE
        ====================================================== --}}
        <section class="prematricula-card">
            <header class="prematricula-card-header">
                <span class="prematricula-card-number">1</span>

                <h2>Datos del estudiante</h2>
            </header>

            <div class="prematricula-card-body">
                <div class="prematricula-student-grid">

                    <div class="prematricula-student-column">
                        <div class="prematricula-field">
                            <label>
                                Nombres
                                <span class="prematricula-required">*</span>
                            </label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="nombres"
                                placeholder="Ingrese los nombres"
                            >

                            @error('nombres')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>
                                Apellidos
                                <span class="prematricula-required">*</span>
                            </label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="apellidos"
                                placeholder="Ingrese los apellidos"
                            >

                            @error('apellidos')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>
                                Tipo de documento
                                <span class="prematricula-required">*</span>
                            </label>

                            <select
                                class="prematricula-select"
                                wire:model.defer="tipo_documento"
                            >
                                <option value="">Seleccione</option>

                                @foreach($tiposDocumento as $valor => $etiqueta)
                                    <option value="{{ $valor }}">
                                        {{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>

                            @error('tipo_documento')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>
                                Documento
                                <span class="prematricula-required">*</span>
                            </label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="documento"
                                placeholder="Ingrese el documento"
                            >

                            @error('documento')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>
                                Ciudad de expedición
                                <span class="prematricula-required">*</span>
                            </label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="ciudad_expedicion"
                                placeholder="Ingrese la ciudad"
                            >

                            @error('ciudad_expedicion')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>


                    <div class="prematricula-student-column">
                        <div class="prematricula-field">
                            <label>
                                Fecha de nacimiento
                                <span class="prematricula-required">*</span>
                            </label>

                            <input
                                type="date"
                                class="prematricula-input"
                                wire:model.live="fecha_nacimiento"
                            >

                            @error('fecha_nacimiento')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>Edad</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                value="{{ $edad !== null ? $edad . ' años' : '' }}"
                                placeholder="Se calcula automáticamente"
                                readonly
                            >
                        </div>

                        <div class="prematricula-field">
                            <label>
                                Ciudad de nacimiento
                                <span class="prematricula-required">*</span>
                            </label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="ciudad_nacimiento"
                                placeholder="Ingrese la ciudad"
                            >

                            @error('ciudad_nacimiento')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>
                                Género
                                <span class="prematricula-required">*</span>
                            </label>

                            <select
                                class="prematricula-select"
                                wire:model.defer="genero"
                            >
                                <option value="">Seleccione</option>
                                <option value="femenino">Femenino</option>
                                <option value="masculino">Masculino</option>
                            </select>

                            @error('genero')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>Número de hermanos</label>

                            <input
                                type="number"
                                min="0"
                                class="prematricula-input"
                                wire:model.defer="numero_hermanos"
                                placeholder="Ingrese el número"
                            >

                            @error('numero_hermanos')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>


                    <div class="prematricula-student-column">
                        <div class="prematricula-field">
                            <label>Teléfono</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="telefono"
                                placeholder="Ingrese el teléfono"
                            >

                            @error('telefono')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>
                                Correo electrónico
                                <span class="prematricula-required">*</span>
                            </label>

                            <input
                                type="email"
                                class="prematricula-input"
                                wire:model.defer="correo"
                                placeholder="Ingrese el correo electrónico"
                            >

                            @error('correo')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>
                                Dirección
                                <span class="prematricula-required">*</span>
                            </label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="direccion"
                                placeholder="Ingrese la dirección"
                            >

                            @error('direccion')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>
                                RH
                                <span class="prematricula-required">*</span>
                            </label>

                            <select
                                class="prematricula-select"
                                wire:model.defer="rh"
                            >
                                <option value="">Seleccione</option>

                                @foreach($rhOpciones as $opcionRh)
                                    <option value="{{ $opcionRh }}">
                                        {{ $opcionRh }}
                                    </option>
                                @endforeach
                            </select>

                            @error('rh')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>
                                EPS
                                <span class="prematricula-required">*</span>
                            </label>

                            <select
                                class="prematricula-select"
                                wire:model.live="eps_id"
                            >
                                <option value="">Seleccione la EPS</option>

                                @foreach($eps as $epsId => $epsNombre)
                                    <option value="{{ $epsId }}">
                                        {{ $epsNombre }}
                                    </option>
                                @endforeach
                            </select>

                            @error('eps_id')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror

                            @if($this->esEpsOtro())
                                <div style="margin-top: 10px;">
                                    <label>
                                        ¿Cuál EPS?
                                        <span class="prematricula-required">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        class="prematricula-input"
                                        wire:model.defer="eps_otro"
                                        placeholder="Ingrese el nombre de la EPS"
                                    >

                                    @error('eps_otro')
                                        <span class="prematricula-error">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    </div>


                    <div class="prematricula-student-column">
                        <div class="prematricula-field">
                            <label>
                                Teléfono de emergencia
                                <span class="prematricula-required">*</span>
                            </label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="telefono_emergencia"
                                placeholder="Ingrese el teléfono de emergencia"
                            >

                            @error('telefono_emergencia')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>
                                Grado al que aspira
                                <span class="prematricula-required">*</span>
                            </label>

                            <select
                                class="prematricula-select"
                                wire:model.defer="grado_aspira"
                            >
                                <option value="">Seleccione el grado</option>

                                @foreach($grados as $grado)
                                    <option value="{{ $grado }}">
                                        {{ $grado }}
                                    </option>
                                @endforeach
                            </select>

                            @error('grado_aspira')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>
                                Institución donde cursó el último año
                                <span class="prematricula-required">*</span>
                            </label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="institucion_anterior"
                                placeholder="Ingrese la institución"
                            >

                            @error('institucion_anterior')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="prematricula-field">
                            <label>
                                Estado del estudiante
                                <span class="prematricula-required">*</span>
                            </label>

                            <select
                                class="prematricula-select"
                                wire:model.defer="condicion_ingreso"
                            >
                                <option value="">Seleccione el estado</option>
                                <option value="nuevo">Nuevo</option>
                                <option value="antiguo">Antiguo</option>
                                <option value="repitente">Repitente</option>
                            </select>

                            @error('condicion_ingreso')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>
        </section>


        {{-- =====================================================
            TARJETAS DE RESPONSABLES
        ====================================================== --}}
        <div class="prematricula-responsables-grid">

            {{-- TARJETA: PADRE --}}
            <section class="prematricula-card">
                <header class="prematricula-card-header">
                    <span class="prematricula-card-number">2</span>

                    <h2>Datos del padre</h2>
                </header>

                <div class="prematricula-card-body">
                    <div class="prematricula-two-columns">
                        <div class="prematricula-field">
                            <label>Nombre completo</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.live.debounce.400ms="padre_nombre"
                                placeholder="Ingrese el nombre completo"
                            >
                            @error('padre_nombre')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                        

                        <div class="prematricula-field">
                            <label>Teléfono</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.live.debounce.400ms="padre_telefono"
                                placeholder="Ingrese el teléfono"
                            >
                            @error('padre_telefono')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                        

                        <div class="prematricula-field">
                            <label>Tipo de documento</label>

                            <select
                                class="prematricula-select"
                                wire:model.live="padre_tipo_documento"
                            >
                                <option value="">Seleccione</option>

                                @foreach($tiposDocumento as $valor => $etiqueta)
                                    <option value="{{ $valor }}">
                                        {{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                            @error('padre_tipo_documento')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                        

                        <div class="prematricula-field">
                            <label>Documento</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.live.debounce.400ms="padre_documento"
                                placeholder="Ingrese el documento"
                            >
                            @error('padre_documento')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                        

                        <div class="prematricula-field">
                            <label>Lugar de trabajo</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.live.debounce.400ms="padre_lugar_trabajo"
                                placeholder="Ingrese el lugar de trabajo"
                            >
                            @error('padre_lugar_trabajo')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                        

                        <div class="prematricula-field">
                            <label>Correo electrónico</label>

                            <input
                                type="email"
                                class="prematricula-input"
                                wire:model.live.debounce.400ms="padre_correo"
                                placeholder="Ingrese el correo electrónico"
                            >
                            @error('padre_correo')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                        

                        <div class="prematricula-field prematricula-field-full">
                            <label>Dirección</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.live.debounce.400ms="padre_direccion"
                                placeholder="Ingrese la dirección"
                            >
                            @error('padre_direccion')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                        
                    </div>
                </div>
            </section>


            {{-- TARJETA: MADRE --}}
            <section class="prematricula-card">
                <header class="prematricula-card-header">
                    <span class="prematricula-card-number">3</span>

                    <h2>Datos de la madre</h2>
                </header>

                <div class="prematricula-card-body">
                    <div class="prematricula-two-columns">
                        <div class="prematricula-field">
                            <label>Nombre completo</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.live.debounce.400ms="madre_nombre"
                                placeholder="Ingrese el nombre completo"
                            >
                            @error('madre_nombre')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                       

                        <div class="prematricula-field">
                            <label>Teléfono</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.live.debounce.400ms="madre_telefono"
                                placeholder="Ingrese el teléfono"
                            >
                            @error('madre_telefono')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                        

                        <div class="prematricula-field">
                            <label>Tipo de documento</label>

                            <select
                                class="prematricula-select"
                                wire:model.live="madre_tipo_documento"
                            >
                                <option value="">Seleccione</option>

                                @foreach($tiposDocumento as $valor => $etiqueta)
                                    <option value="{{ $valor }}">
                                        {{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                            @error('madre_tipo_documento')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                       

                        <div class="prematricula-field">
                            <label>Documento</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.live.debounce.400ms="madre_documento"
                                placeholder="Ingrese el documento"
                            >
                             @error('madre_documento')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                        

                        <div class="prematricula-field">
                            <label>Lugar de trabajo</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.live.debounce.400ms="madre_lugar_trabajo"
                                placeholder="Ingrese el lugar de trabajo"
                            >
                            @error('madre_lugar_trabajo')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                        

                        <div class="prematricula-field">
                            <label>Correo electrónico</label>

                            <input
                                type="email"
                                class="prematricula-input"
                                wire:model.live.debounce.400ms="madre_correo"
                                placeholder="Ingrese el correo electrónico"
                            >
                            @error('madre_correo')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                        

                        <div class="prematricula-field prematricula-field-full">
                            <label>Dirección</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.live.debounce.400ms="madre_direccion"
                                placeholder="Ingrese la dirección"
                            >
                            @error('madre_direccion')
                                <span class="prematricula-error">{{ $message }}</span>
                            @enderror
                        </div>
                        
                    </div>
                </div>
            </section>


            {{-- TARJETA: ACUDIENTE --}}
            <section class="prematricula-card">
                <header class="prematricula-card-header">
                    <span class="prematricula-card-number">4</span>

                    <h2>Datos del acudiente</h2>
                </header>

                <div class="prematricula-card-body">
                    <div class="prematricula-two-columns">

                        <div class="prematricula-field">
                            <span class="prematricula-group-label">
                                El acudiente es:
                            </span>

                            <div class="prematricula-origin-options">
                                <button
                                    type="button"
                                    wire:click="seleccionarOrigenAcudiente('padre')"
                                    class="prematricula-origin-option
                                        {{ $acudiente_origen === 'padre' ? 'is-active' : '' }}"
                                >
                                    <span class="prematricula-origin-dot"></span>
                                    El padre
                                </button>

                                <button
                                    type="button"
                                    wire:click="seleccionarOrigenAcudiente('madre')"
                                    class="prematricula-origin-option
                                        {{ $acudiente_origen === 'madre' ? 'is-active' : '' }}"
                                >
                                    <span class="prematricula-origin-dot"></span>
                                    La madre
                                </button>

                                <button
                                    type="button"
                                    wire:click="seleccionarOrigenAcudiente('otro')"
                                    class="prematricula-origin-option
                                        {{ $acudiente_origen === 'otro' ? 'is-active' : '' }}"
                                >
                                    <span class="prematricula-origin-dot"></span>
                                    Otra persona
                                </button>
                            </div>


                            @error('acudiente_origen')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>


                        <div class="prematricula-field">
                            <label>Parentesco</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="acudiente_parentesco"
                                placeholder="Ingrese el parentesco"
                                @disabled(in_array($acudiente_origen, ['padre', 'madre'], true))
                            >

                            @error('acudiente_parentesco')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>


                        <div class="prematricula-field">
                            <label>Nombre completo</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="acudiente_nombre"
                                placeholder="Ingrese el nombre completo"
                                @disabled(in_array($acudiente_origen, ['padre', 'madre'], true))
                            >

                            @error('acudiente_nombre')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>


                        <div class="prematricula-field">
                            <label>Teléfono</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="acudiente_telefono"
                                placeholder="Ingrese el teléfono"
                                @disabled(in_array($acudiente_origen, ['padre', 'madre'], true))
                            >

                            @error('acudiente_telefono')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>


                        <div class="prematricula-field">
                            <label>Tipo de documento</label>

                            <select
                                class="prematricula-select"
                                wire:model.defer="acudiente_tipo_documento"
                                @disabled(in_array($acudiente_origen, ['padre', 'madre'], true))
                            >
                                <option value="">Seleccione</option>

                                @foreach($tiposDocumento as $valor => $etiqueta)
                                    <option value="{{ $valor }}">
                                        {{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>

                            @error('acudiente_tipo_documento')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>


                        <div class="prematricula-field">
                            <label>Documento</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="acudiente_documento"
                                placeholder="Ingrese el documento"
                                @disabled(in_array($acudiente_origen, ['padre', 'madre'], true))
                            >

                            @error('acudiente_documento')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>


                        <div class="prematricula-field">
                            <label>Lugar de trabajo</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="acudiente_lugar_trabajo"
                                placeholder="Ingrese el lugar de trabajo"
                                @disabled(in_array($acudiente_origen, ['padre', 'madre'], true))
                            >

                            @error('acudiente_lugar_trabajo')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>


                        <div class="prematricula-field">
                            <label>Correo electrónico</label>

                            <input
                                type="email"
                                class="prematricula-input"
                                wire:model.defer="acudiente_correo"
                                placeholder="Ingrese el correo electrónico"
                                @disabled(in_array($acudiente_origen, ['padre', 'madre'], true))
                            >

                            @error('acudiente_correo')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>


                        <div class="prematricula-field prematricula-field-full">
                            <label>Dirección</label>

                            <input
                                type="text"
                                class="prematricula-input"
                                wire:model.defer="acudiente_direccion"
                                placeholder="Ingrese la dirección"
                                @disabled(in_array($acudiente_origen, ['padre', 'madre'], true))
                            >

                            @error('acudiente_direccion')
                                <span class="prematricula-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                    </div>
                </div>
            </section>

        </div>



        {{-- =====================================================
            TARJETA: DOCUMENTOS PARA MATRÍCULA
        ====================================================== --}}
        <section class="prematricula-card">

            <header class="prematricula-card-header">
                <span class="prematricula-card-number">5</span>
                <h2>Documentos para matrícula</h2>
            </header>

            <div class="prematricula-card-body">

                {{-- =================================================
                    INSTRUCCIONES
                ================================================== --}}
                <div class="prematricula-documentos-intro">

                    <div class="prematricula-documentos-intro-title">
                        <x-heroicon-o-light-bulb />

                        <span>
                            ¿Cómo agregar documentos?
                        </span>
                    </div>

                    <div class="prematricula-documentos-pasos">

                        <div class="prematricula-documentos-paso">
                            <span class="prematricula-documentos-paso-numero">
                                1
                            </span>

                            <div class="prematricula-documentos-paso-contenido">
                                <strong>Paso 1</strong>

                                <p>
                                    Seleccione en la lista el tipo de documento
                                    que desea adjuntar.
                                </p>
                            </div>
                        </div>


                        <div class="prematricula-documentos-paso">
                            <span class="prematricula-documentos-paso-numero">
                                2
                            </span>

                            <div class="prematricula-documentos-paso-contenido">
                                <strong>Paso 2</strong>

                                <p>
                                    Seleccione el archivo desde su dispositivo.
                                    Puede adjuntar PDF, JPG, JPEG, PNG o WebP.
                                </p>
                            </div>
                        </div>


                        <div class="prematricula-documentos-paso">
                            <span class="prematricula-documentos-paso-numero">
                                3
                            </span>

                            <div class="prematricula-documentos-paso-contenido">
                                <strong>Paso 3</strong>

                                <p>
                                    Haga clic en <strong>Adjuntar</strong>.
                                    Repita estos pasos con cada documento
                                    que tenga disponible.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>


                {{-- =================================================
                    SELECTOR + ARCHIVO + ADJUNTAR
                ================================================== --}}
                <div class="prematricula-documentos-form">

                    <div class="prematricula-field">
                        <label>
                            Tipo de documento
                        </label>

                        <select
                            class="prematricula-select"
                            wire:model="tipoDocumentoSeleccionado"
                        >
                            <option value="">
                                Seleccione el tipo de documento
                            </option>

                            @foreach(
                                $documentosCatalogo
                                as $codigo => $configuracion
                            )
                                <option value="{{ $codigo }}">
                                    {{
                                        $configuracion['nombre']
                                        ?? $codigo
                                    }}
                                </option>
                            @endforeach
                        </select>

                        @error('tipoDocumentoSeleccionado')
                            <span class="prematricula-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>


                    <div
                        class="prematricula-field"
                        x-data="{ nombreArchivo: '' }"
                    >
                        <label>
                            Archivo
                        </label>

                        <div class="prematricula-file-control">

                            <label
                                for="archivoDocumento"
                                class="prematricula-file-button"
                            >
                                <x-heroicon-o-paper-clip />

                                <span>
                                    Seleccionar archivo
                                </span>
                            </label>

                            <div
                                class="prematricula-file-name"
                                :class="{ 'tiene-archivo': nombreArchivo }"
                            >
                                <span
                                    x-text="
                                        nombreArchivo
                                            ? nombreArchivo
                                            : 'Ningún archivo seleccionado'
                                    "
                                ></span>
                            </div>

                            <input
                                id="archivoDocumento"
                                type="file"
                                class="prematricula-file-input"
                                wire:model="archivoDocumento"
                                accept=".pdf,.jpg,.jpeg,.png,.webp"
                                x-on:change="
                                    nombreArchivo =
                                        $event.target.files.length
                                            ? $event.target.files[0].name
                                            : ''
                                "
                            >

                        </div>

                        <div
                            class="prematricula-file-loading"
                            wire:loading
                            wire:target="archivoDocumento"
                        >
                            Preparando archivo...
                        </div>

                        @error('archivoDocumento')
                            <span class="prematricula-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>


                    <button
                        type="button"
                        class="prematricula-documento-button"
                        wire:click="subirDocumento"
                        wire:loading.attr="disabled"
                        wire:target="
                            archivoDocumento,
                            subirDocumento
                        "
                    >
                        <span
                            wire:loading.remove
                            wire:target="subirDocumento"
                        >
                            Adjuntar
                        </span>

                        <span
                            wire:loading
                            wire:target="subirDocumento"
                        >
                            Guardando...
                        </span>
                    </button>

                </div>


                {{-- =================================================
                    DOCUMENTOS YA ADJUNTADOS
                ================================================== --}}
                <div class="prematricula-documentos-adjuntos">

                    <h3 class="prematricula-documentos-adjuntos-titulo">
                        Documentos adjuntos
                        ({{ count($documentosCargados) }})
                    </h3>


                    @if(count($documentosCargados))

                        <div class="prematricula-documentos-grid">

                            @foreach(
                                $documentosCargados
                                as $documento
                            )

                                <article class="prematricula-documento-card">

                                    <div class="prematricula-documento-preview">

                                        @if(
                                            str_starts_with(
                                                strtolower(
                                                    (string) (
                                                        $documento['mime_type']
                                                        ?? ''
                                                    )
                                                ),
                                                'image/'
                                            )
                                        )

                                            <a
                                                href="{{ $documento['url_visualizacion'] }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="prematricula-documento-preview-link"
                                                title="Ver documento"
                                            >
                                                <img
                                                    src="{{ $documento['url_visualizacion'] }}"
                                                    alt="{{ $documento['nombre_original'] }}"
                                                    loading="lazy"
                                                >
                                            </a>

                                        @elseif(
                                            strtolower(
                                                (string) (
                                                    $documento['mime_type']
                                                    ?? ''
                                                )
                                            ) === 'application/pdf'
                                        )

                                            <a
                                                href="{{ $documento['url_visualizacion'] }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="
                                                    prematricula-documento-preview-generico
                                                    prematricula-documento-preview-pdf
                                                    prematricula-documento-preview-link
                                                "
                                                title="Ver PDF"
                                            >
                                                <x-heroicon-o-document-text />

                                                <span>
                                                    Ver PDF
                                                </span>
                                            </a>

                                        @else

                                            <a
                                                href="{{ $documento['url_visualizacion'] }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="
                                                    prematricula-documento-preview-generico
                                                    prematricula-documento-preview-link
                                                "
                                                title="Ver documento"
                                            >
                                                <x-heroicon-o-document />

                                                <span>
                                                    Ver documento
                                                </span>
                                            </a>

                                        @endif

                                    </div>


                                    <div class="prematricula-documento-body">

                                        <span
                                            class="prematricula-documento-tipo"
                                            title="{{
                                                $documentosCatalogo[
                                                    $documento['tipo_documento']
                                                ]['nombre']
                                                ?? $documento['tipo_documento']
                                            }}"
                                        >
                                            {{
                                                $documentosCatalogo[
                                                    $documento['tipo_documento']
                                                ]['nombre']
                                                ?? $documento['tipo_documento']
                                            }}
                                        </span>


                                        <span
                                            class="prematricula-documento-nombre"
                                            title="{{ $documento['nombre_original'] }}"
                                        >
                                            {{ $documento['nombre_original'] }}
                                        </span>

                                    </div>


                                    {{-- =========================================
                                        ACCIONES DEL DOCUMENTO
                                    ========================================== --}}
                                    <div class="prematricula-documento-actions">

                                        <a
                                            href="{{ $documento['url_visualizacion'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="prematricula-documento-action"
                                            title="Ver documento"
                                        >
                                            <x-heroicon-o-eye />

                                            <span>
                                                Ver
                                            </span>
                                        </a>


                                        <button
                                            type="button"
                                            class="
                                                prematricula-documento-action
                                                prematricula-documento-action-delete
                                            "
                                            wire:click="
                                                quitarDocumento(
                                                    {{ $documento['id'] }}
                                                )
                                            "
                                            wire:confirm="
                                                ¿Está seguro de quitar este documento?
                                            "
                                            wire:loading.attr="disabled"
                                            wire:target="
                                                quitarDocumento(
                                                    {{ $documento['id'] }}
                                                )
                                            "
                                            title="Quitar documento"
                                        >
                                            <x-heroicon-o-trash />

                                            <span>
                                                Quitar
                                            </span>
                                        </button>

                                    </div>

                                </article>

                            @endforeach

                        </div>

                    @else

                        <div class="prematricula-documentos-vacio">
                            Aún no ha adjuntado documentos.
                        </div>

                    @endif

                        

                </div>


                {{-- =================================================
                    ACLARACIÓN
                ================================================== --}}
                <div class="prematricula-documentos-nota">

                    <x-heroicon-o-information-circle />

                    <span>
                        Adjunte los documentos que tenga disponibles.
                    </span>

                </div>

            </div>

        </section>





        {{-- =====================================================
            ERROR: RESPONSABLE REQUERIDO
        ====================================================== --}}
        @error('responsable')
            <div class="prematricula-responsable-error">
                {{ $message }}
            </div>
        @enderror



        {{-- =====================================================
            AVISO DE DILIGENCIAMIENTO
        ====================================================== --}}
        <section class="prematricula-notice">
            <span class="prematricula-notice-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.667 1.73-3L13.73 4c-.77-1.333-2.69-1.333-3.46 0L3.34 16c-.77 1.333.19 3 1.73 3z"
                    />
                </svg>
            </span>

            <div class="prematricula-aviso-texto">
                <strong>Aviso:</strong>

                <p>
                    Complete toda la información solicitada del estudiante y de al menos uno de los responsables (padre, madre o acudiente).
                </p>

                <p style="margin-top: 10px;">
                    Si durante el proceso presenta alguna inquietud o requiere orientación, comuníquese con el equipo de admisiones al correo:
                    <br>
                    <strong>admisiones@colegiorembrandt.edu.co</strong>
                </p>
            </div>
            
        </section>


        {{-- =====================================================
            TRATAMIENTO DE DATOS
        ====================================================== --}}


        {{-- =====================================================
            ACCIÓN FINAL
        ====================================================== --}}
        <section class="prematricula-actions">
            <button
                type="button"
                class="prematricula-submit"
                wire:click="abrirConfirmacion"
            >
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                    />
                </svg>

                Guardar y enviar formulario
            </button>

            <div class="prematricula-privacy">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 11c1.657 0 3-1.343 3-3V7a3 3 0 10-6 0v1c0 1.657 1.343 3 3 3zm-7 0h14v10H5V11z"
                    />
                </svg>

                <span>
                    Tu información está protegida y será tratada de forma confidencial.
                </span>

                <a
                    href="{{ asset('documentos/politica-tratamiento-datos.pdf') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Ver política de tratamiento de datos

                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M14 3h7m0 0v7m0-7L10 14M5 7v14h14v-5"
                        />
                    </svg>
                </a>
            </div>
        </section>

    











            {{-- =========================================================
                MODAL INICIAL - DOCUMENTOS
            ========================================================== --}}
            @if($mostrarModalDocumentosInicial)

                <div class="prematricula-modal-backdrop">

                    <section
                        class="
                            prematricula-modal
                            prematricula-modal-documentos
                        "
                    >

                        <div class="prematricula-modal-body">

                            <span
                                class="
                                    prematricula-modal-icon
                                    prematricula-modal-icon-documentos
                                "
                            >
                                <x-heroicon-o-document-check />
                            </span>

                            <h3>
                                Antes de comenzar
                            </h3>

                            <p>
                                Para facilitar el proceso de inscripción,
                                tenga a mano los documentos que tenga disponibles.
                            </p>

                            <div class="prematricula-modal-documentos-info">

                                <strong>
                                    Documentos que puede tener a mano
                                </strong>

                                <p class="prematricula-modal-documentos-intro">
                                    No es necesario contar con todos para continuar.
                                    Adjunte únicamente los documentos que tenga disponibles.
                                </p>

                                <div class="prematricula-modal-documentos-resumen">

                                    <div class="prematricula-modal-documentos-resumen-item">
                                        <span class="prematricula-modal-documentos-resumen-titulo">
                                            Del estudiante
                                        </span>

                                        <ul>
                                            <li>
                                                -Fotocopia del registro civil de nacimiento legible.
                                            </li>

                                            <li>
                                               -Fotocopia de la tarjeta de identidad para estudiantes
                                                de 7 años cumplidos en adelante.
                                            </li>

                                            <li>
                                                -Certificado médico del estudiante no mayor a 30 días.
                                            </li>

                                            <li>
                                                -Fotocopia del carnet de vacunas para grados
                                                pre-jardín a primero.
                                            </li>

                                            <li>
                                                -Certificado de afiliación del niño a la EPS y/o Sisbén.
                                            </li>

                                            <li>
                                                -Constancia de retiro del estudiante del SIMAT,
                                                expedida por el colegio de procedencia.
                                            </li>
                                        </ul>
                                    </div>


                                    <div class="prematricula-modal-documentos-resumen-item">
                                        <span class="prematricula-modal-documentos-resumen-titulo">
                                            De padres, acudiente y codeudor
                                        </span>

                                        <ul>
                                            <li>
                                                -Copia del documento de identidad de los padres
                                                de familia.
                                            </li>

                                            <li>
                                                -Copia del documento de identidad del acudiente.
                                            </li>

                                            <li>
                                                -Copia del documento de identidad del codeudor,
                                                cuando aplique.
                                            </li>

                                            <li>
                                                -Certificado laboral de los padres de familia
                                                no mayor a 30 días.
                                            </li>

                                            <li>
                                                -Certificado laboral del codeudor no mayor a
                                                30 días, si aplica.
                                            </li>

                                            <li>
                                                -Copia del recibo público del domicilio del padre
                                                de familia y codeudor, si aplica.
                                            </li>
                                        </ul>
                                    </div>

                                </div>

                                <div class="prematricula-modal-documentos-final">
                                    Si algún documento no aplica o aún no lo tiene,
                                    puede continuar con el formulario y entregarlo
                                    posteriormente al colegio.
                                </div>

                            </div>

                            <div class="prematricula-modal-documentos-recordatorio">

                                <x-heroicon-o-information-circle />

                                <span>
                                    Puede adjuntar archivos PDF, JPG, JPEG,
                                    PNG o WebP.
                                </span>

                            </div>

                        </div>


                        <footer class="prematricula-modal-actions">

                            <button
                                type="button"
                                class="
                                    prematricula-modal-button
                                    prematricula-modal-cancel
                                "
                                wire:click="cerrarSesionDesdeAvisoDocumentos"
                            >
                                Aún no estoy listo. Cerrar sesión
                            </button>


                            <button
                                type="button"
                                class="
                                    prematricula-modal-button
                                    prematricula-modal-confirm
                                "
                                wire:click="continuarDesdeAvisoDocumentos"
                            >
                                Continuar con el formulario
                            </button>

                        </footer>

                    </section>

                </div>

            @endif













            {{-- =========================================================
                MODAL DE CONFIRMACIÓN
            ========================================================== --}}
            @if($mostrarModalConfirmacion)
                <div
                    class="prematricula-modal-backdrop"
                    wire:click.self="cancelarEnvio"
                >
                    <section class="prematricula-modal">
                        <div class="prematricula-modal-body">
                            <span class="prematricula-modal-icon">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                    />
                                </svg>
                            </span>

                            <h3>¿Está seguro de enviar el formulario?</h3>

                            <p>
                                Verifique que la información registrada sea correcta antes
                                de continuar.
                            </p>

                            <div class="prematricula-modal-warning">
                                <p>
                                    Una vez enviado el formulario, no podrá modificar la información
                                    desde este portal.
                                </p>

                                <p>
                                    Si posteriormente requiere alguna corrección, comuníquese con el
                                    Colegio Rembrandt.
                                </p>
                            </div>
                            <div class="prematricula-modal-process">
                                <p>
                                    El Colegio Rembrandt verificará la información registrada y se
                                    comunicará con usted para continuar el proceso de admisión y matrícula.
                                </p>
                            </div>
                        </div>

                        <footer class="prematricula-modal-actions">
                            <button
                                type="button"
                                class="prematricula-modal-button prematricula-modal-cancel"
                                wire:click="cancelarEnvio"
                            >
                                Cancelar
                            </button>

                            <button
                                type="button"
                                class="prematricula-modal-button prematricula-modal-confirm"
                                wire:click="confirmarEnvio"
                            >
                                Sí, enviar formulario y cerrar sesión
                            </button>
                        </footer>
                    </section>
                </div>
            @endif

        </div>




</x-filament-panels::page>