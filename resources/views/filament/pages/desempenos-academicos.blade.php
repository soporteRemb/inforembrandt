<x-filament-panels::page>
    <style>
        .des-grid {
            display: grid;
            grid-template-columns: 38% 1fr;
            gap: 18px;
        }

        .des-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        }

        .des-card-fixed {
            height: 500px;
            display: flex;
            flex-direction: column;
        }

        .des-avance-body {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .des-avance-table {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            margin-top: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .des-card-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 4px;
        }

        /* Scroll elegante */
        .des-card-body::-webkit-scrollbar {
            width: 6px;
        }

        .des-card-body::-webkit-scrollbar-thumb {
            background: #d0d5dd;
            border-radius: 10px;
        }

        .des-card-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .des-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .des-stats:has(.des-stat:nth-child(3):last-child) {
            grid-template-columns: repeat(3, 1fr);
        }

        .des-stat {
            border-radius:10px;
            padding:8px;
            text-align:center;
            font-weight:500;
            background:#f9fafb;
            color:#344054;
            border:1px solid #eef2f6;
        }

        .des-stat.green { background: #ecfdf3; color: #067647; }
        .des-stat.orange { background: #fff4e5; color: #b54708; }
        .des-stat.red { background: #ffecec; color: #b42318; }

        .des-stat-number {
            font-size:18px;
            font-weight:700;
            line-height:1;
            color:#344054;
        }


        .des-codigos-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 4px;
        }

        .des-codigos-footer {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 12px;
            margin-top: 14px;
        }

        .des-table {
            width: 100%;
            margin-top: 16px;
            border-collapse: collapse;
            font-size: 14px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .des-table th {
            background: #f9fafb;
            color: #475467;
            font-size: 12px;
            text-transform: uppercase;
            text-align: left;
            padding: 12px;
        }

        .des-table td {
            padding: 12px;
            border-top: 1px solid #eef2f6;
        }

        .badge {
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge.green { background: #dcfce7; color: #15803d; }
        .badge.orange { background: #ffedd5; color: #c2410c; }
        .badge.red { background: #fee2e2; color: #b91c1c; }

        .des-input-row {
            display: grid;
            grid-template-columns: 36px 130px 1fr;
            gap: 12px;
            align-items: center;
            padding: 12px;
            border: 1px solid #eef2f6;
            border-radius: 12px;
            margin-top: 12px;
        }

        .des-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fee2e2;
            color: #b91c1c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
        }


        .des-input {
            width: 100%;
            border: 1px solid #d0d5dd;
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 14px;
        }

        
        .des-btn-red,
        .des-btn-add,
        .des-btn-light {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            height: 40px;
            padding: 0 16px;

            font-size: 15px;
            font-weight: 500;
            line-height: 1;

            border-radius: 10px;

            transition: all .15s ease;
            cursor: pointer;
        }


        .des-btn-red {
            background: #c00000;
            color: white;
            border: 1px solid #c00000;
        }

        .des-btn-red:hover {
            background: #a80000;
        }


        .des-btn-add {
            background: #ecfdf3;
            color: #067647;
            border: 1px solid #b7ebc6;
        }

        .des-btn-add:hover {
            background: #dff7e8;
        }

        .des-btn-light {
            background: white;
            color: #344054;
            border: 1px solid #d0d5dd;
        }

        .des-btn-light:hover {
            background: #f9fafb;
        }


        .modal-title {
            font-size: 28px;
            font-weight: 600;
        }

        .modal-label {
            font-size: 15px;
            font-weight: 400;
            color: #475467;
        }

        .modal-input {
            font-size: 16px;
            font-weight: 400;
            color: #344054;
        }

        .modal-btn {
            font-size: 15px;
            font-weight: 500;
        }

        .des-row-active {
            background: #fff1f2;
            border-left: 4px solid #b42318;
        }

        .des-row-active td:first-child {
            color: #8f1d18;
            font-weight: 600;
        }

        .des-row {
            cursor: pointer;
            transition: all .15s ease;
        }

        .des-row:hover {
            background: #fafafa;
            cursor: pointer;
        }

        .des-row:hover td:first-child {
            color: #b42318;
        }

        .des-row:hover .badge {
            transform: scale(1.03);
        }


        .placeholder-claro::placeholder {
            color: #c7ced8;
            opacity: 1;
        }


        .des-input:disabled {
            background: #f8fafc;
            color: #64748b;
            cursor: default;
        }

        .des-btn-red:disabled {
            opacity: .55;
            cursor: default;
        }

        input::placeholder,
        textarea::placeholder {
            color: #9b9b9b;
            opacity: 1;
        }
        

        @media (max-width: 1200px) {
            .des-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div style="display:flex; flex-direction:column; gap:22px;">
        {{ $this->form }}

        @if ($this->periodoAcademicoCerrado)
            <div style="background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;border-radius:12px;padding:12px 16px;font-size:14px;font-weight:500;">
                Periodo académico cerrado. Solo se permite consulta.
            </div>
        @endif

            <div class="des-grid">

                {{-- TARJETA IZQUIERDA --}}
                <section class="des-card des-card-fixed">
                    <h2 style="font-size:16px;font-weight:800;">Avance de configuración</h2>
                    <p style="font-size:13px;color:#667085;">
                        {{ $this->resumenFiltro }}
                    </p>

                    <div class="des-stats">
                        @if ($this->esTipoDesempeno())
                            <div class="des-stat">
                                <div class="des-stat-number">{{ $this->totalAsignaturas }}</div>
                                <div>Asign.</div>
                            </div>

                            <div class="des-stat">
                                <div class="des-stat-number">{{ $this->totalCompletas }}</div>
                                <div>Completas</div>
                            </div>

                            <div class="des-stat">
                                <div class="des-stat-number">{{ $this->totalIncompletas }}</div>
                                <div>Incomp.</div>
                            </div>

                            <div class="des-stat">
                                <div class="des-stat-number">{{ $this->totalPendientes }}</div>
                                <div>Pend.</div>
                            </div>
                        @else
                            <div class="des-stat">
                                <div class="des-stat-number">{{ $this->totalCodigos }}</div>
                                <div>Códigos</div>
                            </div>

                            <div class="des-stat">
                                <div class="des-stat-number">{{ $this->totalCodigosActivos }}</div>
                                <div>Activos</div>
                            </div>

                            <div class="des-stat">
                                <div class="des-stat-number">{{ $this->totalCodigosInactivos }}</div>
                                <div>Inactivos</div>
                            </div>

                            
                        @endif
                    </div>

                    <div class="des-avance-table">
                        @if ($this->esTipoDesempeno())
                            <table class="des-table">
                                <thead>
                                    <tr>
                                        <th>Asignatura</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->asignaturasAvance as $asignatura)
                                        @php($estado = $asignatura['estado'])

                                        <tr
                                            class="des-row {{ ($this->data['pensum_academico_id'] ?? null) == $asignatura['id'] ? 'des-row-active' : '' }}"
                                            wire:click="seleccionarAsignatura({{ $asignatura['id'] }})"
                                        >
                                            <td style="font-weight:500;">{{ $asignatura['nombre'] }}</td>
                                            <td>
                                                @if ($estado === 'completo')
                                                    <span class="badge green">Completo</span>
                                                @elseif ($estado === 'incompleto')
                                                    <span class="badge orange">Incompleto</span>
                                                @else
                                                    <span class="badge red">Pendiente</span>
                                                @endif
                                            </td>
                                            <td style="text-align:right;color:#98a2b3;">›</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <table class="des-table">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th style="width:120px;">Códigos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="des-row des-row-active">
                                        <td style="font-weight:500;">
                                            @if (($this->data['tipo'] ?? '') === 'perfil')
                                                Perfil Rembrandtino
                                            @elseif (($this->data['tipo'] ?? '') === 'acompanamiento')
                                                Acompañamiento familiar
                                            @else
                                                Actividades de Mejoramiento
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge green">
                                                {{ $this->totalCodigos }} códigos
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif
                    </div>
                </section>

                {{-- TARJETA DERECHA --}}
                <section class="des-card des-card-fixed">

                    @if ($this->esTipoDesempeno())

                        <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;">
                            <h2 style="font-size:17px;font-weight:800;">
                                Desempeños{{ $this->asignaturaSeleccionada ? ' - ' . $this->asignaturaSeleccionada : '' }}
                            </h2>

                            <div style="text-align:right;font-size:13px;color:#667085;">
                                <strong>Última modificación:</strong><br>

                                @if ($this->ultimaModificacionNombre && $this->ultimaModificacionFecha)
                                    {{ $this->ultimaModificacionNombre }} · {{ $this->ultimaModificacionFecha }}
                                @else
                                    Sin registros
                                @endif
                            </div>
                        </div>

                        <div class="des-card-body">
                            @for ($i = 0; $i < 4; $i++)
                                <div class="des-input-row">
                                    <div class="des-circle">{{ $i + 1 }}</div>

                                    <label style="font-weight:700;font-size:13px;padding-top:7px;">
                                        Desempeño {{ $i + 1 }} {{ $i === 0 ? '*' : '' }}
                                    </label>

                                    <div>
                                        <input
                                            class="des-input placeholder-claro"
                                            maxlength="{{ $this->limiteCaracteres }}"
                                            placeholder="Escriba el desempeño {{ $i + 1 }}"
                                            wire:model.live="desempenos.{{ $i }}"
                                            @disabled($this->periodoAcademicoCerrado)
                                        >

                                        @if (mb_strlen($this->desempenos[$i] ?? '') >= $this->limiteCaracteres)
                                            <p style="margin-top:4px;font-size:12px;color:#b91c1c;font-weight:700;">
                                                Máximo {{ $this->limiteCaracteres }} caracteres permitidos.
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <div style="display:flex;justify-content:space-between;gap:12px;margin-top:14px;">
                            <button
                                type="button"
                                class="des-btn-red"
                                wire:click="guardarDesempenos"
                                @disabled($this->periodoAcademicoCerrado)
                            >
                                Guardar desempeños
                            </button>

                            
                        </div>

                    @else

                        <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;">
                            <h2 style="font-size:17px;font-weight:800;">
                                Códigos -
                                @if (($data['tipo'] ?? '') === 'perfil')
                                    Perfil Rembrandtino
                                @elseif (($data['tipo'] ?? '') === 'acompanamiento')
                                    Acompañamiento Familiar
                                @else
                                    Actividades de Mejoramiento
                                @endif
                            </h2>

                            <div style="text-align:right;font-size:13px;color:#667085;">
                                <strong>Última modificación:</strong><br>

                                @if ($this->ultimaModificacionNombre && $this->ultimaModificacionFecha)
                                    {{ $this->ultimaModificacionNombre }} · {{ $this->ultimaModificacionFecha }}
                                @else
                                    Sin registros
                                @endif
                            </div>
                        </div>

                        <div class="des-card-body">
                            <div class="des-table-wrap">
                                <table class="des-table">
                                    <thead>
                                        <tr>
                                            <th style="width:90px;">Código</th>
                                            <th>Descripción</th>
                                            <th style="width:90px;">Estado</th>
                                            <th style="width:100px;text-align:right;">Acción</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($this->codigos as $codigo)
                                            <tr>
                                                <td><strong>{{ $codigo['codigo'] }}</strong></td>
                                                <td>{{ $codigo['descripcion'] }}</td>
                                                <td>
                                                    @if ($codigo['activo'])
                                                        <span class="badge green">Activo</span>
                                                    @else
                                                        <span class="badge red">Inactivo</span>
                                                    @endif
                                                </td>
                                                <td style="text-align:right;">
                                                    <button
                                                        type="button"
                                                        class="des-btn-light"
                                                        style="padding:5px 9px;font-size:12px;"
                                                        wire:click="editarCodigo({{ $codigo['id'] }})"
                                                        @disabled($this->periodoAcademicoCerrado)
                                                    >
                                                        Editar
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            
                        </div>

                        <div class="des-codigos-footer">
                            <div style="display:flex;gap:12px;">
                                

                                <button
                                    type="button"
                                    class="des-btn-add"
                                    wire:click="abrirModalCodigo"
                                    @disabled($this->periodoAcademicoCerrado)
                                >
                                    Agregar código
                                </button>

                            </div>
                        </div>

                    @endif

                </section>
            </div>

            <x-filament::modal id="modal-codigo-boletin" width="lg">
                <x-slot name="heading">
                    <span style="font-size:18px;font-weight:600;color:#101828;">
                        {{ $this->codigoModalTitulo }}
                    </span>
                </x-slot>

                <div class="space-y-4">
                    <div>
                        <label style="font-size:14px;font-weight:400;color:#475467;">
                            Código
                        </label>

                        <input
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="3"
                            class="mt-1 w-full rounded-lg border-gray-300"
                            style="font-size:15px;font-weight:400;color:#344054;"
                            wire:model="codigoForm.codigo"
                            placeholder="Sugerido: {{ $this->codigoSugerido }}"
                            oninput="this.value=this.value.replace(/[^0-9]/g,'').substring(0,3)"
                        >
                    </div>

                    <div>
                        <label style="font-size:14px;font-weight:400;color:#475467;">
                            Descripción
                        </label>

                        <textarea
                            class="mt-1 w-full rounded-lg border-gray-300"
                            style="font-size:15px;font-weight:400;color:#344054;"
                            wire:model.live="codigoForm.descripcion"
                            placeholder="Escriba la descripción del código"
                            @if (in_array($this->data['tipo'] ?? '', ['perfil', 'acompanamiento']))
                                maxlength="68"
                            @endif
                        ></textarea>
                        @if (
                            in_array($this->data['tipo'] ?? '', ['perfil', 'acompanamiento'])
                            && mb_strlen($this->codigoForm['descripcion'] ?? '') >= 60
                        )
                            <p style="margin-top:4px;font-size:12px;color:#b91c1c;font-weight:700;">
                                Máximo 68 caracteres permitidos.
                            </p>
                        @endif
                    </div>

                    <div>
                        <label style="font-size:14px;font-weight:400;color:#475467;">
                            Estado
                        </label>

                        <select
                            class="mt-1 w-full rounded-lg border-gray-300"
                            style="font-size:15px;font-weight:400;color:#344054;"
                            wire:model="codigoForm.activo"
                        >
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <x-slot name="footer">
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            class="des-btn-light"
                            x-on:click="$dispatch('close-modal', { id: 'modal-codigo-boletin' })"
                        >
                            Cancelar
                        </button>

                        <button
                            type="button"
                            class="des-btn-red"
                            wire:click="guardarCodigoModal"
                        >
                            Guardar
                        </button>
                    </div>
                </x-slot>
            </x-filament::modal>



            <x-filament::modal id="modal-confirmar-sobrescribir" width="md">
                <x-slot name="heading">
                    Confirmar sobrescritura
                </x-slot>

                <div style="font-size:14px;color:#475467;line-height:1.6;">
                    La asignatura destino ya contiene información registrada.
                    <br>
                    Si continúa, los desempeños existentes serán reemplazados por la información del origen.
                </div>

                <x-slot name="footer">
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            class="des-btn-light"
                            x-on:click="$dispatch('close-modal', { id: 'modal-confirmar-sobrescribir' })"
                        >
                            Cancelar
                        </button>

                        <button
                            type="button"
                            class="des-btn-red"
                            wire:click="confirmarSobrescribirDuplicacion"
                        >
                            Sobrescribir
                        </button>
                    </div>
                </x-slot>
            </x-filament::modal>




</x-filament-panels::page>