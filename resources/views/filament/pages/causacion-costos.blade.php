<x-filament-panels::page>
    <style>
        .causacion-page {
            max-width: 100%;
        }

        .causacion-alert {
            border: 1px solid #f6c56f;
            background: #fffaf0;
            color: #7c4a03;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .causacion-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .causacion-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 22px;
            min-height: 430px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .04);
        }

        .causacion-title {
            color: #991b1b;
            font-size: 18px;
            font-weight: 750;
            margin-bottom: 5px;
        }

        .causacion-subtitle {
            color: #667085;
            font-size: 13px;
            margin-bottom: 22px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .field.full {
            grid-column: span 4;
        }

        .field label {
            display: block;
            font-size: 13.5px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 6px;
            letter-spacing: .1px;
        }

        .required {
            color: #b91c1c;
            font-weight: 800;
        }

        

        .info-line {
            margin-top: 14px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
        }

        .summary-grid {
            margin-top: 26px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 120px;
        }

        .summary-box {
            border: 1px solid #e5e7eb;
            border-radius: 11px;
            background: #fbfbfc;
            padding: 13px 14px;
            min-height: 76px;
        }

        .summary-number {
            font-size: 19px;
            font-weight: 800;
            color: #111827;
            line-height: 1.1;
        }

        .summary-number.red {
            color: #b91c1c;
        }

        .summary-label {
            margin-top: 6px;
            font-size: 12px;
            color: #667085;
        }

        .action-card {
            margin-top: 14px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .035);
        }

        .action-wrap {
            display: flex;
            justify-content: center;
            gap: 14px;
        }

        .btn-main,
        .btn-outline {
            min-width: 230px;
            height: 44px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-main {
            border: none;
            color: #fff;
            background: linear-gradient(90deg, #b00000 0%, #c00000 100%);
        }

        .btn-outline {
            border: 1px solid #c00000;
            color: #b00000;
            background: #fff;
        }

        .history-card {
            margin-top: 14px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px 18px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .035);
        }

        .history-title {
            color: #991b1b;
            font-size: 17px;
            font-weight: 750;
            margin-bottom: 12px;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
        }

        .history-table th {
            background: #f8fafc;
            color: #475569;
            text-align: left;
            padding: 10px 12px;
            font-weight: 750;
            border-bottom: 1px solid #e5e7eb;
        }

        .history-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eef0f3;
            color: #334155;
        }

        .history-table tr:last-child td {
            border-bottom: none;
        }

        .empty-row {
            text-align: center;
            color: #6b7280;
            padding: 14px !important;
        }

        @media (max-width: 1350px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .field.full {
                grid-column: span 2;
            }

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 1100px) {
            .causacion-grid {
                grid-template-columns: 1fr;
            }
        }


        /* Diferenciar visualmente campos vacíos */
        .fi-input-wrp select {
            color: #0f172a;
        }

        .fi-input-wrp select:invalid {
            color: #94a3b8;
        }

        .fi-input-wrp select option[value=""] {
            color: #94a3b8;
        }

        .fi-input-wrp select option:not([value=""]) {
            color: #0f172a;
        }




        .cc-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cc-modal {
            width: 480px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.22);
            overflow: hidden;
        }

        .cc-modal-header {
            padding: 16px 18px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cc-modal-header h3 {
            font-size: 16px;
            font-weight: 700;
            color: #991b1b;
        }

        .cc-modal-close {
            font-size: 24px;
            line-height: 1;
            color: #64748b;
        }

        .cc-modal-body {
            padding: 18px;
        }

        .cc-confirm-box {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px;
            background: #ffffff;
        }

        .cc-confirm-row {
            margin-bottom: 10px;
        }

        .cc-confirm-row span {
            display: block;
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
        }

        .cc-confirm-row strong {
            display: block;
            font-size: 14px;
            color: #111827;
            font-weight: 700;
        }

        .cc-total-box {
            margin-top: 14px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            border-radius: 14px;
            padding: 14px;
        }

        .cc-total-box span {
            display: block;
            font-size: 12px;
            color: #991b1b;
            font-weight: 700;
        }

        .cc-total-box strong {
            display: block;
            margin-top: 4px;
            font-size: 24px;
            color: #991b1b;
            font-weight: 800;
        }

        .cc-modal-footer {
            padding: 14px 18px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .cc-btn {
            border-radius: 12px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 13px;
        }

        .cc-btn-primary {
            background: #b91c1c;
            color: white;
        }

        .cc-btn-light {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #dbe3ec;
        }

        .cc-loading-button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .cc-spin {
            animation: cc-spin 0.9s linear infinite;
        }

        @keyframes cc-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .cc-import-progress {
            margin-top: 12px;
            padding: 12px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .cc-import-progress-info {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 13px;
            color: #334155;
            margin-bottom: 8px;
        }

        .cc-import-progress-info small {
            color: #64748b;
        }

        .cc-progress-bar {
            height: 7px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .cc-progress-bar-fill {
            height: 100%;
            width: 35%;
            border-radius: 999px;
            background: #16a34a;
            animation: cc-progress 1.1s ease-in-out infinite;
        }

        @keyframes cc-progress {
            0% { transform: translateX(-120%); }
            100% { transform: translateX(320%); }
        }

        .history-filters {
            display: flex;
            gap: 10px;
            align-items: center;
            margin: 14px 0;
        }

        
        .history-filter-input,
        .history-filter-select {
            height: 38px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding-left: 12px;
            font-size: 13px;
            color: #334155;
            background-color: #fff;
        }
        
        .history-filter-select {
            width: 170px;
            padding-right: 34px;

            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;

            background-color: #fff !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M6 8l4 4 4-4' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 10px center !important;
            background-size: 16px 16px !important;
        }

        .history-filter-select::-ms-expand {
            display: none;
        }

        .causacion-card {
            display: flex;
            flex-direction: column;
        }

        .summary-grid-bottom {
            margin-top: auto;
        }

        .history-table-wrapper {
            max-height: 360px;
            overflow-y: auto;
        }

        

        .cc-blocked-modal {
            width: 560px;
        }

        .cc-blocked-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 14px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .cc-blocked-icon svg {
            width: 34px;
            height: 34px;
        }

        .cc-blocked-title {
            text-align: center;
            color: #991b1b;
            font-size: 19px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .cc-blocked-message {
            text-align: center;
            color: #475569;
            font-size: 14px;
            line-height: 1.55;
            max-width: 460px;
            margin: 0 auto;
        }

        .cc-blocked-message strong {
            color: #991b1b;
        }

        .cc-blocked-summary {
            margin-top: 18px;
            padding: 14px;
            border: 1px solid #fecaca;
            background: #fff7f7;
            border-radius: 12px;
        }

        .cc-blocked-summary-row {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            padding: 7px 0;
            border-bottom: 1px solid #fee2e2;
            font-size: 13px;
        }

        .cc-blocked-summary-row:last-child {
            border-bottom: none;
        }

        .cc-blocked-summary-row span {
            color: #64748b;
        }

        .cc-blocked-summary-row strong {
            color: #1f2937;
            text-align: right;
        }
        


    </style>

    <div class="causacion-page">

        {{-- TARJETAS PRINCIPALES --}}
        <div class="causacion-grid">

            {{-- TARJETA IZQUIERDA: CONCEPTOS OBLIGATORIOS --}}
            <section class="causacion-card">
                <div class="causacion-title flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-red-700" />
                    <span>Conceptos obligatorios</span>
                </div>

                <div class="form-grid">
                    {{-- Filtros obligatorios --}}
                    <div class="field">
                        <label>Sede<span class="required">*</span></label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select
                                wire:model.live="sede_id"
                                required
                            >
                                <option value="">Seleccione...</option>
                                @foreach($this->sedes as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="field">
                        <label>Periodo lectivo<span class="required">*</span></label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select
                                wire:model.live="periodo_lectivo_id"
                                required
                            >
                                <option value="">Seleccione...</option>
                                @foreach($this->periodos as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="field">
                        <label>Grado<span class="required">*</span></label>

                        <x-filament::input.wrapper>
                            <x-filament::input.select
                                wire:model.live="grado_obligatorio"
                                required
                            >
                                <option value="">Seleccione...</option>

                                <option value="todos">
                                    Todos los grados
                                </option>

                                @foreach($this->grados as $valor => $nombre)
                                    <option value="{{ $valor }}">
                                        {{ $nombre }}
                                    </option>
                                @endforeach

                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="field">
                        <label>Concepto obligatorio<span class="required">*</span></label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select
                                wire:model.live="concepto_obligatorio_id"
                                required
                            >
                                <option value="">Seleccione...</option>

                                @foreach($this->conceptosObligatorios as $id => $nombre)
                                    <option value="{{ $id }}">
                                        {{ $nombre }}
                                    </option>
                                @endforeach

                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                        
                    </div>

                    @if($this->conceptoObligatorioEsPension())
                    <div class="field full">
                        <label>Mes de pensión<span class="required">*</span></label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select
                                wire:model.live="mes_pension"
                                required
                            >
                                <option value="">Seleccione...</option>

                                @foreach($this->meses as $numero => $nombre)
                                    <option value="{{ $numero }}">
                                        {{ $nombre }}
                                    </option>
                                @endforeach

                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                    @endif
                </div>

                {{-- Resumen tarjeta izquierda --}}
                <div class="summary-grid summary-grid-bottom">
                    <div class="summary-box">
                        <div class="summary-number">{{ $resumenObligatorio['estudiantes'] }}</div>
                        <div class="summary-label">Estudiantes</div>
                    </div>

                    <div class="summary-box">

                        <div class="summary-number">
                            ${{ number_format($resumenObligatorio['valor_base_total'], 0, ',', '.') }}
                        </div>

                        @if($resumenObligatorio['tarifa_variable'] ?? false)
                            <div class="summary-label">
                                Tarifas según grado
                            </div>
                        @else
                            <div class="summary-label">
                                Tarifa:
                                ${{ number_format($resumenObligatorio['tarifa_base'], 0, ',', '.') }}
                            </div>

                            <div class="summary-label">
                                {{ number_format($resumenObligatorio['estudiantes'], 0, ',', '.') }}
                                ×
                                ${{ number_format($resumenObligatorio['tarifa_base'], 0, ',', '.') }}
                            </div>
                        @endif

                    </div>

                    <div class="summary-box">
                        <div class="summary-number">{{ $resumenObligatorio['personalizados'] }}</div>
                        <div class="summary-label">Personalizados</div>

                        @php($difObligatorio = $resumenObligatorio['diferencia_personalizados'] ?? 0)

                        <div class="summary-label">
                            @if($difObligatorio > 0)
                                (+ ${{ number_format($difObligatorio, 0, ',', '.') }})
                            @elseif($difObligatorio < 0)
                                (- ${{ number_format(abs($difObligatorio), 0, ',', '.') }})
                            @else
                                ($0)
                            @endif
                        </div>
                    </div>

                    <div class="summary-box">
                        <div class="summary-number red">${{ number_format($resumenObligatorio['total_causar'], 0, ',', '.') }}</div>
                        <div class="summary-label">Total a causar</div>
                    </div>
                </div>
            </section>

            {{-- TARJETA DERECHA: CONCEPTOS NO OBLIGATORIOS --}}
            <section class="causacion-card">
                <div class="causacion-title flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-red-700" />
                    <span>Conceptos no obligatorios</span>
                </div>

                <div class="form-grid">
                    {{-- Filtros no obligatorios --}}
                    <div class="field">
                        <label>Sede<span class="required">*</span></label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select
                                wire:model.live="sede_id"
                                required
                            >
                                <option value="">Seleccione...</option>
                                @foreach($this->sedes as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="field">
                        <label>Periodo lectivo<span class="required">*</span></label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select
                                wire:model.live="periodo_lectivo_id"
                                required
                            >
                                <option value="">Seleccione...</option>
                                @foreach($this->periodos as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="field">
                        <label>Grado<span class="required">*</span></label>

                        <x-filament::input.wrapper>
                            <x-filament::input.select
                                wire:model.live="grado_no_obligatorio"
                                required
                            >
                                <option value="">Seleccione...</option>

                                <option value="todos">
                                    Todos los grados
                                </option>

                                @foreach($this->grados as $valor => $nombre)
                                    <option value="{{ $valor }}">
                                        {{ $nombre }}
                                    </option>
                                @endforeach

                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="field">
                        <label>Concepto no obligatorio<span class="required">*</span></label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select
                                wire:model.live="concepto_no_obligatorio_id"
                                required
                            >
                                <option value="">Seleccione...</option>

                                @foreach($this->conceptosNoObligatorios as $id => $nombre)
                                    <option value="{{ $id }}">
                                        {{ $nombre }}
                                    </option>
                                @endforeach

                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                </div>

                {{-- Resumen tarjeta derecha --}}
                <div class="summary-grid summary-grid-bottom">
                    <div class="summary-box">
                        <div class="summary-number">{{ $resumenNoObligatorio['estudiantes'] }}</div>
                        <div class="summary-label">Estudiantes</div>
                    </div>

                    <div class="summary-box">

                        <div class="summary-number">
                            ${{ number_format($resumenNoObligatorio['valor_base_total'], 0, ',', '.') }}
                        </div>

                        @if($resumenNoObligatorio['tarifa_variable'] ?? false)

                            <div class="summary-label">
                                Tarifas según grado
                            </div>

                        @else

                            <div class="summary-label">
                                Tarifa:
                                ${{ number_format($resumenNoObligatorio['tarifa_base'], 0, ',', '.') }}
                            </div>

                            <div class="summary-label">
                                {{ number_format($resumenNoObligatorio['estudiantes'], 0, ',', '.') }}
                                ×
                                ${{ number_format($resumenNoObligatorio['tarifa_base'], 0, ',', '.') }}
                            </div>

                        @endif

                    </div>

                    <div class="summary-box">
                        <div class="summary-number">{{ $resumenNoObligatorio['personalizados'] }}</div>
                        <div class="summary-label">Personalizados</div>

                        @php($difNoObligatorio = $resumenNoObligatorio['diferencia_personalizados'] ?? 0)

                        <div class="summary-label">
                            @if($difNoObligatorio > 0)
                                (+ ${{ number_format($difNoObligatorio, 0, ',', '.') }})
                            @elseif($difNoObligatorio < 0)
                                (- ${{ number_format(abs($difNoObligatorio), 0, ',', '.') }})
                            @else
                                ($0)
                            @endif
                        </div>
                    </div>

                    <div class="summary-box">
                        <div class="summary-number red">${{ number_format($resumenNoObligatorio['total_causar'], 0, ',', '.') }}</div>
                        <div class="summary-label">Total a causar</div>
                    </div>
                </div>
            </section>

        </div>

        {{-- BOTONES PRINCIPALES --}}
        <section class="action-card">
            <div class="action-wrap">
                <button type="button" wire:click="causar" class="btn-main flex items-center justify-center gap-2">
                    <x-heroicon-o-document-plus class="w-5 h-5" />
                    <span>Causar</span>
                </button>

                <button type="button" wire:click="reversar" class="btn-outline flex items-center justify-center gap-2">
                    <x-heroicon-o-arrow-uturn-left class="w-5 h-5" />
                    <span>Reversar</span>
                </button>
            </div>
        </section>

        {{-- HISTORIAL --}}
        <section class="history-card">
            <div class="history-title flex items-center gap-2">
                <x-heroicon-o-clock class="w-5 h-5 text-red-700" />
                <span>Historial de causaciones recientes</span>
            </div>

            <div class="history-filters">
                <input
                    type="text"
                    wire:model.live.debounce.400ms="filtroHistorialBuscar"
                    placeholder="Buscar concepto..."
                    class="history-filter-input"
                >

                <select wire:model.live="filtroHistorialGrado" class="history-filter-select">
                    <option value="">Todos los grados</option>
                    @foreach($this->grados as $valor => $nombre)
                        <option value="{{ $valor }}">{{ $nombre }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filtroHistorialEstado" class="history-filter-select">
                    <option value="">Todos los estados</option>
                    <option value="activo">Causado</option>
                    <option value="reversado">Reversado</option>
                </select>

                <button
                    type="button"
                    wire:click="limpiarFiltrosHistorial"
                    class="history-filter-clear"
                >
                    Limpiar
                </button>
            </div>

            <div class="history-table-wrapper">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Grado</th>
                            <th>Concepto</th>
                            <th>Mes</th>
                            <th>Estudiantes</th>
                            <th>Total causado</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($historialCausaciones as $item)
                            <tr
                                wire:click="verDetalleHistorial('{{ $item['referencia'] }}', '{{ $item['estado_raw'] }}')"
                                style="cursor:pointer;"
                                title="Ver detalle de la causación"
                            >
                                <td>{{ $item['fecha'] }}</td>
                                <td>{{ $item['tipo'] }}</td>
                                <td>{{ $item['grado'] }}</td>
                                <td>{{ $item['concepto'] }}</td>
                                <td>{{ $item['mes'] }}</td>
                                <td>{{ $item['estudiantes'] }}</td>
                                <td>${{ number_format($item['total_causado'], 0, ',', '.') }}</td>
                                <td>{{ $item['usuario'] }}</td>
                                <td>{{ $item['estado'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-row">
                                    No hay causaciones registradas todavía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>




    @if($mostrarModalCausar)
        <div class="cc-modal-backdrop">
            <div class="cc-modal">
                <div class="cc-modal-header">
                    <h3>Confirmar causación</h3>

                    <button type="button" wire:click="$set('mostrarModalCausar', false)" class="cc-modal-close">
                        ×
                    </button>
                </div>

                <div class="cc-modal-body">
                    <div class="cc-confirm-box">
                        <div class="cc-confirm-row">
                            <span>Concepto</span>
                            <strong>{{ $confirmacionCausacion['concepto'] }}</strong>
                        </div>

                        <div class="cc-confirm-row">
                            <span>Grado</span>
                            <strong>{{ $confirmacionCausacion['grado'] }}</strong>
                        </div>

                        <div class="cc-confirm-row">
                            <span>Mes</span>
                            <strong>{{ $confirmacionCausacion['mes'] }}</strong>
                        </div>

                        <div class="cc-confirm-row" style="margin-bottom:0;">
                            <span>Estudiantes</span>
                            <strong>{{ $confirmacionCausacion['estudiantes'] }}</strong>
                        </div>
                    </div>

                    <div class="cc-total-box">
                        <span>Total a causar</span>
                        <strong>${{ number_format($confirmacionCausacion['total'], 0, ',', '.') }}</strong>
                    </div>

                    <div wire:loading wire:target="confirmarCausacion" class="cc-import-progress">
                        <div class="cc-import-progress-info">
                            <span>Procesando causación...</span>
                            <small>Esto puede tardar unos segundos.</small>
                        </div>

                        <div class="cc-progress-bar">
                            <div class="cc-progress-bar-fill"></div>
                        </div>
                    </div>
                </div>

                <div class="cc-modal-footer">
                    <button type="button" wire:click="$set('mostrarModalCausar', false)" class="cc-btn cc-btn-light">
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="confirmarCausacion"
                        wire:loading.attr="disabled"
                        wire:target="confirmarCausacion"
                        class="cc-btn cc-btn-primary"
                    >
                        <span wire:loading.remove wire:target="confirmarCausacion">
                            Sí, causar
                        </span>

                        <span wire:loading wire:target="confirmarCausacion" class="cc-loading-button">
                            <x-heroicon-o-arrow-path class="w-4 h-4 cc-spin" />
                            Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif








    @if($mostrarModalReversar)
        <div class="cc-modal-backdrop">
            <div class="cc-modal">
                <div class="cc-modal-header">
                    <h3>Confirmar reversión</h3>

                    <button type="button" wire:click="$set('mostrarModalReversar', false)" class="cc-modal-close">
                        ×
                    </button>
                </div>

                <div class="cc-modal-body">
                    <div class="cc-confirm-box">
                        <div class="cc-confirm-row">
                            <span>Concepto</span>
                            <strong>{{ $confirmacionReversion['concepto'] }}</strong>
                        </div>

                        <div class="cc-confirm-row">
                            <span>Grado</span>
                            <strong>{{ $confirmacionReversion['grado'] }}</strong>
                        </div>

                        <div class="cc-confirm-row">
                            <span>Mes</span>
                            <strong>{{ $confirmacionReversion['mes'] }}</strong>
                        </div>

                        <div class="cc-confirm-row" style="margin-bottom:0;">
                            <span>Estudiantes</span>
                            <strong>{{ $confirmacionReversion['estudiantes'] }}</strong>
                        </div>
                    </div>

                    <div class="cc-total-box">
                        <span>Total a reversar</span>
                        <strong>${{ number_format($confirmacionReversion['total'], 0, ',', '.') }}</strong>
                    </div>

                    <div class="cc-confirm-box" style="margin-top:14px;">
                        <div class="cc-confirm-row" style="margin-bottom:0;">
                            <span>Motivo de la reversión <span style="color:#dc2626">*</span></span>

                            <textarea
                                wire:model.defer="motivoReversion"
                                rows="3"
                                maxlength="500"
                                placeholder="Ejemplo: La causación fue realizada sobre un grado incorrecto..."
                                style="
                                    width:100%;
                                    margin-top:8px;
                                    border:1px solid #d1d5db;
                                    border-radius:10px;
                                    padding:10px 12px;
                                    resize:none;
                                    font-size:13px;
                                    color:#334155;
                                "
                            ></textarea>

                            @error('motivoReversion')
                                <p style="margin-top:6px;color:#dc2626;font-size:12px;">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div wire:loading wire:target="confirmarReversion" class="cc-import-progress">
                        <div class="cc-import-progress-info">
                            <span>Procesando reversión...</span>
                            <small>Esto puede tardar unos segundos.</small>
                        </div>

                        <div class="cc-progress-bar">
                            <div class="cc-progress-bar-fill"></div>
                        </div>
                    </div>
                </div>

                <div class="cc-modal-footer">
                    <button type="button" wire:click="$set('mostrarModalReversar', false)" class="cc-btn cc-btn-light">
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="confirmarReversion"
                        wire:loading.attr="disabled"
                        wire:target="confirmarReversion"
                        class="cc-btn cc-btn-primary"
                    >
                        <span wire:loading.remove wire:target="confirmarReversion">
                            Sí, reversar
                        </span>

                        <span wire:loading wire:target="confirmarReversion" class="cc-loading-button">
                            <x-heroicon-o-arrow-path class="w-4 h-4 cc-spin" />
                            Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif













    @if($mostrarModalReversionBloqueada)
        <div
            class="cc-modal-backdrop"
            wire:click.self="$set('mostrarModalReversionBloqueada', false)"
        >
            <div class="cc-modal cc-blocked-modal">
                <div class="cc-modal-header">
                    <h3>Reversión no permitida</h3>

                    <button
                        type="button"
                        wire:click="$set('mostrarModalReversionBloqueada', false)"
                        class="cc-modal-close"
                    >
                        ×
                    </button>
                </div>

                <div class="cc-modal-body">
                    <div class="cc-blocked-icon">
                        <x-heroicon-o-exclamation-triangle />
                    </div>

                    <div class="cc-blocked-title">
                        No es posible reversar esta causación
                    </div>

                    <div class="cc-blocked-message">
                        La obligación posee pagos registrados.
                        Primero debe
                        <strong>
                            anular los pagos asociados
                        </strong>
                        a esta obligación.
                    </div>

                    <div class="cc-blocked-summary">
                        <div class="cc-blocked-summary-row">
                            <span>Concepto</span>

                            <strong>
                                {{ $detalleReversionBloqueada['concepto'] ?? '-' }}
                            </strong>
                        </div>

                        <div class="cc-blocked-summary-row">
                            <span>Grado</span>

                            <strong>
                                {{ $detalleReversionBloqueada['grado'] ?? '-' }}
                            </strong>
                        </div>

                        @if(
                            filled(
                                $detalleReversionBloqueada['mes'] ?? null
                            )
                            && ($detalleReversionBloqueada['mes'] ?? '-') !== '-'
                        )
                            <div class="cc-blocked-summary-row">
                                <span>Mes</span>

                                <strong>
                                    {{ $detalleReversionBloqueada['mes'] }}
                                </strong>
                            </div>
                        @endif

                        <div class="cc-blocked-summary-row">
                            <span>Estudiantes con pagos</span>

                            <strong>
                                {{
                                    $detalleReversionBloqueada[
                                        'estudiantes_con_pagos'
                                    ] ?? 0
                                }}
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="cc-modal-footer">
                    <button
                        type="button"
                        wire:click="$set('mostrarModalReversionBloqueada', false)"
                        class="cc-btn cc-btn-primary"
                    >
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    @endif













    @if($mostrarModalDetalleHistorial)
        <div class="cc-modal-backdrop">
            <div class="cc-modal" style="width: 760px;">
                <div class="cc-modal-header">
                    <h3>Detalle de causación</h3>

                    <button type="button" wire:click="$set('mostrarModalDetalleHistorial', false)" class="cc-modal-close">
                        ×
                    </button>
                </div>

                <div class="cc-modal-body">
                    <div class="cc-confirm-box">
                        <div class="cc-confirm-row">
                            <span>Concepto</span>
                            <strong>{{ $detalleHistorial['general']['concepto'] ?? '-' }}</strong>
                        </div>

                        <div class="cc-confirm-row">
                            <span>Grado / Mes / Estado</span>
                            <strong>
                                {{ $detalleHistorial['general']['grado'] ?? '-' }}
                                —
                                {{ $detalleHistorial['general']['mes'] ?? '-' }}
                                —
                                {{ $detalleHistorial['general']['estado'] ?? '-' }}
                            </strong>
                        </div>

                        <div class="cc-confirm-row">
                            <span>Causado por</span>
                            <strong>
                                {{ $detalleHistorial['general']['causado_por'] ?? '-' }}
                                |
                                {{ $detalleHistorial['general']['causado_en'] ?? '-' }}
                            </strong>
                        </div>

                        @if(($detalleHistorial['general']['estado'] ?? '') === 'Reversado')
                            <div class="cc-confirm-row">
                                <span>Reversado por</span>
                                <strong>
                                    {{ $detalleHistorial['general']['reversado_por'] ?? '-' }}
                                    |
                                    {{ $detalleHistorial['general']['reversado_en'] ?? '-' }}
                                </strong>
                            </div>

                            <div class="cc-confirm-row">
                                <span>Motivo de reversión</span>
                                <strong>{{ $detalleHistorial['general']['motivo_reversion'] ?? '-' }}</strong>
                            </div>
                        @endif
                    </div>

                    <div class="cc-total-box">
                        <span>Total</span>
                        <strong>${{ number_format($detalleHistorial['general']['total'] ?? 0, 0, ',', '.') }}</strong>
                    </div>

                    <div style="margin-top:14px; max-height:260px; overflow:auto; border:1px solid #e5e7eb; border-radius:12px;">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Estudiante</th>
                                    <th>Base</th>
                                    <th>Personalizado</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($detalleHistorial['movimientos'] ?? [] as $mov)
                                    <tr>
                                        <td>{{ $mov['documento'] }}</td>
                                        <td>{{ $mov['estudiante'] }}</td>
                                        <td>${{ number_format($mov['valor_base'], 0, ',', '.') }}</td>
                                        <td>${{ number_format($mov['valor_personalizado'], 0, ',', '.') }}</td>
                                        <td>${{ number_format($mov['valor'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="empty-row">
                                            No hay estudiantes registrados en este detalle.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="cc-modal-footer">
                    <button type="button" wire:click="$set('mostrarModalDetalleHistorial', false)" class="cc-btn cc-btn-light">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif



</x-filament-panels::page>