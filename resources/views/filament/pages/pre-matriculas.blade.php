<x-filament-panels::page>
    <style>
        .fi-main {
            max-width: none !important;
        }

        .pre-page,
        .pre-page * {
            box-sizing: border-box;
        }

        .pre-page {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 14px;
            color: #1e293b;
        }

        /* =========================================================
           ENCABEZADO
        ========================================================= */

        .pre-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .pre-header h1 {
            margin: 0;
            color: #111827;
            font-size: 30px;
            line-height: 1.1;
            font-weight: 800;
        }

        .pre-header p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .pre-header-actions {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .pre-export-button {
            min-height: 38px;
            padding: 0 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border:1px solid #9d1f20;
            border-radius: 7px;
            background:#9d1f20;
            color:white;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
        }

        .pre-export-button:hover {
            background:#861818;
        }

        .pre-export-button svg {
            width: 16px;
            height: 16px;
        }

        /* =========================================================
           TARJETAS DE RESUMEN
        ========================================================= */

        .pre-summary-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 10px;
        }

        .pre-summary-card {
            min-width: 0;
            min-height: 82px;
            padding: 13px 14px;
            display: flex;
            align-items: center;
            gap: 11px;
            border: 1px solid #e1e7ee;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .035);
        }

        .pre-summary-icon {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            display: grid;
            place-items: center;
            border-radius: 9px;
        }

        .pre-summary-icon svg {
            width: 19px;
            height: 19px;
        }

        .pre-summary-icon-red {
            background: #fee2e2;
            color: #dc2626;
        }

        .pre-summary-icon-green {
            background: #dcfce7;
            color: #16a34a;
        }

        .pre-summary-icon-orange {
            background: #ffedd5;
            color: #ea580c;
        }

        .pre-summary-icon-gray {
            background: #eef2f6;
            color: #64748b;
        }

        .pre-summary-icon-blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .pre-summary-icon-pink{
            background:#FCE7F3;
            color:#DB2777;
        }

        .pre-summary-label {
            display: block;
            margin-bottom: 3px;
            color: #64748b;
            font-size: 12px;
        }

        .pre-summary-value {
            display: block;
            color: #0f172a;
            font-size: 22px;
            line-height: 1;
            font-weight: 800;
        }

        .pre-secondary-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .pre-secondary-card {
            min-height: 67px;
            padding: 11px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid #e1e7ee;
            border-radius: 9px;
            background: #ffffff;
        }

        .pre-secondary-label {
            color: #64748b;
            font-size: 12px;
        }

        .pre-secondary-value {
            display: block;
            margin-top: 4px;
            color: #1e293b;
            font-size: 15px;
            font-weight: 800;
        }

        .pre-secondary-total {
            color: #b91c1c;
            font-size: 22px;
            font-weight: 800;
        }

        /* =========================================================
           FILTROS
        ========================================================= */

        .pre-card {
            border: 1px solid #dde4ec;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
        }

        .pre-filters {
            padding: 12px;
            display: grid;
            grid-template-columns:
                minmax(220px, 1.5fr)
                minmax(150px, .8fr)
                minmax(160px, .9fr)
                minmax(135px, .7fr)
                minmax(135px, .7fr)
                auto;
            gap: 10px;
            align-items: end;
        }

        .pre-field label {
            display: block;
            margin-bottom: 5px;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
        }

        .pre-input,
        .pre-select {
            width: 100%;
            height: 38px;
            padding: 0 10px;
            border: 1px solid #d6dee7;
            border-radius: 6px;
            background: #ffffff;
            color: #1e293b;
            font-size: 13px;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;

            background-image:
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 8 9 11 12 8'/%3E%3C/svg%3E");

            background-repeat: no-repeat;

            background-position:
            right 12px center;

            padding-right:40px;
        }

        .pre-input:focus,
        .pre-select:focus {
            border-color: #b91c1c;
            box-shadow: 0 0 0 3px rgba(185, 28, 28, .07);
        }
        

        .pre-clear-button {
            height: 38px;
            padding: 0 13px;
            border:1px solid #f3c3c3;
            border-radius: 6px;
            background:#fff4f4;
            color:#b91c1c;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .pre-clear-button:hover{
            background:#fdecec;
        }

        /* =========================================================
           LISTADO
        ========================================================= */

        .pre-table-wrap {
            height: 520px;
            overflow: auto;
        }

        .pre-table thead {
            position: sticky;
            top: 0;
            z-index: 5;
        }

        .pre-table th {
            background: #f8fafc;
        }

        .pre-table {
            width: 100%;
            border-collapse: collapse;
        }

        .pre-table th {
            padding: 10px 12px;
            border-bottom: 1px solid #dfe5ec;
            background: #f8fafc;
            color: #536175;
            font-size: 12px;
            font-weight: 600;
            text-align: left;
            white-space: nowrap;
        }

        .pre-table td {
            padding: 14px 13px;
            border-bottom: 1px solid #edf1f5;
            color: #334155;
            font-size: 13px;
            vertical-align: middle;
        }

        .pre-table tbody tr {
            cursor: pointer;
            transition: background .14s ease;
        }

        .pre-table tbody tr:hover {
            background: #fffafa;
        }

        .pre-table tbody tr.is-selected {
            background: #fff1f2;
        }

        .pre-student-name {
            display: block;
            color: #1e293b;
            font-weight: 800;
        }

        .pre-student-document {
            display: block;
            margin-top: 2px;
            color: #94a3b8;
            font-size: 12px;
        }

        .pre-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 75px;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .pre-status-completado {
            border: 1px solid #bbebcf;
            background: #e8f8ef;
            color: #15803d;
        }

        .pre-status-pendiente {
            border: 1px solid #fed7aa;
            background: #fff7ed;
            color: #c25d08;
        }

        .pre-status-vencido{
            background:#fdecec;
            border:1px solid #dfa0a0;
            color:#c62828;
        }

        .pre-view-button {
            width: 30px;
            height: 30px;
            display: inline-grid;
            place-items: center;
            border: 1px solid #dbe2ea;
            border-radius: 6px;
            background: #ffffff;
            color: #475569;
            cursor: pointer;
        }

        .pre-view-button svg {
            width: 15px;
            height: 15px;
        }


        .pre-secondary-content {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 11px;
        }
        /* =========================================================
           AVISO
        ========================================================= */

        .pre-notice {
            padding: 11px 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border: 1px solid #dbe3eb;
            border-radius: 8px;
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            line-height: 1.45;
        }

        .pre-notice svg {
            width: 17px;
            height: 17px;
            flex: 0 0 17px;
            color: #475569;
        }



        /* =========================================================
        MODAL DE DETALLE EDITABLE
        ========================================================= */

        .pre-detail-backdrop {
            position: fixed;
            inset: 0;
            z-index: 100;
            padding: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, .58);
            backdrop-filter: blur(3px);
        }

        .pre-detail-modal {
            width: min(1180px, 100%);
            max-height: calc(100vh - 56px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #dbe2ea;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .25);
        }

        .pre-detail-header {
            padding: 18px 22px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .pre-detail-heading {
            min-width: 0;
        }

        .pre-detail-heading-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 9px;
        }

        .pre-detail-heading h2 {
            margin: 0;
            color: #0f172a;
            font-size: 22px;
            line-height: 1.2;
            font-weight: 800;
        }

        .pre-detail-number {
            margin-top: 5px;
            color: #64748b;
            font-size: 12px;
        }

        .pre-detail-close {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            display: grid;
            place-items: center;
            border: 1px solid #dbe2ea;
            border-radius: 8px;
            background: #ffffff;
            color: #64748b;
            cursor: pointer;
        }

        .pre-detail-close:hover {
            border-color: #fecaca;
            background: #fff1f2;
            color: #b91c1c;
        }

        .pre-detail-close svg {
            width: 18px;
            height: 18px;
        }

        .pre-detail-body {
            min-height: 0;
            padding: 18px 22px 22px;
            overflow-y: auto;
            background: #f8fafc;
        }

        .pre-detail-section {
            margin-bottom: 15px;
            overflow: hidden;
            border: 1px solid #dde4ec;
            border-radius: 10px;
            background: #ffffff;
        }

        .pre-detail-section:last-child {
            margin-bottom: 0;
        }

        .pre-detail-section-header {
            min-height: 45px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 9px;
            border-bottom: 1px solid #e8edf2;
            background: #fffafa;
        }

        .pre-detail-section-number {
            width: 25px;
            height: 25px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 12px;
            font-weight: 800;
        }

        .pre-detail-section-header h3 {
            margin: 0;
            color: #991b1b;
            font-size: 14px;
            font-weight: 800;
        }

        .pre-detail-fields {
            padding: 14px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .pre-detail-fields-three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .pre-detail-field {
            min-width: 0;
        }

        .pre-detail-field-full {
            grid-column: 1 / -1;
        }

        .pre-detail-field-span-2 {
            grid-column: span 2;
        }

        .pre-detail-field label {
            display: block;
            margin-bottom: 5px;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
        }

        .pre-detail-input,
        .pre-detail-select {
            width: 100%;
            height: 37px;
            padding: 0 40px 0 10px;

            border: 1px solid #d6dee7;
            border-radius: 6px;

            background-color: #ffffff;
            color: #1e293b;

            font-size: 12px;
            outline: none;
        }

        .pre-detail-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;

            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 8 9 11 12 8'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }

        .pre-detail-input:focus,
        .pre-detail-select:focus {
            border-color: #b91c1c;
            box-shadow: 0 0 0 3px rgba(185, 28, 28, .08);
        }

        .pre-detail-input[readonly] {
            background: #f1f5f9;
            color: #64748b;
        }

        .pre-detail-footer {
            padding: 14px 22px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 9px;
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .pre-detail-button {
            min-height: 38px;
            padding: 0 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .pre-detail-button svg {
            width: 16px;
            height: 16px;
        }

        .pre-detail-button-cancel {
            border: 1px solid #d7dee7;
            background: #ffffff;
            color: #475569;
        }

        .pre-detail-button-cancel:hover {
            background: #f8fafc;
        }

        .pre-detail-button-save {
            border: 1px solid #991b1b;
            background: #991b1b;
            color: #ffffff;
        }

        .pre-detail-button-save:hover {
            background: #7f1d1d;
        }




        .pre-history{
            display:flex;
            flex-direction:column;
            gap:14px;
        }

        .pre-history-item{
            border:1px solid #e2e8f0;
            border-radius:10px;
            padding:14px 16px;
            background:#fff;
        }

        .pre-history-top{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            margin-bottom:10px;
        }

        .pre-history-action{
            display:block;
            font-size:13px;
            font-weight:700;
            color:#82211d;
        }

        .pre-history-user{
            margin-top:3px;
            font-size:12px;
            color:#64748b;
        }

        .pre-history-date{
            font-size:12px;
            color:#64748b;
        }

        .pre-history-change{
            display:grid;
            grid-template-columns:repeat(3,1fr);
            gap:16px;
        }

        .pre-history-label{
            display:block;
            margin-bottom:4px;
            font-size:11px;
            color:#94a3b8;
            text-transform:uppercase;
            letter-spacing:.04em;
        }

        .pre-history-description{
            font-size:13px;
            color:#334155;
        }

        .pre-history-empty{
            text-align:center;
            padding:20px;
            color:#64748b;
        }

        .pre-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pre-delete-button {
            width: 30px;
            height: 30px;
            display: inline-grid;
            place-items: center;
            border: 1px solid #fecaca;
            border-radius: 6px;
            background: #ffffff;
            color: #dc2626;
            cursor: pointer;
        }

        .pre-delete-button:hover {
            border-color: #fca5a5;
            background: #fff1f2;
            color: #b91c1c;
        }

        .pre-delete-button svg {
            width: 15px;
            height: 15px;
        }




        @media (max-width: 1050px) {
            .pre-detail-fields,
            .pre-detail-fields-three {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .pre-detail-backdrop {
                padding: 10px;
            }

            .pre-detail-modal {
                max-height: calc(100vh - 20px);
            }

            .pre-detail-fields,
            .pre-detail-fields-three {
                grid-template-columns: 1fr;
            }

            .pre-detail-field-span-2 {
                grid-column: auto;
            }
        }




        @media (max-width: 1300px) {
            .pre-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .pre-filters {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-height: 800px) {
            .pre-table-wrap {
                height: 330px;
            }
        }
    </style>

    <div class="pre-page">

        {{-- ENCABEZADO --}}
        <header class="pre-header">
            <div>
                <h1>Pre-matrículas</h1>

                
            </div>

            <div class="pre-header-actions">
                <button
                    type="button"
                    class="pre-export-button"
                    wire:click="exportarEstudiantes"
                    wire:loading.attr="disabled"
                    wire:target="exportarEstudiantes"
                >
                    <x-heroicon-o-arrow-down-tray />

                    <span wire:loading.remove wire:target="exportarEstudiantes">
                        Exportar estudiantes
                    </span>

                    <span wire:loading wire:target="exportarEstudiantes">
                        Generando...
                    </span>
                </button>

                <button
                    type="button"
                    class="pre-export-button"
                    wire:click="exportarAcudientes"
                    wire:loading.attr="disabled"
                    wire:target="exportarAcudientes"
                >
                    <x-heroicon-o-arrow-down-tray />

                    <span wire:loading.remove wire:target="exportarAcudientes">
                        Exportar acudientes
                    </span>

                    <span wire:loading wire:target="exportarAcudientes">
                        Generando...
                    </span>
                </button>
            </div>
        </header>

        {{-- PRIMERA FILA DE RESUMEN --}}
        <section class="pre-summary-grid">
            @php
                $tarjetas = [
                    [
                        'label' => 'Total formularios',
                        'value' => $resumen['total'],
                        'class' => 'red',
                        'icon' => 'heroicon-o-user-group',
                    ],
                    [
                        'label' => 'Completadas',
                        'value' => $resumen['completadas'],
                        'class' => 'green',
                        'icon' => 'heroicon-o-check-circle',
                    ],
                    [
                        'label' => 'Pendientes',
                        'value' => $resumen['pendientes'],
                        'class' => 'orange',
                        'icon' => 'heroicon-o-clock',
                    ],
                    [
                        'label' => 'Vencidas',
                        'value' => $resumen['vencidas'],
                        'class' => 'red',
                        'icon' => 'heroicon-o-exclamation-circle',
                    ],
                    [
                        'label' => 'Hombres',
                        'value' => $resumen['hombres'],
                        'class' => 'blue',
                        'icon' => 'heroicon-o-user',
                    ],
                    [
                        'label' => 'Mujeres',
                        'value' => $resumen['mujeres'],
                        'class' => 'pink',
                        'icon' => 'heroicon-o-user',
                    ],
                ];
            @endphp

            @foreach($tarjetas as $tarjeta)
                <article class="pre-summary-card">
                    <span
                        class="pre-summary-icon
                            pre-summary-icon-{{ $tarjeta['class'] }}"
                    >
                        <x-dynamic-component
                            :component="$tarjeta['icon']"
                        />
                    </span>

                    <div>
                        <span class="pre-summary-label">
                            {{ $tarjeta['label'] }}
                        </span>

                        <strong class="pre-summary-value">
                            {{ $tarjeta['value'] }}
                        </strong>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- SEGUNDA FILA DE RESUMEN --}}
        <section class="pre-secondary-summary">
            <article class="pre-secondary-card">
                <div class="pre-secondary-content">
                    <span
                        class="pre-summary-icon
                            pre-summary-icon-orange"
                    >
                        <x-heroicon-o-academic-cap />
                    </span>

                    <div>
                        <span class="pre-secondary-label">
                            Grado más solicitado
                        </span>

                        <strong class="pre-secondary-value">
                            {{ $resumen['grado_mas_solicitado']['grado'] }}
                        </strong>
                    </div>
                </div>

                <span class="pre-secondary-total">
                    {{ $resumen['grado_mas_solicitado']['total'] }}
                </span>
            </article>

            <article class="pre-secondary-card">
                <div class="pre-secondary-content">
                    <span
                        class="pre-summary-icon
                            pre-summary-icon-blue"
                    >
                        <x-heroicon-o-academic-cap />
                    </span>

                    <div>
                        <span class="pre-secondary-label">
                            Grado menos solicitado
                        </span>

                        <strong class="pre-secondary-value">
                            {{ $resumen['grado_menos_solicitado']['grado'] }}
                        </strong>
                    </div>
                </div>

                <span class="pre-secondary-total">
                    {{ $resumen['grado_menos_solicitado']['total'] }}
                </span>
            </article>

            <article class="pre-secondary-card">
                <div class="pre-secondary-content">
                    <span
                        class="pre-summary-icon
                            pre-summary-icon-pink"
                    >
                        <x-heroicon-o-calendar-days />
                    </span>

                    <div>
                        <span class="pre-secondary-label">
                            Completadas hoy
                        </span>

                        <strong class="pre-secondary-value">
                            Formularios recibidos
                        </strong>
                    </div>
                </div>

                <span class="pre-secondary-total">
                    {{ $resumen['completadas_hoy'] }}
                </span>
            </article>

            <article class="pre-secondary-card">
                <div class="pre-secondary-content">
                    <span
                        class="pre-summary-icon
                            pre-summary-icon-green"
                    >
                        <x-heroicon-o-calendar-days />
                    </span>

                    <div>
                        <span class="pre-secondary-label">
                            Completadas esta semana
                        </span>

                        <strong class="pre-secondary-value">
                            Formularios recibidos
                        </strong>
                    </div>
                </div>

                <span class="pre-secondary-total">
                    {{ $resumen['completadas_semana'] }}
                </span>
            </article>
        </section>

        {{-- FILTROS --}}
        <section class="pre-card pre-filters">
            <div class="pre-field">
                <label>Buscar formulario o estudiante</label>

                <input
                    type="text"
                    class="pre-input"
                    wire:model.live.debounce.400ms="buscar"
                    placeholder="Nombre, documento o número de formulario"
                >
            </div>

            <div class="pre-field">
                <label>Estado</label>

                <select
                    class="pre-select"
                    wire:model.live="filtroEstado"
                >
                    <option value="">Todos</option>
                    <option value="completado">Completadas</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="vencido">Vencidas</option>
                </select>
            </div>

            <div class="pre-field">
                <label>Grado al que aspira</label>

                <select
                    class="pre-select"
                    wire:model.live="filtroGrado"
                >
                    <option value="">Todos los grados</option>

                    @foreach($grados as $grado)
                        <option value="{{ mb_strtoupper(trim($grado), 'UTF-8') }}">
                            {{ $grado }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pre-field">
                <label>Desde</label>

                <input
                    type="date"
                    class="pre-input"
                    wire:model.live="fechaDesde"
                >
            </div>

            <div class="pre-field">
                <label>Hasta</label>

                <input
                    type="date"
                    class="pre-input"
                    wire:model.live="fechaHasta"
                >
            </div>

            <button
                type="button"
                class="pre-clear-button"
                wire:click="limpiarFiltros"
            >
                Limpiar filtros
            </button>
        </section>

        {{-- LISTADO --}}
        <section class="pre-card pre-table-wrap">
            <table class="pre-table">
                <thead>
                    <tr>
                        <th>Formulario</th>
                        <th>Estudiante</th>
                        <th>Grado</th>
                        <th>Género</th>
                        <th>Estado</th>
                        <th>Fecha de envío</th>
                        <th>Acudiente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($formularios as $formulario)
                        <tr
                            wire:key="pre-matricula-{{ $formulario['id'] }}"
                            wire:click="
                                seleccionarPreMatricula(
                                    {{ $formulario['id'] }}
                                )
                            "
                            class="{{
                                $preMatriculaSeleccionadaId === $formulario['id']
                                    ? 'is-selected'
                                    : ''
                            }}"
                        >
                            <td>
                                {{ $formulario['numero_formulario'] }}
                            </td>

                            <td>
                                <span class="pre-student-name">
                                    {{ $formulario['estudiante'] }}
                                </span>

                                <span class="pre-student-document">
                                    {{ $formulario['documento'] }}
                                </span>
                            </td>

                            <td>
                                {{ $formulario['grado'] }}
                            </td>

                            <td>
                                {{ $formulario['genero'] }}
                            </td>

                            <td>
                                <span
                                    class="pre-status
                                        pre-status-{{ $formulario['estado'] }}"
                                >
                                    {{ ucfirst($formulario['estado']) }}
                                </span>
                            </td>

                            <td>
                                {{ $formulario['fecha_envio'] ?? 'Sin enviar' }}
                            </td>

                            <td>
                                {{ $formulario['acudiente'] }}
                            </td>

                            <td>
                                <div class="pre-actions">

                                    {{-- EDITAR --}}
                                    <button
                                        type="button"
                                        class="pre-view-button"
                                        title="Editar formulario"
                                        aria-label="Editar formulario"
                                        wire:click.stop="
                                            seleccionarPreMatricula(
                                                {{ $formulario['id'] }}
                                            )
                                        "
                                    >
                                        <x-heroicon-o-pencil-square />
                                    </button>

                                    {{-- ELIMINAR - SOLO SUPERADMIN --}}
                                    @if(auth()->user()?->hasRole('superadmin'))
                                        <button
                                            type="button"
                                            class="pre-delete-button"
                                            title="Eliminar pre-matrícula"
                                            aria-label="Eliminar pre-matrícula"
                                            wire:click.stop="
                                                eliminarPreMatricula(
                                                    {{ $formulario['id'] }}
                                                )
                                            "
                                            wire:confirm="¿Está seguro de eliminar la pre-matrícula {{ $formulario['numero_formulario'] }}? Esta acción eliminará también su historial y no se puede deshacer."
                                        >
                                            <x-heroicon-o-trash />
                                        </button>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        
    </div>

















    {{-- =========================================================
        MODAL DE DETALLE EDITABLE
    ========================================================= --}}
    @if($mostrarModalDetalle && !empty($formularioEdicion))
        <div
            class="pre-detail-backdrop"
            wire:click.self="cerrarModalDetalle"
        >
            <section
                class="pre-detail-modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="pre-detail-title"
            >
                <header class="pre-detail-header">
                    <div class="pre-detail-heading">
                        <div class="pre-detail-heading-row">
                            <h2 id="pre-detail-title">
                                Editar pre-matrícula
                            </h2>

                            <span
                                class="pre-status
                                    pre-status-{{ $formularioEdicion['estado'] }}"
                            >
                                {{ ucfirst($formularioEdicion['estado']) }}
                            </span>
                        </div>

                        <div class="pre-detail-number">
                            {{ $formularioEdicion['numero_formulario'] }}

                            @if(!empty($formularioEdicion['fecha_envio']))
                                · Enviado:
                                {{ $formularioEdicion['fecha_envio'] }}
                            @endif
                        </div>
                    </div>

                    <button
                        type="button"
                        class="pre-detail-close"
                        wire:click="cerrarModalDetalle"
                        aria-label="Cerrar modal"
                        title="Cerrar"
                    >
                        <x-heroicon-o-x-mark />
                    </button>
                </header>

                <div class="pre-detail-body">

                    {{-- DATOS DEL ESTUDIANTE --}}
                    <section class="pre-detail-section">
                        <header class="pre-detail-section-header">
                            <span class="pre-detail-section-number">1</span>
                            <h3>Datos del estudiante</h3>
                        </header>

                        <div class="pre-detail-fields">
                            <div class="pre-detail-field">
                                <label>Nombres</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.nombres"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Apellidos</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.apellidos"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Tipo de documento</label>
                                <select
                                    class="pre-detail-select"
                                    wire:model.defer="formularioEdicion.tipo_documento"
                                >
                                    <option value="">Seleccione</option>
                                    <option value="RC">Registro civil</option>
                                    <option value="TI">Tarjeta de identidad</option>
                                    <option value="CC">Cédula de ciudadanía</option>
                                    <option value="CE">Cédula de extranjería</option>
                                    <option value="PA">Pasaporte</option>
                                </select>
                            </div>

                            <div class="pre-detail-field">
                                <label>Documento</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.documento"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Ciudad de expedición</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.ciudad_expedicion"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Fecha de nacimiento</label>
                                <input
                                    type="date"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.fecha_nacimiento"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Edad</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    value="{{ $formularioEdicion['edad'] }} años"
                                    readonly
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Ciudad de nacimiento</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.ciudad_nacimiento"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Género</label>
                                <select
                                    class="pre-detail-select"
                                    wire:model.defer="formularioEdicion.genero"
                                >
                                    <option value="">Seleccione</option>
                                    <option value="masculino">Masculino</option>
                                    <option value="femenino">Femenino</option>
                                </select>
                            </div>

                            <div class="pre-detail-field">
                                <label>Número de hermanos</label>
                                <input
                                    type="number"
                                    min="0"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.numero_hermanos"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Teléfono</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.telefono"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Correo electrónico</label>
                                <input
                                    type="email"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.correo"
                                >
                            </div>

                            <div class="pre-detail-field pre-detail-field-span-2">
                                <label>Dirección</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.direccion"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>RH</label>
                                <select
                                    class="pre-detail-select"
                                    wire:model.defer="formularioEdicion.rh"
                                >
                                    <option value="">Seleccione</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                </select>
                            </div>

                            <div class="pre-detail-field">
                                <label>EPS</label>

                                <select
                                    class="pre-detail-select"
                                    wire:model.live="formularioEdicion.eps_id"
                                >
                                    <option value="">Seleccione</option>

                                    @foreach($eps as $epsId => $epsNombre)
                                        <option value="{{ $epsId }}">
                                            {{ $epsNombre }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('formularioEdicion.eps_id')
                                    <span class="prematricula-error">
                                        {{ $message }}
                                    </span>
                                @enderror
                                @if(
                                    !empty($formularioEdicion['eps_id'])
                                    && isset($eps[$formularioEdicion['eps_id']])
                                    && mb_strtolower(
                                        trim($eps[$formularioEdicion['eps_id']]),
                                        'UTF-8'
                                    ) === 'otro'
                                )
                                    <div class="pre-detail-field">
                                        <label>¿Cuál EPS?</label>

                                        <input
                                            type="text"
                                            class="pre-detail-input"
                                            wire:model.defer="formularioEdicion.eps_otro"
                                        >

                                        @error('formularioEdicion.eps_otro')
                                            <span class="prematricula-error">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            <div class="pre-detail-field">
                                <label>Teléfono de emergencia</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.telefono_emergencia"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Grado al que aspira</label>
                                <select
                                    class="pre-detail-select"
                                    wire:model.defer="formularioEdicion.grado"
                                >
                                    @foreach($grados as $grado)
                                        <option value="{{ mb_strtoupper(trim($grado), 'UTF-8') }}">
                                            {{ $grado }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="pre-detail-field pre-detail-field-span-2">
                                <label>Institución anterior</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.institucion_anterior"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Condición de ingreso</label>
                                <select
                                    class="pre-detail-select"
                                    wire:model.defer="formularioEdicion.condicion_ingreso"
                                >
                                    <option value="">Seleccione</option>
                                    <option value="nuevo">Nuevo</option>
                                    <option value="antiguo">Antiguo</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    {{-- DATOS DEL PADRE --}}
                    <section class="pre-detail-section">
                        <header class="pre-detail-section-header">
                            <span class="pre-detail-section-number">2</span>
                            <h3>Datos del padre</h3>
                        </header>

                        <div class="pre-detail-fields pre-detail-fields-three">
                            @include(
                                'filament.pages.partials.pre-matricula-responsable-fields',
                                [
                                    'prefijo' => 'padre',
                                ]
                            )
                        </div>
                    </section>

                    {{-- DATOS DE LA MADRE --}}
                    <section class="pre-detail-section">
                        <header class="pre-detail-section-header">
                            <span class="pre-detail-section-number">3</span>
                            <h3>Datos de la madre</h3>
                        </header>

                        <div class="pre-detail-fields pre-detail-fields-three">
                            @include(
                                'filament.pages.partials.pre-matricula-responsable-fields',
                                [
                                    'prefijo' => 'madre',
                                ]
                            )
                        </div>
                    </section>

                    {{-- DATOS DEL ACUDIENTE --}}
                    <section class="pre-detail-section">
                        <header class="pre-detail-section-header">
                            <span class="pre-detail-section-number">4</span>
                            <h3>Datos del acudiente</h3>
                        </header>

                        <div class="pre-detail-fields pre-detail-fields-three">
                            <div class="pre-detail-field">
                                <label>Origen del acudiente</label>
                                <select
                                    class="pre-detail-select"
                                    wire:model.defer="formularioEdicion.acudiente_origen"
                                >
                                    <option value="padre">El padre</option>
                                    <option value="madre">La madre</option>
                                    <option value="otro">Otra persona</option>
                                </select>
                            </div>

                            <div class="pre-detail-field">
                                <label>Parentesco</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.acudiente_parentesco"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Nombre completo</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.acudiente_nombre"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Teléfono</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.acudiente_telefono"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Tipo de documento</label>
                                <select
                                    class="pre-detail-select"
                                    wire:model.defer="formularioEdicion.acudiente_tipo_documento"
                                >
                                    <option value="">Seleccione</option>
                                    <option value="CC">Cédula de ciudadanía</option>
                                    <option value="CE">Cédula de extranjería</option>
                                    <option value="PA">Pasaporte</option>
                                </select>
                            </div>

                            <div class="pre-detail-field">
                                <label>Documento</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.acudiente_documento"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Lugar de trabajo</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.acudiente_lugar_trabajo"
                                >
                            </div>

                            <div class="pre-detail-field">
                                <label>Correo electrónico</label>
                                <input
                                    type="email"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.acudiente_correo"
                                >
                            </div>

                            <div class="pre-detail-field pre-detail-field-full">
                                <label>Dirección</label>
                                <input
                                    type="text"
                                    class="pre-detail-input"
                                    wire:model.defer="formularioEdicion.acudiente_direccion"
                                >
                            </div>
                        </div>
                    </section>
                

                <section class="pre-detail-section">

                    <header class="pre-detail-section-header">
                        <span class="pre-detail-section-number">
                            5
                        </span>

                        <h3>
                            Historial del formulario
                        </h3>
                    </header>

                    <div class="pre-history">

                        @forelse($formularioEdicion['historial'] ?? [] as $historial)

                            <article class="pre-history-item">

                                <div class="pre-history-top">

                                    <div>

                                        <strong class="pre-history-action">
                                            {{ $historial['accion_texto'] }}
                                        </strong>

                                        <div class="pre-history-user">
                                            {{ $historial['usuario'] }}
                                        </div>

                                    </div>

                                    <span class="pre-history-date">
                                        {{ $historial['fecha'] }}
                                    </span>

                                </div>

                                @if($historial['accion'] === 'actualizacion')

                                    <div class="pre-history-change">

                                        <div>
                                            <span class="pre-history-label">
                                                Campo
                                            </span>

                                            <strong>
                                                {{ $historial['campo_texto'] }}
                                            </strong>
                                        </div>

                                        <div>
                                            <span class="pre-history-label">
                                                Valor anterior
                                            </span>

                                            <strong>
                                                {{ filled($historial['anterior'])
                                                    ? $historial['anterior']
                                                    : 'Sin información' }}
                                            </strong>
                                        </div>

                                        <div>
                                            <span class="pre-history-label">
                                                Valor nuevo
                                            </span>

                                            <strong>
                                                {{ filled($historial['nuevo'])
                                                    ? $historial['nuevo']
                                                    : 'Sin información' }}
                                            </strong>
                                        </div>

                                    </div>

                                @elseif(filled($historial['descripcion']))

                                    <div class="pre-history-description">
                                        {{ $historial['descripcion'] }}
                                    </div>

                                @endif

                                </article>

                                @empty

                                    <div class="pre-history-empty">
                                        No existen movimientos registrados para este formulario.
                                    </div>

                                @endforelse

                                        </div>
                                    </section>

                                    
                                </div>

                                <footer class="pre-detail-footer">
                                    <button
                                        type="button"
                                        class="pre-detail-button pre-detail-button-cancel"
                                        wire:click="cerrarModalDetalle"
                                    >
                                        Cancelar
                                    </button>

                                    <button
                                        type="button"
                                        class="pre-detail-button pre-detail-button-save"
                                        wire:click="guardarCambios"
                                    >
                                        <x-heroicon-o-check />

                                        Guardar cambios
                                    </button>
                                </footer>

                                        </section>
                                    </div>
                                @endif












</x-filament-panels::page>