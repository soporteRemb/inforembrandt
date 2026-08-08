<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Documentos — {{ $student->primer_nombre }} {{ $student->primer_apellido }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8fafc; color: #1e293b; }

        .topbar {
            background: #82211d;
            color: white;
            padding: 14px 32px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .topbar .logo { font-size: 1.3rem; font-weight: 800; letter-spacing: -0.5px; }
        .topbar .sub  { font-size: 0.85rem; opacity: 0.85; }

        .container { max-width: 860px; margin: 32px auto; padding: 0 16px; }

        .student-card {
            background: white;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .student-avatar {
            width: 60px; height: 60px;
            background: #fecaca;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 700; color: #c1121f;
            flex-shrink: 0;
            overflow: hidden;
        }
        .student-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .student-info h2 { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
        .student-info p  { font-size: 0.85rem; color: #64748b; margin-top: 2px; }
        .student-badge   { margin-left: auto; background: #f1f5f9; padding: 4px 12px; border-radius: 99px; font-size: 0.8rem; color: #475569; font-weight: 600; }

        .section-title {
            font-size: 0.78rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 10px;
        }

        .doc-list { background: white; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); overflow: hidden; }

        .doc-header {
            background: #fecaca;
            padding: 12px 20px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid #fca5a5;
        }
        .doc-header span { font-weight: 700; color: #991b1b; font-size: 0.92rem; }
        .badge-pend { background: #fca5a5; color: #7f1d1d; padding: 2px 10px; border-radius: 99px; font-size: 0.78rem; font-weight: 700; }

        .doc-row {
            display: flex;
            align-items: center;
            padding: 11px 20px;
            border-bottom: 1px solid #f1f5f9;
            gap: 12px;
        }
        .doc-row:last-child { border-bottom: none; }
        .doc-row:hover { background: #fafafa; }

        /* Botón PDF */
        .doc-btn {
            border: none; border-radius: 7px;
            padding: 6px 14px;
            font-weight: 600; font-size: 0.82rem;
            cursor: pointer;
            white-space: nowrap;
            display: inline-flex; align-items: center; gap: 5px;
            transition: opacity 0.15s;
            text-decoration: none;
        }
        .doc-btn:hover { opacity: 0.82; }

        /* Toggle checkbox */
        .toggle-wrap {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .toggle-btn {
            width: 34px; height: 20px;
            border-radius: 99px;
            border: none;
            cursor: pointer;
            position: relative;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        .toggle-btn::after {
            content: '';
            position: absolute;
            top: 3px; left: 3px;
            width: 14px; height: 14px;
            border-radius: 50%;
            background: white;
            transition: transform 0.2s;
        }
        .toggle-btn.off { background: #cbd5e1; }
        .toggle-btn.on  { background: #16a34a; }
        .toggle-btn.on::after { transform: translateX(14px); }

        .doc-meta { font-size: 0.75rem; text-align: right; }
        .doc-meta .done { color: #16a34a; font-weight: 600; display: block; }
        .doc-meta .who  { color: #94a3b8; display: block; }
        .doc-meta .pend { color: #94a3b8; }

        .spinner { display: inline-block; width: 12px; height: 12px; border: 2px solid currentColor; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .toast {
            position: fixed; bottom: 24px; right: 24px;
            background: #1e293b; color: white;
            padding: 10px 18px; border-radius: 8px;
            font-size: 0.85rem; font-weight: 500;
            opacity: 0; transition: opacity 0.3s;
            pointer-events: none;
            z-index: 9999;
        }
        .toast.show { opacity: 1; }
    </style>
</head>
<body>

<div class="topbar">
    <div>
        <div class="logo">Colegio Rembrandt</div>
        <div class="sub">Gestión de documentos de matrícula</div>
    </div>
    <div style="margin-left:auto; font-size:0.82rem; opacity:0.85;">
        Usuario: {{ auth()->user()->name }}
    </div>
</div>

<div class="container">

    {{-- Tarjeta del estudiante --}}
    <div class="student-card">
        <div class="student-avatar">
            @if($student->foto)
                <img src="{{ asset('storage/' . $student->foto) }}" alt="foto">
            @else
                {{ strtoupper(substr($student->primer_nombre, 0, 1)) }}{{ strtoupper(substr($student->primer_apellido, 0, 1)) }}
            @endif
        </div>
        <div class="student-info">
            <h2>{{ $student->primer_nombre }} {{ $student->segundo_nombre }} {{ $student->primer_apellido }} {{ $student->segundo_apellido }}</h2>
            <p>
                {{ $student->tipo_documento }} {{ $student->documento }}
                &nbsp;·&nbsp; {{ $student->course?->grado }}° — {{ $student->course?->curso }}
                &nbsp;·&nbsp; {{ $student->periodoLectivo?->nombre }}
            </p>
        </div>
        <span class="student-badge">Código: {{ $student->codigo }}</span>
    </div>

    {{-- Lista de documentos --}}
    <div class="section-title">Documentos del proceso de matrícula</div>

    <div class="doc-list">
        <div class="doc-header">
            <span style="display:flex;align-items:center;gap:7px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="17" height="17"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                Documentos pendientes
            </span>
            <span class="badge-pend" id="badge-count"></span>
        </div>

        <div id="doc-rows">
            @php
                $todos      = \App\Models\StudentDocumento::todos();
                $generados  = $student->documentos()->get()->keyBy('tipo');
            @endphp

            @foreach($todos as $tipo => $doc)
            @php $registro = $generados[$tipo] ?? null; @endphp
            <div class="doc-row" id="row-{{ $tipo }}" data-generado="{{ $registro ? 'true' : 'false' }}">

                {{-- Botón que abre el PDF --}}
                <a
                    href="{{ $tipo === 'contrato_servicios'
                        ? route('students.pdf.formato', [
                            'student' => $student,
                            'tipo' => $tipo,
                        ])
                        : asset('formatos/' . rawurlencode($doc['archivo']))
                    }}"
                    target="_blank"
                    class="doc-btn"
                    style="
                        background: {{ $doc['color'] }};
                        color: {{ $doc['text'] }};
                        border: 1px solid {{ $doc['border'] }};
                    "
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        width="13"
                        height="13"
                        style="flex-shrink:0"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"
                        />
                    </svg>

                    {{ $doc['label'] }}
                </a>

                {{-- Toggle + meta --}}
                <div class="toggle-wrap">
                    <div class="doc-meta" id="meta-{{ $tipo }}">
                        @if($registro)
                            <span class="done">✓ Entregado</span>
                            <span class="who">{{ \Carbon\Carbon::parse($registro->generado_at)->format('d/m/Y H:i') }} · {{ $registro->generado_por }}</span>
                        @else
                            <span class="pend">Pendiente</span>
                        @endif
                    </div>
                    <button
                        class="toggle-btn {{ $registro ? 'on' : 'off' }}"
                        id="toggle-{{ $tipo }}"
                        onclick="toggleDoc('{{ $tipo }}')"
                        title="{{ $registro ? 'Desmarcar como entregado' : 'Marcar como entregado' }}">
                    </button>
                </div>

            </div>
            @endforeach
        </div>
    </div>

</div>

<div class="toast" id="toast"></div>

<script>
const studentId = {{ $student->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const userName  = '{{ auth()->user()->name }}';

function updateBadge() {
    const pendientes = document.querySelectorAll('.doc-row[data-generado="false"]').length;
    const badge = document.getElementById('badge-count');
    badge.textContent = pendientes + ' pendiente' + (pendientes !== 1 ? 's' : '');
    badge.style.display = pendientes === 0 ? 'none' : '';
}

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2800);
}

async function toggleDoc(tipo) {
    const toggleBtn = document.getElementById('toggle-' + tipo);
    const row       = document.getElementById('row-' + tipo);
    const meta      = document.getElementById('meta-' + tipo);

    toggleBtn.innerHTML = '<span class="spinner"></span>';
    toggleBtn.disabled  = true;

    try {
        const res  = await fetch(`/students/${studentId}/documentos/${tipo}/toggle`, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
        });
        const data = await res.json();

        if (data.estado === 'marcado') {
            row.dataset.generado  = 'true';
            toggleBtn.className   = 'toggle-btn on';
            const ahora = new Date().toLocaleString('es-CO', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
            meta.innerHTML = `<span class="done">✓ Entregado</span><span class="who">${ahora} · ${userName}</span>`;
            showToast('✓ Marcado como entregado');
        } else {
            row.dataset.generado  = 'false';
            toggleBtn.className   = 'toggle-btn off';
            meta.innerHTML        = '<span class="pend">Pendiente</span>';
            showToast('Desmarcado');
        }
    } catch(e) {
        showToast('Error al guardar');
    }

    toggleBtn.innerHTML = '';
    toggleBtn.disabled  = false;
    updateBadge();
}

document.addEventListener('DOMContentLoaded', updateBadge);
</script>
</body>
</html>
