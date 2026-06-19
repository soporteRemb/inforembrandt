<x-filament-panels::page>

    <style>
        .id-container {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .id-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
        }

        .id-card-header {
            padding: 16px 18px;
            border-bottom: 1px solid #e5e7eb;
        }

        .id-card-header h2 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #991b1b;
        }

        .id-card-header p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .id-card-body {
            padding: 18px;
        }

        .id-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        .id-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .id-file-hidden {
            display: none;
        }

        .id-upload-box {
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

        .id-upload-box:hover {
            border-color: #94a3b8;
            background: #ffffff;
        }

        .id-upload-box-active {
            border-color: #22c55e;
            background: #f0fdf4;
            transform: scale(1.01);
        }

        .id-upload-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .id-upload-box strong {
            display: block;
            font-size: 14px;
            color: #111827;
            font-weight: 700;
        }

        .id-upload-box span {
            display: block;
            margin-top: 3px;
            font-size: 12px;
            color: #64748b;
        }

        .id-file-selected {
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

        .id-card-footer {
            padding: 14px 18px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
        }

        .id-btn-primary {
            background: #b91c1c;
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
        }

        .id-btn-light {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #dbe3ec;
            padding: 9px 18px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
        }

        .id-summary {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            margin-top: 16px;
        }

        .id-summary-card {
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            border-radius: 12px;
            padding: 10px 12px;
        }

        .id-summary-card span {
            display: block;
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }

        .id-summary-card strong {
            display: block;
            margin-top: 4px;
            font-size: 16px;
            color: #991b1b;
            font-weight: 700;
        }

        .id-help {
            font-size: 12px;
            color: #64748b;
        }

        .id-uploading {
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

        .id-spin {
            animation: id-spin .9s linear infinite;
        }

        @keyframes id-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .id-import-progress {
            margin-top: 12px;
            padding: 12px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .id-import-progress-info {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 13px;
            color: #334155;
            margin-bottom: 8px;
        }

        .id-import-progress-info small {
            color: #64748b;
        }

        .id-progress-bar {
            height: 7px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .id-progress-bar-fill {
            height: 100%;
            width: 35%;
            border-radius: 999px;
            background: #16a34a;
            animation: id-progress 1.1s ease-in-out infinite;
        }

        @keyframes id-progress {
            0% { transform: translateX(-120%); }
            100% { transform: translateX(320%); }
        }

        .id-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .id-modal {
            width: 680px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.22);
            overflow: hidden;
        }

        .id-modal-header {
            padding: 16px 18px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .id-modal-header h3 {
            font-size: 16px;
            font-weight: 700;
            color: #991b1b;
        }

        .id-modal-close {
            font-size: 24px;
            line-height: 1;
            color: #64748b;
        }

        .id-modal-body {
            padding: 18px;
            max-height: 420px;
            overflow-y: auto;
        }

        .id-error-item {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 9px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
            color: #334155;
        }

        .id-error-item svg {
            color: #d97706;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .id-modal-footer {
            padding: 14px 18px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
        }

        @media (max-width: 900px) {
            .id-grid,
            .id-summary {
                grid-template-columns: 1fr;
            }
        }


        .id-btn-primary:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }


    </style>






    <div class="id-container">

        <div class="id-card">

            <div class="id-card-header">
                <h2>Estudiantes</h2>
                <p>Importa estudiantes desde Excel, los asocia al curso correspondiente y crea la matrícula del periodo lectivo.</p>
            </div>

            <div class="id-card-body">

                <div class="id-grid">
                    <div>
                        <label class="id-label">Sede</label>

                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="sedeId">
                                <option value="">Seleccione una sede</option>
                                @foreach($this->sedes as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div>
                        <label class="id-label">Periodo lectivo</label>

                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="periodoLectivoId">
                                <option value="">Seleccione un periodo</option>
                                @foreach($this->periodos as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                </div>

                <label
                    class="id-upload-box"
                    x-data
                    x-on:dragover.prevent="$el.classList.add('id-upload-box-active')"
                    x-on:dragleave.prevent="$el.classList.remove('id-upload-box-active')"
                    x-on:drop.prevent="
                        $el.classList.remove('id-upload-box-active');
                        const file = $event.dataTransfer.files[0];
                        if (file) {
                            $wire.upload('archivoEstudiantes', file);
                        }
                    "
                >
                    <input
                        type="file"
                        wire:model="archivoEstudiantes"
                        accept=".xlsx,.xls"
                        class="id-file-hidden"
                    >

                    <div class="id-upload-icon">
                        <x-heroicon-o-document-arrow-up class="w-5 h-5" />
                    </div>

                    <div>
                        <strong>Seleccionar archivo Excel de estudiantes</strong>
                        <span>Formatos permitidos: .xlsx, .xls</span>
                    </div>
                </label>

                @if($archivoEstudiantes)
                    <div class="id-file-selected">
                        <x-heroicon-o-check-circle class="w-5 h-5" />
                        <span>{{ $archivoEstudiantes->getClientOriginalName() }}</span>
                    </div>
                @endif

                <div wire:loading wire:target="archivoEstudiantes" class="id-uploading">
                    <x-heroicon-o-arrow-path class="w-4 h-4 id-spin" />
                    <span>Subiendo archivo...</span>
                </div>

                <div wire:loading wire:target="importarEstudiantes" class="id-import-progress">
                    <div class="id-import-progress-info">
                        <span>Procesando archivo Excel...</span>
                        <small>Esto puede tardar unos segundos.</small>
                    </div>

                    <div class="id-progress-bar">
                        <div class="id-progress-bar-fill"></div>
                    </div>
                </div>

                @if($resultadoEstudiantes)
                    <div class="id-summary">
                        <div class="id-summary-card">
                            <span>Última importación</span>
                            <strong>{{ $resultadoEstudiantes['fecha'] }}</strong>
                        </div>

                        <div class="id-summary-card">
                            <span>Filas leídas</span>
                            <strong>{{ $resultadoEstudiantes['filasLeidas'] }}</strong>
                        </div>

                        <div class="id-summary-card">
                            <span>Importados</span>
                            <strong>{{ $resultadoEstudiantes['totalImportados'] }}</strong>
                        </div>

                        <div class="id-summary-card">
                            <span>Matrículas</span>
                            <strong>{{ $resultadoEstudiantes['matriculas'] }}</strong>
                        </div>

                        <div class="id-summary-card">
                            <span>Inconsistencias</span>
                            <strong>{{ $resultadoEstudiantes['totalErrores'] }}</strong>
                        </div>
                    </div>
                @endif

            </div>

            <div class="id-card-footer">

                <div style="display:flex; gap:10px;">
                    @if(count($erroresEstudiantes) > 0)
                        <button
                            type="button"
                            wire:click="$set('mostrarModalErroresEstudiantes', true)"
                            class="id-btn-light"
                        >
                            Ver inconsistencias
                        </button>
                    @endif

                    <button
                        type="button"
                        wire:click="importarEstudiantes"
                        wire:loading.attr="disabled"
                        wire:target="importarEstudiantes"
                        class="id-btn-primary"
                        @disabled(! $sedeId || ! $periodoLectivoId || ! $archivoEstudiantes)
                    >
                        <span wire:loading.remove wire:target="importarEstudiantes">
                            Importar estudiantes
                        </span>

                        <span wire:loading wire:target="importarEstudiantes">
                            Procesando...
                        </span>
                    </button>
                </div>
            </div>

        </div>

    </div>



    <div class="id-card">

        <div class="id-card-header">
            <h2>Pensum académico</h2>
            <p>Importa áreas y asignaturas desde Excel, asociadas al grado, sede y periodo lectivo seleccionado.</p>
        </div>

        <div class="id-card-body">

            <div class="id-grid">
                <div>
                    <label class="id-label">Sede</label>

                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="sedeId">
                            <option value="">Seleccione una sede</option>
                            @foreach($this->sedes as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label class="id-label">Periodo lectivo</label>

                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="periodoLectivoId">
                            <option value="">Seleccione un periodo</option>
                            @foreach($this->periodos as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>

            <label
                class="id-upload-box"
                x-data
                x-on:dragover.prevent="$el.classList.add('id-upload-box-active')"
                x-on:dragleave.prevent="$el.classList.remove('id-upload-box-active')"
                x-on:drop.prevent="
                    $el.classList.remove('id-upload-box-active');
                    const file = $event.dataTransfer.files[0];
                    if (file) {
                        $wire.upload('archivoPensum', file);
                    }
                "
            >
                <input
                    type="file"
                    wire:model="archivoPensum"
                    accept=".xlsx,.xls"
                    class="id-file-hidden"
                >

                <div class="id-upload-icon">
                    <x-heroicon-o-document-arrow-up class="w-5 h-5" />
                </div>

                <div>
                    <strong>Seleccionar archivo Excel de pensum académico</strong>
                    <span>Formatos permitidos: .xlsx, .xls</span>
                </div>
            </label>

            @if($archivoPensum)
                <div class="id-file-selected">
                    <x-heroicon-o-check-circle class="w-5 h-5" />
                    <span>{{ $archivoPensum->getClientOriginalName() }}</span>
                </div>
            @endif

            <div wire:loading wire:target="archivoPensum" class="id-uploading">
                <x-heroicon-o-arrow-path class="w-4 h-4 id-spin" />
                <span>Subiendo archivo...</span>
            </div>

            <div wire:loading wire:target="importarPensum" class="id-import-progress">
                <div class="id-import-progress-info">
                    <span>Procesando archivo Excel...</span>
                    <small>Esto puede tardar unos segundos.</small>
                </div>

                <div class="id-progress-bar">
                    <div class="id-progress-bar-fill"></div>
                </div>
            </div>

            @if($resultadoPensum)
                <div class="id-summary">
                    <div class="id-summary-card">
                        <span>Última importación</span>
                        <strong>{{ $resultadoPensum['fecha'] }}</strong>
                    </div>

                    <div class="id-summary-card">
                        <span>Filas leídas</span>
                        <strong>{{ $resultadoPensum['filasLeidas'] }}</strong>
                    </div>

                    <div class="id-summary-card">
                        <span>Importados</span>
                        <strong>{{ $resultadoPensum['totalImportados'] }}</strong>
                    </div>

                    <div class="id-summary-card">
                        <span>Actualizados</span>
                        <strong>{{ $resultadoPensum['actualizados'] }}</strong>
                    </div>

                    <div class="id-summary-card">
                        <span>Inconsistencias</span>
                        <strong>{{ $resultadoPensum['totalErrores'] }}</strong>
                    </div>
                </div>
            @endif

        </div>

        <div class="id-card-footer">
             

            <div style="display:flex; gap:10px;">
                @if(count($erroresPensum) > 0)
                    <button
                        type="button"
                        wire:click="$set('mostrarModalErroresPensum', true)"
                        class="id-btn-light"
                    >
                        Ver inconsistencias
                    </button>
                @endif

                <button
                    type="button"
                    wire:click="importarPensum"
                    wire:loading.attr="disabled"
                    wire:target="importarPensum"
                    class="id-btn-primary"
                    @disabled(! $sedeId || ! $periodoLectivoId || ! $archivoPensum)
                >
                    <span wire:loading.remove wire:target="importarPensum">
                        Importar pensum
                    </span>

                    <span wire:loading wire:target="importarPensum">
                        Procesando...
                    </span>
                </button>
            </div>
        </div>

    </div>




    <div class="id-card">

        <div class="id-card-header">
                <h2>Docentes</h2>
                <p>Importa docentes desde Excel, crea o actualiza su información y asigna la dirección de curso si aplica.</p>
            </div>

            <div class="id-card-body">

                <div class="id-grid">
                    <div>
                        <label class="id-label">Sede</label>

                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="sedeId">
                                <option value="">Seleccione una sede</option>
                                @foreach($this->sedes as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div>
                        <label class="id-label">Periodo lectivo</label>

                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="periodoLectivoId">
                                <option value="">Seleccione un periodo</option>
                                @foreach($this->periodos as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                </div>

                <label
                    class="id-upload-box"
                    x-data
                    x-on:dragover.prevent="$el.classList.add('id-upload-box-active')"
                    x-on:dragleave.prevent="$el.classList.remove('id-upload-box-active')"
                    x-on:drop.prevent="
                        $el.classList.remove('id-upload-box-active');
                        const file = $event.dataTransfer.files[0];
                        if (file) {
                            $wire.upload('archivoDocentes', file);
                        }
                    "
                >
                    <input
                        type="file"
                        wire:model="archivoDocentes"
                        accept=".xlsx,.xls"
                        class="id-file-hidden"
                    >

                    <div class="id-upload-icon">
                        <x-heroicon-o-document-arrow-up class="w-5 h-5" />
                    </div>

                    <div>
                        <strong>Seleccionar archivo Excel de docentes</strong>
                        <span>Formatos permitidos: .xlsx, .xls</span>
                    </div>
                </label>

                @if($archivoDocentes)
                    <div class="id-file-selected">
                        <x-heroicon-o-check-circle class="w-5 h-5" />
                        <span>{{ $archivoDocentes->getClientOriginalName() }}</span>
                    </div>
                @endif

                <div wire:loading wire:target="archivoDocentes" class="id-uploading">
                    <x-heroicon-o-arrow-path class="w-4 h-4 id-spin" />
                    <span>Subiendo archivo...</span>
                </div>

                <div wire:loading wire:target="importarDocentes" class="id-import-progress">
                    <div class="id-import-progress-info">
                        <span>Procesando archivo Excel...</span>
                        <small>Esto puede tardar unos segundos.</small>
                    </div>

                    <div class="id-progress-bar">
                        <div class="id-progress-bar-fill"></div>
                    </div>
                </div>

                @if($resultadoDocentes)
                    <div class="id-summary">
                        <div class="id-summary-card">
                            <span>Última importación</span>
                            <strong>{{ $resultadoDocentes['fecha'] }}</strong>
                        </div>

                        <div class="id-summary-card">
                            <span>Filas leídas</span>
                            <strong>{{ $resultadoDocentes['filasLeidas'] }}</strong>
                        </div>

                        <div class="id-summary-card">
                            <span>Importados</span>
                            <strong>{{ $resultadoDocentes['totalImportados'] }}</strong>
                        </div>

                        <div class="id-summary-card">
                            <span>Actualizados</span>
                            <strong>{{ $resultadoDocentes['actualizados'] }}</strong>
                        </div>

                        <div class="id-summary-card">
                            <span>Inconsistencias</span>
                            <strong>{{ $resultadoDocentes['totalErrores'] }}</strong>
                        </div>
                    </div>
                @endif

            </div>

            <div class="id-card-footer">

                <div style="display:flex; gap:10px;">
                    @if(count($erroresDocentes) > 0)
                        <button
                            type="button"
                            wire:click="$set('mostrarModalErroresDocentes', true)"
                            class="id-btn-light"
                        >
                            Ver inconsistencias
                        </button>
                    @endif

                    <button
                        type="button"
                        wire:click="importarDocentes"
                        wire:loading.attr="disabled"
                        wire:target="importarDocentes"
                        class="id-btn-primary"
                        @disabled(! $sedeId || ! $periodoLectivoId || ! $archivoDocentes)
                    >
                        <span wire:loading.remove wire:target="importarDocentes">
                            Importar docentes
                        </span>

                        <span wire:loading wire:target="importarDocentes">
                            Procesando...
                        </span>
                    </button>
                </div>
            </div>

        </div>






        <div class="id-card">

            <div class="id-card-header">
                <h2>Asignación de materias (Docente)</h2>
                <p>Importa la relación entre docente, curso y asignatura para dejar listas las cargas académicas.</p>
            </div>

            <div class="id-card-body">

                <div class="id-grid">
                    <div>
                        <label class="id-label">Sede</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="sedeId">
                                <option value="">Seleccione una sede</option>
                                @foreach($this->sedes as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div>
                        <label class="id-label">Periodo lectivo</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="periodoLectivoId">
                                <option value="">Seleccione un periodo</option>
                                @foreach($this->periodos as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                </div>

                <label
                    class="id-upload-box"
                    x-data
                    x-on:dragover.prevent="$el.classList.add('id-upload-box-active')"
                    x-on:dragleave.prevent="$el.classList.remove('id-upload-box-active')"
                    x-on:drop.prevent="
                        $el.classList.remove('id-upload-box-active');
                        const file = $event.dataTransfer.files[0];
                        if (file) {
                            $wire.upload('archivoAsignaciones', file);
                        }
                    "
                >
                    <input
                        type="file"
                        wire:model="archivoAsignaciones"
                        accept=".xlsx,.xls"
                        class="id-file-hidden"
                    >

                    <div class="id-upload-icon">
                        <x-heroicon-o-document-arrow-up class="w-5 h-5" />
                    </div>

                    <div>
                        <strong>Seleccionar archivo Excel de asignación de materias</strong>
                        <span>Formatos permitidos: .xlsx, .xls</span>
                    </div>
                </label>

                @if($archivoAsignaciones)
                    <div class="id-file-selected">
                        <x-heroicon-o-check-circle class="w-5 h-5" />
                        <span>{{ $archivoAsignaciones->getClientOriginalName() }}</span>
                    </div>
                @endif

                <div wire:loading wire:target="archivoAsignaciones" class="id-uploading">
                    <x-heroicon-o-arrow-path class="w-4 h-4 id-spin" />
                    <span>Subiendo archivo...</span>
                </div>

                <div wire:loading wire:target="importarAsignaciones" class="id-import-progress">
                    <div class="id-import-progress-info">
                        <span>Procesando archivo Excel...</span>
                        <small>Esto puede tardar unos segundos.</small>
                    </div>

                    <div class="id-progress-bar">
                        <div class="id-progress-bar-fill"></div>
                    </div>
                </div>

                @if($resultadoAsignaciones)
                    <div class="id-summary">
                        <div class="id-summary-card">
                            <span>Última importación</span>
                            <strong>{{ $resultadoAsignaciones['fecha'] }}</strong>
                        </div>

                        <div class="id-summary-card">
                            <span>Filas leídas</span>
                            <strong>{{ $resultadoAsignaciones['filasLeidas'] }}</strong>
                        </div>

                        <div class="id-summary-card">
                            <span>Importados</span>
                            <strong>{{ $resultadoAsignaciones['totalImportados'] }}</strong>
                        </div>

                        <div class="id-summary-card">
                            <span>Actualizados</span>
                            <strong>{{ $resultadoAsignaciones['actualizados'] }}</strong>
                        </div>

                        <div class="id-summary-card">
                            <span>Inconsistencias</span>
                            <strong>{{ $resultadoAsignaciones['totalErrores'] }}</strong>
                        </div>
                    </div>
                @endif

            </div>

            <div class="id-card-footer">

                <div style="display:flex; gap:10px;">
                    @if(count($erroresAsignaciones) > 0)
                        <button
                            type="button"
                            wire:click="$set('mostrarModalErroresAsignaciones', true)"
                            class="id-btn-light"
                        >
                            Ver inconsistencias
                        </button>
                    @endif

                    <button
                        type="button"
                        wire:click="importarAsignaciones"
                        wire:loading.attr="disabled"
                        wire:target="importarAsignaciones"
                        class="id-btn-primary"
                        @disabled(! $sedeId || ! $periodoLectivoId || ! $archivoAsignaciones)
                    >
                        <span wire:loading.remove wire:target="importarAsignaciones">
                            Importar asignaciones
                        </span>

                        <span wire:loading wire:target="importarAsignaciones">
                            Procesando...
                        </span>
                    </button>
                </div>
            </div>

        </div>





    @if($mostrarModalErroresEstudiantes ?? false)
        <div class="id-modal-backdrop">
            <div class="id-modal">
                <div class="id-modal-header">
                    <h3>Inconsistencias de importación - Estudiantes</h3>

                    <button
                        type="button"
                        wire:click="$set('mostrarModalErroresEstudiantes', false)"
                        class="id-modal-close"
                    >
                        ×
                    </button>
                </div>

                <div class="id-modal-body">
                    @foreach($erroresEstudiantes as $error)
                        <div class="id-error-item">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="id-modal-footer">
                    <button
                        type="button"
                        wire:click="$set('mostrarModalErroresEstudiantes', false)"
                        class="id-btn-light"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif




    @if($mostrarModalErroresPensum ?? false)
        <div class="id-modal-backdrop">
            <div class="id-modal">
                <div class="id-modal-header">
                    <h3>Inconsistencias de importación - Pensum académico</h3>

                    <button
                        type="button"
                        wire:click="$set('mostrarModalErroresPensum', false)"
                        class="id-modal-close"
                    >
                        ×
                    </button>
                </div>

                <div class="id-modal-body">
                    @foreach($erroresPensum as $error)
                        <div class="id-error-item">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="id-modal-footer">
                    <button
                        type="button"
                        wire:click="$set('mostrarModalErroresPensum', false)"
                        class="id-btn-light"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif





    @if($mostrarModalErroresDocentes ?? false)
        <div class="id-modal-backdrop">
            <div class="id-modal">
                <div class="id-modal-header">
                    <h3>Inconsistencias de importación - Docentes</h3>

                    <button
                        type="button"
                        wire:click="$set('mostrarModalErroresDocentes', false)"
                        class="id-modal-close"
                    >
                        ×
                    </button>
                </div>

                <div class="id-modal-body">
                    @foreach($erroresDocentes as $error)
                        <div class="id-error-item">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="id-modal-footer">
                    <button
                        type="button"
                        wire:click="$set('mostrarModalErroresDocentes', false)"
                        class="id-btn-light"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif








    @if($mostrarModalErroresAsignaciones ?? false)
        <div class="id-modal-backdrop">
            <div class="id-modal">
                <div class="id-modal-header">
                    <h3>Inconsistencias de importación - Asignación de materias</h3>

                    <button
                        type="button"
                        wire:click="$set('mostrarModalErroresAsignaciones', false)"
                        class="id-modal-close"
                    >
                        ×
                    </button>
                </div>

                <div class="id-modal-body">
                    @foreach($erroresAsignaciones as $error)
                        <div class="id-error-item">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="id-modal-footer">
                    <button
                        type="button"
                        wire:click="$set('mostrarModalErroresAsignaciones', false)"
                        class="id-btn-light"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif



</x-filament-panels::page>