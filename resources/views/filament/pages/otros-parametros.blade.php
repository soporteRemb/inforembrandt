<x-filament-panels::page>

    <style>
        .op-container {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .op-search-card,
        .op-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
        }

        .op-search-card {
            padding: 14px;
        }

        .op-search-input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
        }

        .op-card {
            overflow: hidden;
        }

        .op-card-header {
            padding: 16px 18px;
            border-bottom: 1px solid #e5e7eb;
        }

        .op-card-header h2 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #991b1b;
        }

        .op-card-header p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .op-card-body {
            padding: 14px 18px;
        }

        .op-row {
            display: grid;
            grid-template-columns: 170px 90px 230px 1fr;
            align-items: center;
            gap: 14px;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .op-input-convencion {
            width: 75px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 8px 10px;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
        }

        .op-input-desc {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 14px;
        }

        .op-range {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .op-row:last-child {
            border-bottom: none;
        }

        .op-name strong {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        .op-range {
            justify-content: flex-start;
        }

        .op-range span {
            font-size: 13px;
            color: #64748b;
        }

        .op-input {
            width: 95px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 8px 10px;
            text-align: center;
            font-size: 14px;
        }

        .op-input-name{
            width:170px;
            border:1px solid #d1d5db;
            border-radius:10px;
            padding:8px 12px;
            font-size:14px;
        }

        .op-card-footer {
            padding: 14px 18px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
        }

        .op-btn-save {
            background: #b91c1c;
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
        }

        .op-input::placeholder,
        .op-input-name::placeholder,
        .op-input-desc::placeholder {
            color: #cbd5e1;
            opacity: 1;
        }

        .op-input-convencion {
            width: 75px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 8px 10px;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
        }

        .op-input-convencion::placeholder {
            color: #d7dde7;
            font-weight: 400;
            opacity: 1;
        }

        .op-list-scroll {
            height: 230px;
            max-height: 230px;
            overflow-y: auto;
            padding-right: 6px;
        }

        .op-card-body.op-list-scroll {
            display: block;
        }

        .op-btn-add {
            background: #d5f8e7;
            color: #047857;
            border: 1px solid #84ebaa;
        }

        .op-btn-delete {
            background: #fff1f2;
            color: #b91c1c;
            border: 1px solid #fecdd3;
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
        }

        .op-btn-delete:hover {
            background: #ffe4e6;
        }

        .op-card input[type="checkbox"].op-checkbox {
            appearance: none !important;
            -webkit-appearance: none !important;
            width: 17px;
            height: 17px;
            margin: 0;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            background-color: #ffffff;
            cursor: pointer;
            display: inline-grid;
            place-content: center;
            transition: background-color .15s ease, border-color .15s ease;
        }

        .op-card input[type="checkbox"].op-checkbox:hover {
            border-color: #89e2ab;
        }

        .op-card input[type="checkbox"].op-checkbox:checked {
            background-color: #89e2ab!important;
            border-color: #89e2ab !important;
        }

        .op-card input[type="checkbox"].op-checkbox:checked::before {
            content: "";
            width: 9px;
            height: 5px;
            border-left: 2px solid #ffffff;
            border-bottom: 2px solid #ffffff;
            transform: rotate(-45deg) translateY(-1px);
        }
    </style>

    <div class="op-container">

        <div class="op-search-card">
            <input
                type="text"
                wire:model.live="buscarParametro"
                placeholder="Buscar parámetro..."
                class="op-search-input"
            >
        </div>

        <div class="op-card">

            <div class="op-card-header">
                <h2>Rangos de desempeño académico</h2>
                <p>Configure los rangos utilizados para clasificar las notas.</p>
            </div>

            <div class="op-card-body">
                @foreach($rangos as $index => $rango)
                    <div class="op-row">
                        <div>
                            <input
                                type="text"
                                wire:model="rangos.{{ $index }}.nombre"
                                class="op-input-name"
                                placeholder="Nombre"
                            >
                        </div>

                        <div>
                            <input
                                type="text"
                                wire:model="rangos.{{ $index }}.convencion"
                                class="op-input-convencion"
                                placeholder="Ej.: B"
                                maxlength="10"
                            >
                        </div>

                        <div class="op-range">
                            <input
                                type="number"
                                min="0"
                                max="100"
                                wire:model="rangos.{{ $index }}.desde"
                                class="op-input"
                            >

                            <span>hasta</span>

                            <input
                                type="number"
                                min="0"
                                max="100"
                                wire:model="rangos.{{ $index }}.hasta"
                                class="op-input"
                            >
                        </div>

                        <div>
                            <input
                                type="text"
                                wire:model.live="rangos.{{ $index }}.descripcion_convencion"
                                class="op-input-desc"
                                placeholder="Descripción que aparecerá en el boletín"
                                maxlength="70"
                            >

                            @if(mb_strlen($rangos[$index]['descripcion_convencion'] ?? '') >= 70)
                                <div style="
                                    margin-top:4px;
                                    text-align:right;
                                    font-size:11px;
                                    color:#dc2626;
                                    font-weight:500;
                                ">
                                    Ha alcanzado el máximo de 70 caracteres.
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="op-card-footer">
                <button
                    type="button"
                    wire:click="guardarRangos"
                    class="op-btn-save"
                >
                    Guardar rangos
                </button>
            </div>

        </div>

    </div>







    <div class="op-card">
        <div class="op-card-header">
            <h2>EPS</h2>
            <p>Configure las EPS disponibles para seleccionar en estudiantes.</p>
        </div>

        <div class="op-card-body op-list-scroll">
            @foreach($eps as $index => $item)
                <div class="op-row" style="grid-template-columns: 1fr 130px;">
                    <input
                        type="text"
                        wire:model="eps.{{ $index }}.nombre"
                        class="op-input-desc"
                        placeholder="Nombre de la EPS"
                    >

                    <button
                        type="button"
                        wire:click="eliminarEps({{ $index }})"
                        class="op-btn-delete"
                    >
                        Eliminar
                    </button>
                </div>
            @endforeach
        </div>

        <div class="op-card-footer" style="justify-content: space-between;">
            <button type="button" wire:click="agregarEps" class="op-btn-save op-btn-add">
                Agregar EPS
            </button>

            <button type="button" wire:click="guardarEps" class="op-btn-save">
                Guardar EPS
            </button>
        </div>
    </div>









    <div class="op-card">
        <div class="op-card-header">
            <h2>Jornadas académicas</h2>
            <p>Configure las jornadas disponibles para cursos y boletines.</p>
        </div>

        <div class="op-card-body op-list-scroll">
            @foreach($jornadas as $index => $item)
                <div class="op-row" style="grid-template-columns: 1fr 130px;">
                    <input
                        type="text"
                        wire:model="jornadas.{{ $index }}.nombre"
                        class="op-input-desc"
                        placeholder="Nombre de la jornada"
                    >

                    <button
                        type="button"
                        wire:click="eliminarJornada({{ $index }})"
                        class="op-btn-delete"
                    >
                        Eliminar
                    </button>
                </div>
            @endforeach
        </div>

        <div class="op-card-footer" style="justify-content: space-between;">
            <button type="button" wire:click="agregarJornada" class="op-btn-save op-btn-add">
                Agregar jornada
            </button>

            <button type="button" wire:click="guardarJornadas" class="op-btn-save">
                Guardar jornadas
            </button>
        </div>
    </div>







    <div class="op-card">
        <div class="op-card-header">
            <h2>Límites de pago extemporáneo</h2>
            <p>Configure los tipos que se usarán en las tarifas extemporáneas.</p>
        </div>

        <div class="op-card-body op-list-scroll">
            @foreach($tiposLimite as $index => $item)
                <div class="op-row" style="grid-template-columns: 160px 1fr 130px;">
                    <input
                        type="text"
                        wire:model="tiposLimite.{{ $index }}.codigo"
                        class="op-input-desc"
                        placeholder="Limite 1"
                    >

                    <input
                        type="text"
                        wire:model="tiposLimite.{{ $index }}.nombre"
                        class="op-input-desc"
                        placeholder="Ej.: 30 días"
                    >

                    

                    <button
                        type="button"
                        wire:click="eliminarTipoLimite({{ $index }})"
                        class="op-btn-delete"
                    >
                        Eliminar
                    </button>
                </div>
            @endforeach
        </div>

        <div class="op-card-footer" style="justify-content: space-between;">
            <button type="button" wire:click="agregarTipoLimite" class="op-btn-save op-btn-add">
                Agregar límite
            </button>

            <button type="button" wire:click="guardarTiposLimite" class="op-btn-save">
                Guardar límites
            </button>
        </div>
    </div>


    <div class="op-card">
        <div class="op-card-header">
            <h2>Formas de pago</h2>
            <p>Configure las opciones disponibles al registrar pagos de estudiantes.</p>
        </div>

        <div class="op-card-body op-list-scroll">
            @foreach($formasPago as $index => $item)
                <div class="op-row" style="grid-template-columns: minmax(180px, 1fr) 150px 175px 100px 120px;">
                    <input
                        type="text"
                        wire:model="formasPago.{{ $index }}.nombre"
                        class="op-input-desc"
                        placeholder="Ej.: Efectivo"
                    >

                    <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#475569;">
                        <input
                            type="checkbox"
                            class="op-checkbox"
                            wire:model="formasPago.{{ $index }}.requiere_referencia"
                        >
                        Referencia
                    </label>

                    <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#475569;">
                        <input
                            type="checkbox"
                            class="op-checkbox"
                            wire:model="formasPago.{{ $index }}.requiere_fecha_consignacion"
                        >
                        Fecha consignación
                    </label>

                    <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#475569;">
                        <input
                            type="checkbox"
                            class="op-checkbox"
                            wire:model="formasPago.{{ $index }}.activo"
                        >
                        Activa
                    </label>

                    <button
                        type="button"
                        wire:click="eliminarFormaPago({{ $index }})"
                        class="op-btn-delete"
                    >
                        Eliminar
                    </button>
                </div>
            @endforeach
        </div>

        <div class="op-card-footer" style="justify-content: space-between;">
            <button type="button" wire:click="agregarFormaPago" class="op-btn-save op-btn-add">
                Agregar forma
            </button>

            <button type="button" wire:click="guardarFormasPago" class="op-btn-save">
                Guardar formas de pago
            </button>
        </div>
    </div>

</x-filament-panels::page>