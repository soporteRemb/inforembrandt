<div wire:ignore class="mb-2 p-4 bg-white rounded-xl border border-gray-200 shadow-sm flex flex-wrap items-center gap-3">

    {{-- Buscador --}}
    <div class="relative flex-1 min-w-48">
        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400" style="z-index:1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
        </span>
        <input
            id="rp-search"
            type="text"
            placeholder="Buscar permiso o módulo..."
            oninput="rpFilter()"
            onkeydown="event.stopPropagation()"
            autocomplete="off"
            style="padding-left:2.25rem"
            class="w-full pr-3 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
        >
        <button
            id="rp-clear"
            onclick="document.getElementById('rp-search').value=''; rpFilter();"
            style="display:none; position:absolute; right:8px; top:50%; transform:translateY(-50%);"
            class="text-gray-400 hover:text-gray-600"
            type="button"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Solo marcados — toggle fuera del label para evitar doble clic --}}
    <div class="flex items-center gap-2 cursor-pointer select-none" onclick="rpToggleOnlyChecked()">
        <div id="rp-toggle-track"
            class="relative w-9 h-5 rounded-full bg-gray-200 transition-colors duration-200 flex-shrink-0">
            <span id="rp-toggle-thumb"
                class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200">
            </span>
        </div>
        <span class="text-sm font-medium text-gray-700">Solo marcados</span>
    </div>
    <input id="rp-only" type="checkbox" class="sr-only" onchange="rpFilter()">

    <div class="flex gap-2 flex-wrap">
        <button type="button" onclick="rpSelectAll()"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-primary-50 text-primary-700 hover:bg-primary-100 border border-primary-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Seleccionar todo
        </button>
        <button type="button" onclick="rpDeselectAll()"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Deseleccionar todo
        </button>
    </div>

    <div class="flex gap-2 ml-auto">
        <button type="button" onclick="rpExpandAll()"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            Expandir todo
        </button>
        <button type="button" onclick="rpCollapseAll()"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
            Colapsar todo
        </button>
    </div>
</div>

<script>
// ── Toggle "Solo marcados" ───────────────────────────────────────────────────
function rpToggleOnlyChecked() {
    const cb    = document.getElementById('rp-only');
    const track = document.getElementById('rp-toggle-track');
    const thumb = document.getElementById('rp-toggle-thumb');

    cb.checked = !cb.checked;

    if (cb.checked) {
        track.style.backgroundColor = 'rgb(var(--color-primary-600, 30 64 175))';
        track.style.background = '#c1121f';
        thumb.style.transform  = 'translateX(1rem)';
    } else {
        track.style.background = '';
        track.classList.add('bg-gray-200');
        thumb.style.transform  = '';
    }

    rpFilter();
}

// ── Filtrar secciones ────────────────────────────────────────────────────────
function rpFilter() {
    const q          = (document.getElementById('rp-search')?.value ?? '').toLowerCase().trim();
    const onlyCheck  = document.getElementById('rp-only')?.checked ?? false;

    // Mostrar/ocultar botón limpiar
    const clearBtn = document.getElementById('rp-clear');
    if (clearBtn) clearBtn.style.display = q ? '' : 'none';

    document.querySelectorAll('.fi-section').forEach(section => {
        const headingText = (
            section.querySelector('.fi-section-header-heading')?.textContent
            ?? section.querySelector('h3, h2')?.textContent
            ?? ''
        ).toLowerCase();

        const checkboxes = section.querySelectorAll('input[type="checkbox"]');
        if (!checkboxes.length) return;

        let visible = 0;
        checkboxes.forEach(cb => {
            // Sube hasta encontrar el contenedor del item individual
            let item = cb.parentElement;
            for (let i = 0; i < 4; i++) {
                if (!item || item === section) break;
                if (item.querySelectorAll('input[type="checkbox"]').length === 1) break;
                item = item.parentElement;
            }
            if (!item || item === section) return;

            const labelText = (item.textContent ?? '').toLowerCase().trim();
            const matchQ    = !q          || labelText.includes(q) || headingText.includes(q);
            const matchCk   = !onlyCheck  || cb.checked;

            item.style.display = (matchQ && matchCk) ? '' : 'none';
            if (matchQ && matchCk) visible++;
        });

        // Ocultar la sección Y su wrapper padre para no dejar espacio vacío
        const wrapper = section.parentElement ?? section;
        wrapper.style.display = visible === 0 ? 'none' : '';
    });
}

// ── Colapsar / Expandir ──────────────────────────────────────────────────────
function rpSetCollapsed(value) {
    document.querySelectorAll('.fi-section').forEach(section => {
        try {
            const d = window.Alpine.$data(section);
            if (typeof d.isCollapsed !== 'undefined') { d.isCollapsed = value; return; }
        } catch(e) {}
        const xEl = section.querySelector('[x-data]');
        if (xEl) {
            try {
                const d = window.Alpine.$data(xEl);
                if (typeof d.isCollapsed !== 'undefined') { d.isCollapsed = value; return; }
            } catch(e) {}
        }
        const btn = section.querySelector('button[type="button"]');
        if (btn) btn.click();
    });
}

function rpExpandAll()  { rpSetCollapsed(false); }
function rpCollapseAll(){ rpSetCollapsed(true);  }

// ── Seleccionar / Deseleccionar ──────────────────────────────────────────────
function rpToggleCheckboxes(check) {
    document.querySelectorAll('.fi-section').forEach(section => {
        const wrapper = section.parentElement ?? section;
        if (wrapper.style.display === 'none') return;

        section.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            let item = cb.parentElement;
            for (let i = 0; i < 4; i++) {
                if (!item || item === section) break;
                if (item.querySelectorAll('input[type="checkbox"]').length === 1) break;
                item = item.parentElement;
            }
            if (item && item.style.display === 'none') return;
            if (cb.checked !== check) cb.click();
        });
    });
}

function rpSelectAll()   { rpToggleCheckboxes(true);  }
function rpDeselectAll() { rpToggleCheckboxes(false); }
</script>
