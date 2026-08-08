<x-filament-panels::page.simple>
<style>
    .fi-simple-layout { background: transparent !important; }
    .fi-simple-main-ctn { max-width: 820px !important; width: 95% !important; padding: 0 !important; }
    .fi-simple-main {
        max-width: 820px !important; width: 100% !important;
        background-color: #8f0f12 !important;
        background-image: url('{{ asset('images/colorrojo.png') }}') !important;
        background-size: cover !important; background-position: center !important;
        border-radius: 28px !important; padding: 42px 46px 36px !important;
        box-shadow: 0 26px 65px rgba(0,0,0,0.4) !important;
    }
    body {
        background-image: url('{{ asset('images/fondo.png') }}') !important;
        background-size: cover !important; background-position: center !important;
    }
    .fi-simple-layout .fi-logo { display: none !important; }

    /* Wrapper de input (incluye prefijo) */
    .fi-input-wrp {
        background: rgba(80,0,0,0.5) !important;
        border: 1px solid rgba(255,210,210,0.25) !important;
        border-radius: 12px !important;
        min-height: 56px !important;
    }
    .fi-input-wrp-input { background: transparent !important; border: none !important; }
    .fi-input {
        background: transparent !important;
        border: none !important;
        color: white !important;
        font-size: 1.05rem !important;
        height: 56px !important;
        line-height: 56px !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }
    .fi-input::placeholder { color: rgba(255,255,255,0.6) !important; }
    .fi-input:focus { outline: none !important; }
    .fi-input-wrp:focus-within { border-color: rgba(255,230,0,0.55) !important; }

    /* Prefijo (ícono) */
    .fi-input-wrp-prefix { background: transparent !important; border-right: 1px solid rgba(255,255,255,0.12) !important; padding-right: 10px !important; }
    .fi-input-wrp-prefix svg { color: rgba(255,255,255,0.7) !important; }

    /* Ojo de contraseña */
    .fi-input-wrp .fi-icon-btn { background: transparent !important; }
    .fi-input-wrp .fi-icon-btn svg { color: rgba(255,255,255,0.6) !important; }

    /* Labels — blanco puro (el texto está en el span interno del label) */
    .fi-simple-main .fi-fo-field-wrp-label span { color: #ffffff !important; }
    .fi-fo-field-wrp .fi-fo-field-wrp-label sup { display: none !important; }

    /* Form centrado */
    .fi-simple-main .fi-form { display: flex; flex-direction: column; align-items: stretch; }
    .fi-simple-main .fi-fo-component-ctn { width: 100%; }

    /* Ocultar heading de Filament */
    .fi-simple-main > div > h1,
    .fi-simple-main > div > h2,
    .fi-simple-main .text-2xl,
    .fi-simple-main [class*="heading"] { display: none !important; }

    /* Botón amarillo */
    .fi-simple-main .fi-form-actions button,
    .fi-simple-main [wire\:submit] ~ div button {
        background: linear-gradient(180deg, #ffe100 0%, #f5cb00 100%) !important;
        color: #381606 !important;
        font-weight: 800 !important;
        font-size: 1.15rem !important;
        border-radius: 14px !important;
        height: 60px !important;
        box-shadow: 0 8px 0 rgba(100,60,0,0.35) !important;
        border: none !important;
        width: 100% !important;
        margin-top: 8px !important;
    }
    .fi-simple-main .fi-form-actions button span { color: #381606 !important; }

    /* Dropdowns custom */
    .login-select-btn {
        display: flex; align-items: center; gap: 8px;
        background: rgba(80,0,0,0.5);
        color: white;
        border: 1px solid rgba(255,210,210,0.25);
        border-radius: 10px;
        padding: 9px 13px;
        font-size: 0.92rem;
        cursor: pointer;
        min-width: 110px;
        user-select: none;
        white-space: nowrap;
    }
    .login-select-btn:hover { border-color: rgba(255,230,0,0.4); }
    .login-dropdown-menu {
        position: absolute; top: calc(100% + 5px); left: 0;
        background: #6b0a0d;
        border: 1px solid rgba(255,210,210,0.2);
        border-radius: 10px; overflow: hidden;
        z-index: 999; min-width: 100%;
        box-shadow: 0 8px 24px rgba(0,0,0,0.35);
    }
    .login-dropdown-item {
        padding: 10px 16px; color: rgba(255,255,255,0.9);
        cursor: pointer; font-size: 0.9rem; white-space: nowrap;
    }
    .login-dropdown-item:hover { background: rgba(255,255,255,0.1); }
    .login-dropdown-item.is-selected { background: rgba(255,220,0,0.15); font-weight: 700; color: white; }

    [x-cloak] { display: none !important; }

    /* Mensajes de validación del login de Filament */
    .fi-simple-main .fi-fo-field-wrp-error-message {
        color: #d1c2c2 !important;
    }
</style>

{{-- Header: logos + título + selectores --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:30px;">

    <img src="{{ asset('images/Logo.png') }}" style="width:115px; height:auto; flex-shrink:0;">

    <div style="text-align:center; flex:1; padding:0 18px;">
        <h1 style="color:white; font-size:2.1rem; font-weight:800; margin:0 0 16px; letter-spacing:0.2px;">
            Colegio Rembrandt
        </h1>

        <div style="display:flex; gap:12px; justify-content:center;">

            {{-- Selector de Año --}}
            <div x-data="{
                    open: false,
                    value: '{{ $this->anio }}',
                    years: @js($this->getAnios())
                 }"
                 @click.outside="open = false"
                 style="position:relative;">

                <div class="login-select-btn" @click="open = !open">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    <span x-text="value"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" width="12" height="12" style="opacity:0.6;margin-left:2px"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </div>

                <div class="login-dropdown-menu" x-show="open" x-cloak>
                    <template x-for="y in years" :key="y">
                        <div class="login-dropdown-item"
                             :class="{ 'is-selected': String(y) === String(value) }"
                             @click="value = String(y); $wire.set('anio', String(y)); open = false"
                             x-text="y">
                        </div>
                    </template>
                </div>
            </div>

            {{-- Selector de Sede --}}
            @php
                $sedesData = \App\Models\Sede::where('activa', true)->get(['id','nombre']);
                $sedeActual = $sedesData->firstWhere('id', $this->sede_id);
            @endphp
            <div x-data="{
                    open: false,
                    selectedId: '{{ $this->sede_id }}',
                    selectedName: '{{ $sedeActual?->nombre ?? 'Sede' }}',
                    sedes: {{ $sedesData->map(fn($s) => ['id' => (string)$s->id, 'nombre' => $s->nombre])->values()->toJson() }}
                 }"
                 @click.outside="open = false"
                 style="position:relative;">

                <div class="login-select-btn" @click="open = !open">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/></svg>
                    <span x-text="selectedName"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" width="12" height="12" style="opacity:0.6;margin-left:2px"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </div>

                <div class="login-dropdown-menu" x-show="open" x-cloak>
                    <template x-for="sede in sedes" :key="sede.id">
                        <div class="login-dropdown-item"
                             :class="{ 'is-selected': sede.id === selectedId }"
                             @click="selectedId = sede.id; selectedName = sede.nombre; $wire.set('sede_id', sede.id); open = false"
                             x-text="sede.nombre">
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <img src="{{ asset('images/logo-rembrandt.png') }}" style="width:130px; height:auto; flex-shrink:0;">
</div>

{{-- Formulario --}}
<x-filament-panels::form wire:submit="authenticate">
    {{ $this->form }}
    <x-filament-panels::form.actions
        :actions="$this->getCachedFormActions()"
        :full-width="$this->hasFullWidthFormActions()"
    />
</x-filament-panels::form>

<div style="text-align:center; margin-top:14px;">
    <a href="#" style="color:rgba(255,255,255,0.8); font-size:0.92rem; text-decoration:none;">¿Olvidaste tu contraseña?</a>
</div>

</x-filament-panels::page.simple>
