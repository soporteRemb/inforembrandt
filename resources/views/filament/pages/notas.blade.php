<x-filament-panels::page>

    <style>
        .rn-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.035);
        }

        .rn-card-header {
            padding: 10px 14px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #991b1b;
            background: #fff;
        }

        .rn-card-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .rn-actions-top {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .rn-btn {
            border-radius: 12px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 13px;
        }

        .rn-btn-primary {
            background: #b91c1c;
            color: white;
        }

        .rn-btn-success {
            background: #d5f8e7;
            color: #047857;
            border: 1px solid #84ebaa;
        }

        .rn-btn-light {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #dbe3ec;
        }

        .rn-scroll {
            max-height: 370px;
            overflow-y: auto;
            overflow-x: auto;
        }

        .rn-table {
            width: 100%;
            table-layout: fixed;
        }

        .rn-table thead {
            position: sticky;
            top: 0;
            z-index: 5;
        }

        .rn-table thead th {
            padding: 12px 10px;
            background: #f8fafc;
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
        }

        .rn-table tbody td {
            padding: 7px 8px;
            font-weight: 500;
            border-bottom: 1px solid #eef2f7;
        }

        .rn-table tbody tr:hover {
            background: #fff7f7;
        }

        .rn-table th:first-child,
        .rn-table td:first-child {
            width: 30%;
            position: sticky;
            left: 0;
            z-index: 4;
            background: #fff;
        }

        .rn-table thead th:first-child {
            z-index: 6;
            background: #f8fafc;
        }

        .rn-table th:not(:first-child),
        .rn-table td:not(:first-child) {
            width: 10%;
            text-align: center;
        }

        .rn-student-cell {
            font-weight: 400;
            font-size: 13px;
            color: #374151;
        }

        .rn-input {
            width: 83px;
            height: 32px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            text-align: center;
            font-weight: 400;
            font-size: 13px;
        }

        .rn-input:focus {
            outline: none;
            border-color: #b91c1c;
            box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.12);
        }

        .rn-empty {
            padding: 36px;
            text-align: center;
            color: #64748b;
            font-weight: 600;
        }

        .rn-indicators {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .rn-indicator-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 6px 10px;
            box-shadow: 0 3px 8px rgba(15, 23, 42, 0.03);
        }

        .rn-indicator-card span {
            display: block;
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .rn-indicator-card strong {
            font-size: 17px;
            color: #991b1b;
            font-weight: 600;
        }

        .rn-indicator-card.rn-wide strong {
            font-size: 13px;
            color: #111827;
            line-height: 1.3;
        }

        .rn-wide {
            grid-column: span 2;
        }


        .rn-search-input {
            width: 240px;
            height: 36px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 0 12px;
            font-size: 13px;
            color: #374151;
            background: #ffffff;
        }

        .rn-search-input::placeholder {
            color: #9ca3af;
        }



        .rn-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .rn-modal {
            width: 480px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.22);
            overflow: hidden;
        }

        .rn-modal-header {
            padding: 16px 18px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .rn-modal-header h3 {
            font-size: 16px;
            font-weight: 700;
            color: #991b1b;
        }

        .rn-modal-close {
            font-size: 24px;
            line-height: 1;
            color: #64748b;
        }

        .rn-modal-body {
            padding: 18px;
        }

        .rn-file-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #334155;
        }

        
        .rn-help-text {
            margin-top: 10px;
            font-size: 12px;
            color: #64748b;
        }

        .rn-modal-footer {
            padding: 14px 18px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .rn-file-hidden {
            display: none;
        }

        .rn-upload-box {
            border: 1.5px dashed #cbd5e1;
            background: #f8fafc;
            border-radius: 14px;
            padding: 18px;
            display: flex;
            gap: 14px;
            align-items: center;
            cursor: pointer;
            transition: all .2s ease;
        }

        .rn-upload-box:hover {
            border-color: #94a3b8;
            background: #ffffff;
        }

        .rn-upload-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .rn-upload-box strong {
            display: block;
            font-size: 14px;
            color: #111827;
            font-weight: 700;
        }

        .rn-upload-box span {
            display: block;
            margin-top: 3px;
            font-size: 12px;
            color: #64748b;
        }



        .rn-file-selected {
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #ecfdf5;
            border: 1px solid #86efac;
            color: #047857;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
        }


        .rn-import-summary {
            margin-top: 14px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 14px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .rn-import-summary strong {
            display: block;
            font-size: 13px;
            color: #92400e;
            font-weight: 700;
        }

        .rn-import-summary span {
            display: block;
            margin-top: 2px;
            font-size: 12px;
            color: #78350f;
        }

        .rn-modal-errors {
            width: 680px;
        }

        .rn-errors-body {
            max-height: 420px;
            overflow-y: auto;
        }

        .rn-error-item {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 9px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
            color: #334155;
        }

        .rn-error-item svg {
            color: #d97706;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .rn-upload-box-active {
            border-color: #22c55e;
            background: #f0fdf4;
            transform: scale(1.01);
            transition: all .15s ease;
        }

        .rn-loading-button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .rn-spin {
            animation: rn-spin 0.9s linear infinite;
        }

        @keyframes rn-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .rn-import-progress {
            margin-top: 12px;
            padding: 12px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .rn-import-progress-info {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 13px;
            color: #334155;
            margin-bottom: 8px;
        }

        .rn-import-progress-info small {
            color: #64748b;
        }

        .rn-progress-bar {
            height: 7px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .rn-progress-bar-fill {
            height: 100%;
            width: 35%;
            border-radius: 999px;
            background: #16a34a;
            animation: rn-progress 1.1s ease-in-out infinite;
        }

        @keyframes rn-progress {
            0% { transform: translateX(-120%); }
            100% { transform: translateX(320%); }
        }


        .rn-uploading {
            margin-top: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }


        .rn-performance-badge {
            display: inline-flex;
            min-width: 78px;
            justify-content: center;
            padding: 5px 10px;

            border-radius: 8px; 

            background: #f8fafc;
            border: 1px solid #e2e8f0;

            color: #334155;
            font-size: 12px;
            font-weight: 600;
        }


        .rn-unsaved-badge {
            margin-left: 10px;
            padding: 4px 8px;
            border-radius: 6px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            font-size: 12px;
            font-weight: 600;
        }


        .rn-export-search{
            width:100%;
            height:42px;
            border:1px solid #d1d5db;
            border-radius:10px;
            padding:0 14px;
            font-size:13px;
            outline:none;
        }

        .rn-export-list{
            margin-top:10px;
            max-height:220px;
            overflow-y:auto;
            border:1px solid #e5e7eb;
            border-radius:10px;
            background:#fff;
        }

        .rn-export-item{
            width:100%;
            text-align:left;
            padding:12px 14px;
            border:none;
            background:#fff;
            cursor:pointer;
            font-size:13px;
            border-bottom:1px solid #f1f5f9;
        }

        .rn-export-item:hover{
            background:#f8fafc;
        }

        .rn-export-item.active{
            background:#991b1b;
            color:white;
        }

        .rn-export-empty{
            padding:14px;
            font-size:13px;
            color:#6b7280;
        }

        .rn-input-error {
            border: 1px solid #dc2626 !important;
            background: #fef2f2 !important;
        }

        .rn-error-text {
            color: #dc2626;
            font-size: 11px;
            margin-top: 2px;
            font-weight: 600;
        }


    </style>

    {{ $this->form }}

    <div class="rn-indicators mt-5">

        <div class="rn-indicator-card">
            <span>Estudiantes</span>
            <strong>{{ $this->indicadores['total'] }}</strong>
        </div>

        <div class="rn-indicator-card">
            <span>Registradas</span>
            <strong>{{ $this->indicadores['registradas'] }}</strong>
        </div>

        <div class="rn-indicator-card">
            <span>Pendientes</span>
            <strong>{{ $this->indicadores['pendientes'] }}</strong>
        </div>

        <div class="rn-indicator-card">
            <span>Promedio curso</span>
            <strong>{{ $this->indicadores['promedio'] ?? '-' }}</strong>
        </div>

        <div class="rn-indicator-card rn-wide">
            <span>Mejor desempeño</span>
            <strong>
                @if($this->indicadores['mayor'])
                    {{ $this->indicadores['mayor']['nombre'] }} — {{ $this->indicadores['mayor']['nota'] }}
                @else
                    -
                @endif
            </strong>
        </div>

        <div class="rn-indicator-card rn-wide">
            <span>Requiere apoyo</span>
            <strong>
                @if($this->indicadores['menor'])
                    {{ $this->indicadores['menor']['nombre'] }} — {{ $this->indicadores['menor']['nota'] }}
                @else
                    -
                @endif
            </strong>
        </div>

    </div>



    <div class="rn-card mt-6">

        @if(count($erroresImportacion) > 0)
            <div class="rn-import-summary">
                <div>
                    <strong>Última importación con inconsistencias</strong>
                    <span>{{ count($erroresImportacion) }} inconsistencias encontradas durante la última importación.</span>
                </div>

                <button
                    type="button"
                    wire:click="abrirModalErrores"
                    class="rn-btn rn-btn-light">
                    Ver inconsistencias
                </button>
            </div>
        @endif

        <div class="rn-card-header rn-card-header-flex">

            <span>
                Registro de notas
                @if($hayCambiosSinGuardar)
                    <span class="rn-unsaved-badge">
                        Cambios sin guardar
                    </span>
                @endif
            </span>

            <div class="rn-actions-top">

                <input
                    type="text"
                    wire:model.live="buscarEstudiante"
                    placeholder="Buscar estudiante..."
                    class="rn-search-input"
                />

                @php
                    $periodoCerrado = ! $this->periodoLectivoEstaAbierto();
                @endphp

                <button
                    type="button"
                    wire:click="abrirModalExportar"
                    class="rn-btn rn-btn-light"
                >
                    Exportar Excel
                </button>

                <button type="button" wire:click="abrirModalImportar" class="rn-btn rn-btn-success" @disabled($periodoCerrado)
                @disabled($this->periodoAcademicoCerrado)>
                    Importar Excel
                </button>

                <button
                    type="button"
                    wire:click="guardarNotas"
                    class="rn-btn rn-btn-primary"
                    @disabled($periodoCerrado)
                    @disabled($this->periodoAcademicoCerrado)
                >
                    Guardar notas
                </button>

            </div>

        </div>


            
            <div class="rn-scroll">

                <table
                    class="rn-table"
                    wire:key="tabla-notas-{{ $this->data['course_id'] ?? 'curso' }}-{{ $this->data['pensum_academico_id'] ?? 'asignatura' }}-{{ $this->data['periodo'] ?? 'periodo' }}"
                >

                    <thead>
                        <tr>
                            <th class="text-left">Estudiante</th>
                            <th>Nota</th>
                            <th>Desempeño</th>
                            <th>Fallas</th>
                            <th>01</th>
                            <th>02</th>
                            <th>03</th>
                            <th>04</th>
                            <th>PGC</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($this->estudiantesFiltrados as $index => $estudiante)

                            <tr wire:key="fila-nota-{{ $estudiante['student_id'] }}-{{ $this->data['course_id'] ?? 'curso' }}-{{ $this->data['pensum_academico_id'] ?? 'asignatura' }}-{{ $this->data['periodo'] ?? 'periodo' }}">
                                <td class="rn-student-cell">
                                    {{ $estudiante['nombre'] }}
                                </td>

                                <td>
                                    <input
                                        type="text"
                                        inputmode="decimal"
                                        wire:model.live.debounce.300ms="estudiantes.{{ $index }}.nota"
                                        class="rn-input nota-cell {{ isset($this->erroresNotas[$index]) ? 'rn-input-error' : '' }}"
                                        @disabled($periodoCerrado)
                                        @disabled($this->periodoAcademicoCerrado)
                                    >
                                    @if(isset($this->erroresNotas[$index]))
                                        <div class="rn-error-text">
                                            {{ $this->erroresNotas[$index] }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="rn-performance-badge">
                                        {{ $this->obtenerDesempeno($estudiante['nota']) }}
                                    </span>
                                </td>

                                <td>
                                    <input type="number" min="0"
                                        wire:model="estudiantes.{{ $index }}.fallas"
                                        class="rn-input nota-cell"
                                        @disabled($periodoCerrado)
                                        @disabled($this->periodoAcademicoCerrado)>
                                </td>

                                @foreach(['01', '02', '03', '04'] as $codigo)
                                    <td>
                                        <input type="text"
                                            wire:model="estudiantes.{{ $index }}.mejoramientos.{{ $codigo }}"
                                            class="rn-input nota-cell"
                                            @disabled($periodoCerrado)
                                            @disabled($this->periodoAcademicoCerrado)>
                                    </td>
                                @endforeach

                                <td>
                                    <input type="text"
                                        wire:model="estudiantes.{{ $index }}.pgc"
                                        class="rn-input nota-cell"
                                        @disabled($periodoCerrado)
                                        @disabled($this->periodoAcademicoCerrado)>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="rn-empty">
                                    Seleccione un curso para cargar estudiantes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>       

    </div>

    
    @if($mostrarModalImportar)
        <div class="rn-modal-backdrop">
            <div class="rn-modal">
                <div class="rn-modal-header">
                    <h3>Importar notas desde Excel</h3>

                    <button type="button" wire:click="cerrarModalImportar" class="rn-modal-close">
                        ×
                    </button>
                </div>

                <div class="rn-modal-body">
                    <label class="rn-file-label">
                        Archivo Excel
                    </label>

                    <label
                        class="rn-upload-box"
                        x-data
                        x-on:dragover.prevent="$el.classList.add('rn-upload-box-active')"
                        x-on:dragleave.prevent="$el.classList.remove('rn-upload-box-active')"
                        x-on:drop.prevent="
                            $el.classList.remove('rn-upload-box-active');
                            const file = $event.dataTransfer.files[0];
                            if (file) {
                                $wire.upload('archivoExcel', file);
                            }
                        "
                    >
                        <input
                            type="file"
                            wire:model="archivoExcel"
                            accept=".xlsx,.xls"
                            class="rn-file-hidden"
                        >

                        <div class="rn-upload-icon">
                            <x-heroicon-o-document-arrow-up class="w-5 h-5" />
                        </div>

                        <div>
                            <strong>Seleccionar archivo Excel</strong>
                            <span>Formatos permitidos: .xlsx, .xls</span>
                        </div>
                    </label>


                    @if($archivoExcel)
                        <div class="rn-file-selected">
                            <x-heroicon-o-check-circle class="w-5 h-5" />
                            <span>
                                {{ $archivoExcel->getClientOriginalName() }}
                            </span>
                        </div>
                    @endif

                    <div wire:loading wire:target="archivoExcel" class="rn-uploading">
                        <x-heroicon-o-arrow-path class="w-4 h-4 rn-spin" />
                        <span>Subiendo archivo...</span>
                    </div>

                    <div wire:loading wire:target="importarExcel" class="rn-import-progress">
                        <div class="rn-import-progress-info">
                            <span>Procesando archivo Excel...</span>
                            <small>Esto puede tardar unos segundos.</small>
                        </div>

                        <div class="rn-progress-bar">
                            <div class="rn-progress-bar-fill"></div>
                        </div>
                    </div>

                    <p class="rn-help-text">
                        Seleccione el archivo Excel correspondiente.
                    </p>
                </div>

                <div class="rn-modal-footer">
                    <button type="button" wire:click="cerrarModalImportar" class="rn-btn rn-btn-light">
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="importarExcel"
                        wire:loading.attr="disabled"
                        wire:target="importarExcel"
                        class="rn-btn rn-btn-success"
                    >
                        <span wire:loading.remove wire:target="importarExcel">
                            Cargar archivo
                        </span>

                        <span wire:loading wire:target="importarExcel" class="rn-loading-button">
                            <x-heroicon-o-arrow-path class="w-4 h-4 rn-spin" />
                            Procesando...
                        </span>
                    </button>


                </div>
            </div>
        </div>
    @endif

    @if($mostrarModalErrores)
        <div class="rn-modal-backdrop">
            <div class="rn-modal rn-modal-errors">
                <div class="rn-modal-header">
                    <h3>Inconsistencias de importación</h3>

                    <button type="button" wire:click="cerrarModalErrores" class="rn-modal-close">
                        ×
                    </button>
                </div>

                <div class="rn-modal-body rn-errors-body">
                    @foreach($erroresImportacion as $error)
                        <div class="rn-error-item">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="rn-modal-footer">
                    <button type="button" wire:click="cerrarModalErrores" class="rn-btn rn-btn-light">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('keydown', function (event) {
            const input = event.target;

            if (!input.classList.contains('nota-cell')) {
                return;
            }

            const cells = Array.from(document.querySelectorAll('.nota-cell'));
            const currentIndex = cells.indexOf(input);

            if (currentIndex === -1) {
                return;
            }

            const columnas = 7; // Nota, Fallas, 01, 02, 03, 04, PGC

            let nextIndex = null;

            if (event.key === 'Enter' || event.key === 'ArrowDown') {
                event.preventDefault();
                nextIndex = currentIndex + columnas;
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                nextIndex = currentIndex - columnas;
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                nextIndex = currentIndex + 1;
            }

            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                nextIndex = currentIndex - 1;
            }

            if (nextIndex !== null && cells[nextIndex]) {
                cells[nextIndex].focus();
                cells[nextIndex].select();
            }
        });
    </script>

    <div
        x-data="{
            get cambios() {
                return $wire.hayCambiosSinGuardar;
            }
        }"
    >
    </div>

    <div
        x-data="{
            get cambios() {
                return $wire.hayCambiosSinGuardar;
            }
        }"
    >
    </div>

    <script>
        window.notasCambiosSinGuardar = false;

        window.addEventListener('notas-cambios-sin-guardar', function (event) {
            window.notasCambiosSinGuardar = event.detail.estado;
        });

        window.addEventListener('beforeunload', function (event) {
            if (window.notasCambiosSinGuardar === true) {
                event.preventDefault();
                event.returnValue = '';
                return '';
            }
        });
    </script>

    @if($mostrarModalExportar)
        <div class="rn-modal-backdrop">
            <div class="rn-modal">
                <div class="rn-modal-header">
                    <h3>Exportar planillas de notas</h3>

                    <button type="button" wire:click="cerrarModalExportar" class="rn-modal-close">
                        ×
                    </button>
                </div>

                <div class="rn-modal-body">
                    <label class="rn-file-label">
                        Docente
                    </label>

                    <input
                        type="text"
                        wire:model.live="buscarDocenteExportar"
                        placeholder="Buscar docente..."
                        class="rn-export-search"
                    />

                    <div class="rn-export-list">

                        @forelse($this->docentesFiltradosExportar as $docente)

                            <button
                                type="button"
                                wire:click="$set('docenteExportarId', {{ $docente['id'] }})"
                                class="rn-export-item
                                {{ $docenteExportarId == $docente['id'] ? 'active' : '' }}"
                            >
                                {{ $docente['nombre'] }}
                            </button>

                        @empty

                            <div class="rn-export-empty">
                                No se encontraron docentes.
                            </div>

                        @endforelse

                    </div>

                    <p class="rn-help-text">
                        Seleccione el docente para generar un archivo con sus cursos y asignaturas asignadas.
                    </p>
                </div>

                <div class="rn-modal-footer">
                    <button type="button" wire:click="cerrarModalExportar" class="rn-btn rn-btn-light">
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="exportarExcel"
                        class="rn-btn rn-btn-primary"
                    >
                        Exportar
                    </button>
                </div>
            </div>
        </div>
    @endif





</x-filament-panels::page>