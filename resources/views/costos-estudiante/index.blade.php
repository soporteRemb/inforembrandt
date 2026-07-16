<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <title>
        Costos - {{ $student->primer_nombre }} {{ $student->primer_apellido }}
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="/images/Logo.png?v=3">
    <link rel="shortcut icon" type="image/png" href="/images/Logo.png?v=3">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Inter", "Segoe UI", Arial, Helvetica, sans-serif;
            background: #f6f7f9;
            color: #1f2937;
            font-size: 14px;
        }

        .topbar {
            height: 66px;
            background: linear-gradient(90deg, #8b1d1d 0%, #a00000 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            box-shadow: 0 2px 12px rgba(15, 23, 42, .16);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .brand img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .brand-title {
            font-size: 25px;
            font-weight: 700;
            letter-spacing: .2px;
            line-height: 1;
        }

        .brand-subtitle {
            font-size: 13px;
            margin-top: 7px;
            font-weight: 500;
            opacity: .92;
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        .avatar-user {
            width: 40px;
            height: 40px;
            background: #f59e0b;
            color: white;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-weight: 800;
        }

        .page {
            padding: 14px 28px 24px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e9edf3;
            border-radius: 14px;
            box-shadow:
                0 2px 4px rgba(15, 23, 42, .02),
                0 8px 24px rgba(15, 23, 42, .04);
        }

        .student-card {
            display: grid;
            grid-template-columns: 125px 1fr;
            gap: 18px;
            padding: 12px 24px;
            margin-bottom: 12px;
            align-items: center;
        }

        .student-photo {
            width: 105px;
            height: 112px;
            border-radius: 7px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            background: #f3f4f6;
            box-shadow: 0 8px 20px rgba(0,0,0,.08);
        }

        .student-info {
            display: grid;
            grid-template-columns: 2fr 1.3fr 1.1fr .8fr .8fr;
            gap: 18px 28px;
            align-items: end;
        }

        .field-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: .1px;
        }

        .field-value {
            font-size: 16px;
            font-weight: 650;
            color: #111827;
            letter-spacing: .15px;
        }

        .select-control,
        .input-control,
        .textarea-control {
            width: 100%;
            border: 1px solid #d9dee7;
            border-radius: 7px;
            padding: 5px 9px;
            font-size: 13px;
            background: #fff;
            color: #111827;
            outline: none;
        }

        .select-control:focus,
        .input-control:focus,
        .textarea-control:focus {
            border-color: #b91c1c;
            box-shadow: 0 0 0 3px rgba(185, 28, 28, .08);
        }

        .btn-outline-green {
            height: 40px;
            border: 1px solid #059669;
            color: #047857;
            background: #f0fdf4;
            border-radius: 8px;
            padding: 0 18px;
            font-weight: 650;
            cursor: pointer;
        }

        .columns {
            display: grid;
            grid-template-columns: 1.05fr 1fr 1.15fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .panel {
            padding: 13px 14px;
            min-height: 455px;
        }

        .panel-title {
            color: #991b1b;
            font-size: 17px;
            font-weight: 650;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .row-line {
            display: grid;
            grid-template-columns: 1fr 120px;
            gap: 8px;
            align-items: center;
            margin-bottom: 7px;
        }

        .money-box {
            border: 1px solid #d9dee7;
            border-radius: 7px;
            min-height: 30px;
            padding: 5px 9px;
            text-align: right;
            font-size: 13px;
            background: #fff;
            color: #111827;
        }

        .total-deuda {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding-top: 14px;
            margin-top: 12px;
            border-top: 1px dashed #d1d5db;
        }

        .total-deuda .label {
            font-size: 20px;
            font-weight: 700;
        }

        .total-deuda .small {
            display: block;
            font-size: 11px;
            color: #9ca3af;
            margin-top: 3px;
            font-weight: 400;
        }

        .total-deuda .value {
            font-size: 21px;
            color: #dc2626;
            font-weight: 750;
        }

        .month-list .row-line {
            grid-template-columns: 1fr 135px;
        }

        .green-total {
            margin-top: 8px;
            background: #e9f8ee;
            border: 1px solid #c8efd3;
            color: #047857;
            display: grid;
            grid-template-columns: 1fr 120px;
            gap: 8px;
            align-items: center;
            padding: 7px 10px;
            border-radius: 8px;
            font-weight: 700;
        }

        .red-total {
            margin-top: 10px;
            background: #fff1f1;
            border: 1px solid #fecaca;
            color: #dc2626;
            display: flex;
            justify-content: space-between;
            padding: 9px 11px;
            border-radius: 8px;
            font-weight: 700;
        }

        .search-row {
            display: grid;
            grid-template-columns: 1fr 80px;
            gap: 8px;
            margin-bottom: 10px;
        }

        .mini-table-head {
            display: grid;
            grid-template-columns: 1fr 100px;
            background: #f3f4f6;
            border: 1px solid #d9dde3;
            border-radius: 5px 5px 0 0;
            font-weight: 800;
            font-size: 14px;
        }

        .mini-table-head,
        .mini-table-row {
            display: grid;
            grid-template-columns: 1fr 105px;
            align-items: center;
        }

        .mini-table-head div,
        .mini-table-row div {
            padding: 5px 8px;
        }

        .input-mini-valor {
            width: 82px;
            text-align: right;
        }

        .mini-table-scroll {
            max-height: 270px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .mini-table-row {
            display: grid;
            grid-template-columns: 1fr 100px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }

        .mini-table-row:last-child {
            border-bottom: none;
        }

        .bottom-card {
            padding: 10px 18px 18px;
        }

        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 150px;
            gap: 28px;
            align-items: end;
        }

        .payments-table {
            width: 100%;
            border-collapse: separate;
            font-size: 14px;
            border-spacing: 0;
        }

        .payments-table th,
        .payments-table td {
            border: 1px solid #d9dde3;
            padding: 10px 12px;
        }

        .payments-table th {
            background: #f8fafc;
            text-align: left;
            font-weight: 700;
            color: #334155;
        }

        .btn-save {
            height: 40px;
            border: none;
            background: #b00000;
            color: white;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            width: 130px;
            box-shadow: none;
        }
      
        .card:hover {
            transform: translateY(-1px);
            transition: .18s ease;
        }

        .title-icon {
            width: 18px;
            height: 18px;
            stroke-width: 1.8;
            flex-shrink: 0;
        }

        .textarea-control {
            font-family: "Inter", "Segoe UI", Arial, Helvetica, sans-serif;
            min-height: 78px;
            resize: none;
        }

        .mini-table-scroll {
            max-height: 270px;
            overflow-y: auto;
            border-left: 1px solid #d9dde3;
            border-right: 1px solid #d9dde3;
            border-bottom: 1px solid #d9dde3;
        }

        .payments-wrap {
            max-height: 130px;
            overflow-y: auto;
        }

        .money-input {
            text-align: right;
        }


        .toast-success {
            position: fixed;
            top: 88px;
            right: 32px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #86efac;
            padding: 13px 18px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .16);
            z-index: 9999;
            min-width: 280px;
            text-align: center;
            animation: toastIn .25s ease-out;
        }

        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }


        @media (max-width: 1300px) {
            .columns {
                grid-template-columns: 1fr 1fr;
            }

            .student-info {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        @media (max-width: 800px) {
            .student-card {
                grid-template-columns: 1fr;
            }

            .student-info,
            .columns,
            .bottom-grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                padding: 0 18px;
            }
        }
    </style>
</head>
<body>

@php
    $logo = asset('images/Logo.png');
    $foto = $student->foto
        ? asset('storage/' . $student->foto)
        : null;

    $periodo = $student->periodoLectivo?->nombre ?? date('Y');
    $sede = $student->sede?->nombre ?? 'Rembrandt';
    $usuario = auth()->user()?->name ?? 'Usuario';
    $ficha->load(['pensiones', 'otrosCostos', 'moras']);


    $meses = [
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
    ];
@endphp

<header class="topbar">
    <div class="brand">
        <img src="{{ $logo }}" alt="Logo Rembrandt">
        <div>
            <div class="brand-title">{{ $sede }} {{ $periodo }}</div>
            <div class="brand-subtitle">Costos</div>
        </div>
    </div>

    <div class="user-box">
        <div class="avatar-user">{{ strtoupper(substr($usuario, 0, 1)) }}</div>
        <div>{{ $usuario }}</div>
    </div>
</header>

<main class="page">
    @if(session('success'))
        <div class="toast-success" id="toastSuccess">
            {{ session('success') }}
        </div>
    @endif
    <form method="POST" action="{{ route('costos.estudiante.guardar', $student) }}">
    @csrf
    <section class="card student-card">
        <div>
            @if($foto)
                <img 
                    class="student-photo"
                    src="{{ $foto }}"
                    alt="Foto estudiante"
                    style="
                        width: 95px;
                        height: 95px;
                        border-radius: 50%;
                        object-fit: cover;
                        border: 3px solid #ffffff;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.12);
                    "
                >
            @else
                <div 
                    class="student-photo"
                    style="
                        width: 95px;
                        height: 95px;
                        border-radius: 50%;
                        background: #e5e7eb;
                        border: 3px solid #ffffff;
                    "
                ></div>
            @endif
        </div>

        <div class="student-info">
            <div>
                <div class="field-label">Apellidos y Nombres</div>
                <div class="field-value">
                    {{ strtoupper($student->primer_apellido . ' ' . $student->segundo_apellido . ' ' . $student->primer_nombre . ' ' . $student->segundo_nombre) }}
                </div>
            </div>

            <div>
                <div class="field-label">Documento de identidad</div>
                <div class="field-value">{{ $student->documento }}</div>
            </div>

            <div>
                <div class="field-label">Código asignado</div>
                <div class="field-value">{{ $student->codigo }}</div>
            </div>

            <div>
                <div class="field-label">Grado</div>
                <div class="field-value">{{ $student->course?->grado }}</div>
            </div>

            <div>
                <div class="field-label">Curso</div>
                <div class="field-value">{{ $student->course?->curso }}</div>
            </div>

            <div>
                <div class="field-label">Tipo de pago</div>
                <select class="select-control" name="tipo_pago">
                    <option value="PRIVADO" @selected($ficha->tipo_pago === 'PRIVADO')>PRIVADO</option>
                </select>
            </div>

            <div>
                <div class="field-label">Mes causado</div>
                <div class="field-value">{{ $ficha->mes_causado }}</div>
            </div>

            <div></div>
            <div></div>

            <div>
                <button 
                    type="button"
                    class="btn-outline-green"
                    onclick="if(confirm('¿Desea asignar nuevamente los costos desde la configuración del grado?')) { document.getElementById('formAsignarCostos').submit(); }"
                >
                    Asignar costos
                </button>
            </div>
        </div>
    </section>

    <section class="columns">

        <div class="card panel">
            <div class="panel-title">
                <svg class="title-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                </svg>
                Resumen de cuenta
            </div>

            <div class="row-line">
                <label>Saldo anterior</label>
                <input type="text" name="saldo_anterior" class="input-control money-input"
                    value="{{ number_format($ficha->saldo_anterior ?? 0, 0, ',', '.') }}">
            </div>

            <div class="row-line">
                <label>Matrícula</label>
                <input type="text" name="matricula" class="input-control money-input"
                    value="{{ number_format($ficha->matricula ?? 0, 0, ',', '.') }}">
            </div>

            <div class="row-line">
                <label>Costos académicos</label>
                <input type="text" name="costos_academicos" class="input-control money-input"
                    value="{{ number_format($ficha->costos_academicos ?? 0, 0, ',', '.') }}">
            </div>

            <div class="row-line">
                <label>Deudas</label>
                <input type="text" name="deudas" class="input-control money-input"
                    value="{{ number_format($ficha->deudas ?? 0, 0, ',', '.') }}">
            </div>

            <div class="row-line">
                <label>Otras deudas</label>
                <input
                    type="text"
                    name="otras_deudas"
                    class="input-control money-input"
                    value="{{ number_format($ficha->otras_deudas ?? 0, 0, ',', '.') }}"
                    readonly
                >
            </div>

            <div class="row-line">
                <label>Abonos</label>
                <input type="text" name="abonos" class="input-control money-input"
                    value="{{ number_format($ficha->abonos ?? 0, 0, ',', '.') }}">
            </div>

            <div class="total-deuda">
                <div>
                    <span class="label">Total deuda</span>
                    <span class="small">Total resumen - abonos</span>
                </div>

                <div class="value" id="totalDeudaVista">
                    ${{ number_format($ficha->total_deuda ?? 0, 0, ',', '.') }}
                </div>
            </div>

            <div style="margin-top:14px;">
                <div class="field-label">Observaciones</div>
                <textarea class="textarea-control" name="observaciones" rows="4" placeholder="Ingrese observaciones...">{{ $ficha->observaciones }}</textarea>
            </div>
        </div>

        <div class="card panel">
            <div class="panel-title">
                <svg class="title-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3L3 8l9 5 9-5-9-5zM5 10v5c0 2 3 4 7 4s7-2 7-4v-5"/>
                </svg>
                Costos de Pensión
            </div>

            <div class="month-list">
                @foreach($meses as $numero => $mes)
                    @php
                        $pensionMes = $ficha->pensiones->firstWhere('mes_numero', $numero);
                    @endphp

                    <div class="row-line">
                        <label>
                            {{ $mes }}

                            @if(in_array($numero, $mesesCausados ?? []))
                                <span style="color:#047857; font-weight:700;">
                                    (causado)
                                </span>
                            @endif
                        </label>
                        <input 
                            type="text"
                            name="pensiones[{{ $numero }}]"
                            class="input-control money-input" 
                            value="{{ number_format($pensionMes?->valor_personalizado ?? 0, 0, ',', '.') }}" 
                            style="text-align:right;"
                        >
                    </div>
                @endforeach
            </div>

            <div class="green-total">
                <div>Pensión inicial</div>
                <input type="text" name="pension_inicial" class="input-control money-input"
                    value="{{ number_format($ficha->pension_inicial ?? 0, 0, ',', '.') }}"
                    style="text-align:right; font-weight:700; color:#047857;">
            </div>

            <div class="row-line" style="margin-top:10px;">
                <label>Pagaré No.</label>
                <input class="input-control" name="pagare_no" value="{{ $ficha->pagare_no ?? 0 }}" style="text-align:right;">
            </div>
        </div>

        <div class="card panel">
           <div class="panel-title">
                <svg class="title-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                </svg>
                Otros Costos Académicos
            </div>
            <div style="margin-bottom:8px;">
                <input 
                    id="buscarOtrosCostos"
                    class="input-control" 
                    placeholder="Buscar concepto..."
                >
            </div>

            <div class="mini-table-head">
                <div>Concepto</div>
                <div>Valor</div>
            </div>
            @php
                $otrosCostosFiltrados = $ficha->otrosCostos->filter(function ($otroCosto) {
                    $nombre = \Illuminate\Support\Str::upper(
                        \Illuminate\Support\Str::ascii($otroCosto->nombre_concepto)
                    );

                    return ! str_contains($nombre, 'MATRICULA')
                        && ! str_contains($nombre, 'PENSION')
                        && ! str_contains($nombre, 'COSTOS ACADEMICOS');
                });

                $totalOtrosCostos = $otrosCostosFiltrados->sum('valor_personalizado');
            @endphp

            <div class="mini-table-scroll">
                @forelse($otrosCostosFiltrados as $otroCosto)
                    <div class="mini-table-row fila-otro-costo">
                        <div>
                            {{ $otroCosto->nombre_concepto }}
                        </div>

                        <div>
                            <input
                                type="text"
                                name="otros_costos[{{ $otroCosto->id }}]"
                                class="input-control money-input input-mini-valor input-otro-costo"
                                value="{{ number_format($otroCosto->valor_personalizado, 0, ',', '.') }}"
                            >
                        </div>
                    </div>
                @empty
                    <div class="mini-table-row fila-otro-costo">
                        <div style="grid-column: span 2; text-align:center; color:#6b7280;">
                            No hay otros costos asignados.
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="green-total" id="totalOtrosCostosBox">
                <div>Total otros costos</div>

                <div style="text-align:right;">
                    <span id="totalOtrosCostosValor">
                        ${{ number_format($totalOtrosCostos, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            

            
        </div>

        <div class="card panel">
            <div class="panel-title">
                <svg class="title-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 17L17 7M17 7H9m8 0v8"/>
                </svg>
                Intereses / Penalización por mora
            </div>

            <div class="month-list">
                @foreach($meses as $numero => $mes)
                    <div class="row-line">
                        <label>{{ $mes }}</label>
                        <input class="input-control" value="$0" style="text-align:right;">
                    </div>
                @endforeach
            </div>

            <div class="red-total">
                <span>Total penalización por mora</span>
                <span>$0</span>
            </div>
        </div>

    </section>

    <section class="card bottom-card">
        <div class="panel-title" style="color:#008f5a;">
            <svg class="title-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18v10H3V7zm3 3h4m6 4h2"/>
            </svg>
            Pagos realizados
        </div>

        <div class="bottom-grid">
            <div>

                <div class="payments-wrap">
                    <table class="payments-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>No. de recibo</th>
                                <th>Concepto de pago</th>
                                <th>Valor pagado</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td colspan="4" style="text-align:center;color:#6b7280;">
                                    No hay pagos registrados todavía.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <button class="btn-save">Guardar</button>
        </div>
    </section>

    </form>

    <form 
        id="formAsignarCostos"
        method="POST"
        action="{{ route('costos.estudiante.asignar', $student) }}"
        style="display:none;"
    >
        @csrf
    </form>

</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('buscarOtrosCostos');

        if (!input) {
            return;
        }

        input.addEventListener('input', function () {
            const filtro = this.value.toLowerCase().trim();
            const filas = document.querySelectorAll('.fila-otro-costo');

            filas.forEach(function (fila) {
                const texto = fila.innerText.toLowerCase();

                fila.style.display = texto.includes(filtro) ? 'grid' : 'none';
            });
        });
    });
</script>
<script>
    function soloNumeros(valor) {
        return String(valor || '').replace(/\D/g, '');
    }

    function formatoCOP(valor) {
        const limpio = soloNumeros(valor);
        if (!limpio) return '0';
        return new Intl.NumberFormat('es-CO').format(parseInt(limpio, 10));
    }

    function valorCampo(nombre) {
        const input = document.querySelector(`[name="${nombre}"]`);
        if (!input) return 0;
        return parseInt(soloNumeros(input.value), 10) || 0;
    }

    function totalOtrosCostosActual() {
        let total = 0;

        document.querySelectorAll('.input-otro-costo').forEach(function (input) {
            total += parseInt(soloNumeros(input.value), 10) || 0;
        });

        return total;
    }

    function sincronizarOtrasDeudas() {
        const totalOtros = totalOtrosCostosActual();
        const inputOtrasDeudas = document.querySelector('[name="otras_deudas"]');

        if (inputOtrasDeudas) {
            inputOtrasDeudas.value = formatoCOP(totalOtros);
        }

        return totalOtros;
    }

    function recalcularTotalOtrosCostos() {
        const total = sincronizarOtrasDeudas();
        const totalElement = document.getElementById('totalOtrosCostosValor');

        if (totalElement) {
            totalElement.innerText = '$' + formatoCOP(total);
        }
    }

    function recalcularTotalDeuda() {
        const total =
            valorCampo('saldo_anterior') +
            valorCampo('matricula') +
            valorCampo('costos_academicos') +
            valorCampo('deudas') +
            valorCampo('otras_deudas') -
            valorCampo('abonos');

        const totalVista = document.getElementById('totalDeudaVista');

        if (totalVista) {
            totalVista.innerText = '$' + formatoCOP(Math.max(total, 0));
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.money-input').forEach(function (input) {
            input.value = formatoCOP(input.value);

            input.addEventListener('input', function () {
                input.value = formatoCOP(input.value);

                if (input.classList.contains('input-otro-costo')) {
                    recalcularTotalOtrosCostos();
                }

                recalcularTotalDeuda();
            });
        });

        recalcularTotalOtrosCostos();
        recalcularTotalDeuda();
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toast = document.getElementById('toastSuccess');

        if (toast) {
            setTimeout(function () {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-8px)';
                toast.style.transition = 'all .35s ease';

                setTimeout(function () {
                    toast.remove();
                }, 400);
            }, 2600);
        }
    });
</script>


</body>
</html>