<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Documentos pendientes — {{ $student->primer_nombre }} {{ $student->primer_apellido }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8fafc; color: #1e293b; }

        .topbar {
            background: #82211d;
            color: white; padding: 14px 32px;
            display: flex; align-items: center; gap: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .topbar .logo { font-size: 1.3rem; font-weight: 800; letter-spacing: -0.5px; }
        .topbar .sub  { font-size: 0.85rem; opacity: 0.85; }

        .container { max-width: 720px; margin: 32px auto; padding: 0 16px; }

        .student-card {
            background: white; border-radius: 12px;
            padding: 18px 24px; margin-bottom: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            display: flex; align-items: center; gap: 20px;
        }
        .student-avatar {
            width: 54px; height: 54px; background: #fecaca; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; font-weight: 700; color: #82211d;
            flex-shrink: 0; overflow: hidden;
        }
        .student-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .student-info h2 { font-size: 1.05rem; font-weight: 700; color: #1e293b; }
        .student-info p  { font-size: 0.83rem; color: #64748b; margin-top: 2px; }
        .student-badge   { margin-left: auto; background: #f1f5f9; padding: 4px 12px; border-radius: 99px; font-size: 0.8rem; color: #475569; font-weight: 600; }

        .section-title {
            font-size: 0.78rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 10px;
        }

        .doc-list { background: white; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); overflow: hidden; }

        .doc-header {
            background: #fecaca; padding: 12px 20px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid #fca5a5;
        }
        .doc-header span { font-weight: 700; color: #991b1b; font-size: 0.92rem; }
        .badge-pend { background: #fca5a5; color: #7f1d1d; padding: 2px 10px; border-radius: 99px; font-size: 0.78rem; font-weight: 700; }
        .badge-done { background: #bbf7d0; color: #166534; padding: 2px 10px; border-radius: 99px; font-size: 0.78rem; font-weight: 700; }

        .doc-row {
            display: flex; align-items: center;
            padding: 13px 20px; border-bottom: 1px solid #f1f5f9;
            gap: 14px; cursor: pointer; transition: background 0.15s;
            user-select: none;
        }
        .doc-row:last-child { border-bottom: none; }
        .doc-row:hover { background: #fafafa; }
        .doc-row.checked { background: #f0fdf4; }

        .checkbox {
            width: 20px; height: 20px; flex-shrink: 0;
            border: 2px solid #cbd5e1; border-radius: 5px;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
        }
        .doc-row.checked .checkbox {
            background: #16a34a; border-color: #16a34a;
        }
        .check-icon { display: none; }
        .doc-row.checked .check-icon { display: block; }

        .doc-label { font-size: 0.9rem; flex: 1; color: #374151; }
        .doc-row.checked .doc-label { color: #16a34a; text-decoration: line-through; opacity: 0.7; }

        .spinner {
            display: none; width: 16px; height: 16px;
            border: 2px solid #94a3b8; border-top-color: transparent;
            border-radius: 50%; animation: spin 0.6s linear infinite;
        }
        .doc-row.loading .spinner { display: block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .progress-bar {
            background: white; border-radius: 12px; padding: 16px 20px;
            margin-bottom: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            display: flex; align-items: center; gap: 14px;
        }
        .progress-track { flex: 1; height: 8px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
        .progress-fill { height: 100%; background: #16a34a; border-radius: 99px; transition: width 0.3s; }
        .progress-text { font-size: 0.83rem; font-weight: 600; color: #475569; white-space: nowrap; }

        .toast {
            position: fixed; bottom: 24px; right: 24px;
            background: #1e293b; color: white;
            padding: 10px 18px; border-radius: 8px;
            font-size: 0.85rem; font-weight: 500;
            opacity: 0; transition: opacity 0.3s; pointer-events: none; z-index: 9999;
        }
        .toast.show { opacity: 1; }
    </style>
</head>
<body>

<div class="topbar">
    <div>
        <div class="logo">Colegio Rembrandt</div>
        <div class="sub">Documentos pendientes de entrega</div>
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
            </p>
        </div>
        <span class="student-badge">Código: {{ $student->codigo }}</span>
    </div>

    {{-- Barra de progreso --}}
    @php $total = count($items); $done = count($checked); @endphp
    <div class="progress-bar">
        <div class="progress-track">
            <div class="progress-fill" id="progress-fill" style="width: {{ $total > 0 ? round($done / $total * 100) : 0 }}%"></div>
        </div>
        <span class="progress-text" id="progress-text">{{ $done }} / {{ $total }} entregados</span>
    </div>

    {{-- Lista --}}
    <div class="section-title">Documentos físicos requeridos</div>

    <div class="doc-list">
        <div class="doc-header">
            <span style="display:flex;align-items:center;gap:7px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="17" height="17"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V19.5a2.25 2.25 0 002.25 2.25h.75m0-3.75h3M3.75 18h.008v.008H3.75V18zm0-3h.008v.008H3.75V15zm0-3h.008v.008H3.75V12z"/></svg>
                Documentos pendientes
            </span>
            <span id="badge-count" class="{{ $done >= $total ? 'badge-done' : 'badge-pend' }}">
                {{ $done >= $total ? '✓ Completo' : ($total - $done) . ' pendiente' . (($total - $done) !== 1 ? 's' : '') }}
            </span>
        </div>

        @foreach($items as $key => $label)
        @php $isChecked = in_array($key, $checked); @endphp
        <div class="doc-row {{ $isChecked ? 'checked' : '' }}"
             id="row-{{ $key }}"
             onclick="toggle('{{ $key }}')">
            <div class="checkbox">
                <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="white" width="12" height="12">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </div>
            <span class="doc-label">{{ $label }}</span>
            <div class="spinner" id="spin-{{ $key }}"></div>
        </div>
        @endforeach
    </div>

</div>

<div class="toast" id="toast"></div>

<script>
const studentId = {{ $student->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const total = {{ $total }};

function updateProgress() {
    const done = document.querySelectorAll('.doc-row.checked').length;
    const pct  = total > 0 ? Math.round(done / total * 100) : 0;
    document.getElementById('progress-fill').style.width = pct + '%';
    document.getElementById('progress-text').textContent  = done + ' / ' + total + ' entregados';
    const badge = document.getElementById('badge-count');
    const pend  = total - done;
    if (pend === 0) {
        badge.textContent = '✓ Completo';
        badge.className = 'badge-done';
    } else {
        badge.textContent = pend + ' pendiente' + (pend !== 1 ? 's' : '');
        badge.className = 'badge-pend';
    }
}

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2200);
}

async function toggle(key) {
    const row  = document.getElementById('row-' + key);
    const spin = document.getElementById('spin-' + key);
    if (row.classList.contains('loading')) return;

    row.classList.add('loading');
    spin.style.display = 'block';

    try {
        const res = await fetch(`/students/${studentId}/documentos-fisicos/${key}/toggle`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
        });
        const data = await res.json();
        if (data.ok) {
            if (data.checked) {
                row.classList.add('checked');
                showToast('✓ Documento marcado como entregado');
            } else {
                row.classList.remove('checked');
                showToast('Documento desmarcado');
            }
            updateProgress();
        }
    } finally {
        row.classList.remove('loading');
        spin.style.display = 'none';
    }
}
</script>
</body>
</html>
