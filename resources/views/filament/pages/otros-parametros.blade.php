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
            grid-template-columns: 160px 1fr;
            align-items: center;
            gap: 24px;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
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
                        <div class="op-name">
                            <input
                                type="text"
                                wire:model="rangos.{{ $index }}.nombre"
                                class="op-input-name"
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

</x-filament-panels::page>