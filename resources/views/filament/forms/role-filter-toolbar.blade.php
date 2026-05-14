<div
    x-data="{
        search: '',
        onlyChecked: false,

        init() {
            this.$watch('search',      () => this.applyFilters());
            this.$watch('onlyChecked', () => this.applyFilters());

            // Re-aplicar filtros cada vez que Livewire actualiza el DOM
            document.addEventListener('livewire:updated', () => {
                this.$nextTick(() => this.applyFilters());
            });
        },

        applyFilters() {
            const q           = this.search.toLowerCase().trim();
            const onlyChecked = this.onlyChecked;

            // Cada sección colapsable de Filament
            document.querySelectorAll('.fi-section').forEach(section => {
                const heading = (section.querySelector('.fi-section-header-heading')?.textContent ?? '').toLowerCase();
                const items   = section.querySelectorAll('.fi-fo-checkbox-list-item');

                let visible = 0;

                items.forEach(item => {
                    const label     = (item.querySelector('label, span')?.textContent ?? '').toLowerCase();
                    const checkbox  = item.querySelector('input[type=checkbox]');
                    const isChecked = checkbox?.checked ?? false;

                    const matchSearch  = !q            || label.includes(q) || heading.includes(q);
                    const matchChecked = !onlyChecked  || isChecked;

                    if (matchSearch && matchChecked) {
                        item.style.display = '';
                        visible++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Ocultar la sección completa si no hay nada que mostrar
                section.style.display = (items.length > 0 && visible === 0) ? 'none' : '';
            });
        },

        collapseAll() {
            document.querySelectorAll('.fi-section').forEach(section => {
                // El estado Alpine de cada sección está en su propio x-data
                const alpineEl = section.closest('[x-data]') ?? section;
                try {
                    const data = Alpine.\$data(alpineEl);
                    if (typeof data.isCollapsed !== 'undefined') {
                        data.isCollapsed = true;
                        return;
                    }
                } catch(e) {}
                // Fallback: clic en el botón del header si existe
                const btn = section.querySelector('.fi-section-header button');
                if (btn) btn.click();
            });
        },

        expandAll() {
            document.querySelectorAll('.fi-section').forEach(section => {
                const alpineEl = section.closest('[x-data]') ?? section;
                try {
                    const data = Alpine.\$data(alpineEl);
                    if (typeof data.isCollapsed !== 'undefined') {
                        data.isCollapsed = false;
                        return;
                    }
                } catch(e) {}
                const btn = section.querySelector('.fi-section-header button');
                if (btn) btn.click();
            });
        },
    }"
    class="mb-2 p-4 bg-white rounded-xl border border-gray-200 shadow-sm flex flex-wrap items-center gap-3"
>
    {{-- Buscador --}}
    <div class="relative flex-1 min-w-48">
        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
        </span>
        <input
            x-model="search"
            type="text"
            placeholder="Buscar permiso o módulo..."
            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        >
        <button
            x-show="search !== ''"
            @click="search = ''"
            class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-gray-600"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Solo marcados --}}
    <label class="flex items-center gap-2 cursor-pointer select-none text-sm text-gray-700 font-medium">
        <div
            @click="onlyChecked = !onlyChecked"
            :class="onlyChecked ? 'bg-primary-600' : 'bg-gray-200'"
            class="relative w-9 h-5 rounded-full transition-colors duration-200 cursor-pointer flex-shrink-0"
        >
            <span
                :class="onlyChecked ? 'translate-x-4' : 'translate-x-0.5'"
                class="absolute top-0.5 left-0 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"
            ></span>
        </div>
        Solo marcados
    </label>

    <div class="flex gap-2 ml-auto">
        {{-- Expandir todo --}}
        <button
            type="button"
            @click="expandAll()"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            Expandir todo
        </button>

        {{-- Colapsar todo --}}
        <button
            type="button"
            @click="collapseAll()"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
            Colapsar todo
        </button>
    </div>
</div>
