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
            padding: 12px 20px;
            border-bottom: 1px solid #f1f5f9;
            gap: 14px;
            transition: background 0.15s;
        }
        .doc-row:last-child { border-bottom: none; }
        .doc-row:hover { background: #fafafa; }

        .doc-btn {
            border: none; border-radius: 8px;
            padding: 7px 16px;
            font-weight: 600; font-size: 0.83rem;
            cursor: pointer;
            white-space: nowrap;
            transition: opacity 0.15s;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .doc-btn:hover { opacity: 0.85; }
        .doc-btn:disabled { opacity: 0.45; cursor: not-allowed; }

        .doc-meta { margin-left: auto; text-align: right; font-size: 0.78rem; }
        .doc-meta .done  { color: #16a34a; font-weight: 600; }
        .doc-meta .pend  { color: #94a3b8; }
        .doc-meta .who   { color: #64748b; display: block; }

        .all-done {
            padding: 20px;
            text-align: center;
            color: #16a34a;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid currentColor; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; vertical-align: middle; margin-right: 4px; }
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

            @forelse($todos as $tipo => $doc)
            @php $registro = $generados[$tipo] ?? null; @endphp
            <div class="doc-row" id="row-{{ $tipo }}" data-generado="{{ $registro ? 'true' : 'false' }}">
                <button
                    class="doc-btn"
                    style="background:{{ $doc['color'] }}; color:{{ $doc['text'] }}; border:1px solid {{ $doc['border'] }};"
                    onclick="accionDoc('{{ $tipo }}', {{ $doc['pdf'] ? 'true' : 'false' }}, '{{ $doc['label'] }}')"
                    {{ $registro ? 'disabled' : '' }}
                    id="btn-{{ $tipo }}">
                    @if($doc['pdf'])
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14" style="flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14" style="flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V19.5a2.25 2.25 0 002.25 2.25h.75m0-3.75h3M3.75 18h.008v.008H3.75V18zm0-3h.008v.008H3.75V15zm0-3h.008v.008H3.75V12z"/></svg>
                    @endif
                    {{ $doc['label'] }}
                </button>

                <div class="doc-meta">
                    @if($registro)
                        <span class="done">✓ Generado</span>
                        <span class="who">{{ \Carbon\Carbon::parse($registro->generado_at)->format('d/m/Y H:i') }} · {{ $registro->generado_por }}</span>
                    @else
                        <span class="pend">Pendiente</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="all-done">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="20" height="20" style="display:inline;vertical-align:middle;margin-right:6px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Todos los documentos están completos
            </div>
            @endforelse
        </div>
    </div>

</div>

<div class="toast" id="toast"></div>

<script>
const studentId = {{ $student->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

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

async function marcarGenerado(tipo) {
    await fetch(`/students/${studentId}/documentos/${tipo}/marcar`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
    });

    const row = document.getElementById('row-' + tipo);
    const btn = document.getElementById('btn-' + tipo);
    const meta = row.querySelector('.doc-meta');
    const ahora = new Date().toLocaleString('es-CO', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });

    row.dataset.generado = 'true';
    btn.disabled = true;
    meta.innerHTML = `<span class="done">✓ Generado</span><span class="who">${ahora} · {{ auth()->user()->name }}</span>`;

    updateBadge();
    showToast('✓ Documento registrado');
}

async function accionDoc(tipo, esPdf, label) {
    const urls = {
        pre_matricula:  `/students/${studentId}/pdf/pre-matricula`,
        hoja_matricula: `/students/${studentId}/pdf/hoja-matricula`,
    };

    if (esPdf) {
        window.open(urls[tipo], '_blank');
        await marcarGenerado(tipo);
    } else {
        if (!confirm(`¿Marcar "${label}" como entregado?`)) return;
        await marcarGenerado(tipo);
    }
}

// Badge inicial
document.addEventListener('DOMContentLoaded', updateBadge);
</script>
</body>
</html>
