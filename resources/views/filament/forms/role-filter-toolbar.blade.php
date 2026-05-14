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

    {{-- Solo marcados --}}
    <label class="flex items-center gap-2 cursor-pointer select-none text-sm font-medium text-gray-700">
        <div onclick="document.getElementById('rp-only').click()" id="rp-toggle-track"
            class="relative w-9 h-5 rounded-full bg-gray-200 transition-colors duration-200 cursor-pointer flex-shrink-0">
            <span id="rp-toggle-thumb"
                class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200">
            </span>
        </div>
        <input id="rp-only" type="checkbox" class="sr-only" onchange="rpFilter(); rpToggleUI(this.checked);">
        Solo marcados
    </label>

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
function rpToggleUI(checked) {
    const track = document.getElementById('rp-toggle-track');
    const thumb = document.getElementById('rp-toggle-thumb');
    if (checked) {
        track.classList.replace('bg-gray-200', 'bg-primary-600');
        thumb.style.transform = 'translateX(1rem)';
    } else {
        track.classList.replace('bg-primary-600', 'bg-gray-200');
        thumb.style.transform = '';
    }
}

function rpFilter() {
    const q          = (document.getElementById('rp-search')?.value ?? '').toLowerCase().trim();
    const onlyCheck  = document.getElementById('rp-only')?.checked ?? false;

    // Mostrar/ocultar botón limpiar
    const clearBtn = document.getElementById('rp-clear');
    if (clearBtn) clearBtn.style.display = q ? '' : 'none';

    // Cada sección de Filament
    document.querySelectorAll('.fi-section').forEach(section => {
        const headingText = (section.querySelector('.fi-section-header-heading')?.textContent
            ?? section.querySelector('h3, h2')?.textContent
            ?? '').toLowerCase();

        // Todos los checkboxes de esta sección
        const checkboxes = section.querySelectorAll('input[type="checkbox"]');
        if (!checkboxes.length) return; // sección sin checkboxes, ignorar

        let visible = 0;
        checkboxes.forEach(cb => {
            // Contenedor del item (sube hasta encontrar algo con un solo checkbox)
            let item = cb.parentElement;
            for (let i = 0; i < 4; i++) {
                if (!item || item === section) break;
                if (item.querySelectorAll('input[type="checkbox"]').length === 1) break;
                item = item.parentElement;
            }
            if (!item || item === section) return;

            const labelText = (item.textContent ?? '').toLowerCase().trim();
            const matchQ    = !q           || labelText.includes(q) || headingText.includes(q);
            const matchCk   = !onlyCheck   || cb.checked;

            item.style.display = (matchQ && matchCk) ? '' : 'none';
            if (matchQ && matchCk) visible++;
        });

        // Ocultar sección entera si no hay nada visible
        section.style.display = visible === 0 ? 'none' : '';
    });
}

function rpSetCollapsed(value) {
    document.querySelectorAll('.fi-section').forEach(section => {
        // Intentar con Alpine.$data (API pública)
        try {
            const d = window.Alpine.$data(section);
            if (typeof d.isCollapsed !== 'undefined') { d.isCollapsed = value; return; }
        } catch(e) {}
        // Fallback: buscar el elemento con x-data dentro de la sección
        const xEl = section.querySelector('[x-data]');
        if (xEl) {
            try {
                const d = window.Alpine.$data(xEl);
                if (typeof d.isCollapsed !== 'undefined') { d.isCollapsed = value; return; }
            } catch(e) {}
        }
        // Último fallback: clic en el botón solo si el estado es diferente al deseado
        const btn = section.querySelector('button[type="button"]');
        if (btn) btn.click();
    });
}

function rpExpandAll()  { rpSetCollapsed(false); }
function rpCollapseAll(){ rpSetCollapsed(true);  }
</script>
