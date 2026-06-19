<x-filament-panels::page>
    <style>
        /* =========================
        1. Base
        ========================= */
        .br-page {
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .br-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .br-title {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        .br-muted {
            color: #6b7280;
            font-size: 11px;
        }

        .br-label {
            font-size: 14px;
            font-weight: 500;
            color: #747a88;
            margin-bottom: 4px;
        }

        /* =========================
        2. Controles
        ========================= */
        .br-select,
        .br-textarea {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 7px 32px 7px 9px;
            font-size: 14px;
            background-color: #ffffff;
            color: #111827;
            outline: none;
        }

        .br-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 14px;
        }

        .br-textarea {
            resize: none;
        }

        .br-observaciones-textarea {
            width: 100%;
            min-height: 90px;
            max-height: 90px;
            resize: none;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            line-height: 1.45;
            outline: none;
        }

        .br-code-input {
            width: 58px;
            text-align: center;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 7px;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        /* =========================
        3. Botones
        ========================= */
        .br-actions-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .br-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 37px;
            padding: 0 17px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            transition: .18s;
            cursor: pointer;
        }

        .br-btn-save {
            background: #c61b1b;
            color: #ffffff;
            border: 1px solid #b71515;
        }

        .br-btn-save:hover {
            background: #ad1515;
        }

        .br-btn-preview {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .br-btn-preview:hover {
            background: #e5e7eb;
        }

        .br-btn-pdf {
            background: #dff7e7;
            color: #0b7a3a;
            border: 1px solid #86efac;
        }

        .br-btn-pdf:hover {
            background: #c8f0d6;
        }

        .br-btn-course {
            background: #eef4ff;
            color: #2563eb;
            border: 1px solid #93c5fd;
        }

        .br-btn-course:hover {
            background: #dbeafe;
            border-color: #60a5fa;
        }

        /* =========================
        4. Tablas
        ========================= */
        .br-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
        }

        .br-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #b85353;
            color: #ffffff;
            font-weight: 700;
            padding: 6px 7px;
            text-align: left;
            border-right: 1px solid rgba(255, 255, 255, .18);
            white-space: nowrap;
        }

        .br-table-gray thead th {
            background: #f3f4f6 !important;
            color: #374151 !important;
            border-right: 1px solid #e5e7eb !important;
        }

        .br-table tbody td {
            padding: 5px 7px;
            border-bottom: 1px solid #e5e7eb;
            border-right: none;
            vertical-align: top;
        }

        .br-table tbody tr:hover {
            background: #fafafa;
        }

        .br-table-box {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        /* =========================
        5. Badges / estados
        ========================= */
        .br-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 2px 7px;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.2;
        }

        .br-badge-green {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .br-badge-orange {
            background: #ffedd5;
            color: #9a3412;
            border: 1px solid #fed7aa;
        }

        .br-status-open {
            color: #15803d;
            font-weight: 700;
        }

        .br-status-closed {
            color: #b91c1c;
            font-weight: 700;
        }

        /* =========================
        6. Filtros
        ========================= */
        .br-filters-row {
            display: grid;
            grid-template-columns: 1.2fr 1.5fr 1fr 1fr;
            gap: 12px;
        }

        .br-student-row {
            margin-top: 12px;
            width: 40%;
        }

        @media (max-width: 1200px) {
            .br-student-row {
                width: 100%;
            }
        }

        @media (max-width: 700px) {
            .br-filters-row {
                grid-template-columns: 1fr;
            }
        }

        .required {
            color: #dc2626;
            font-weight: 600;
        }

        .br-select.placeholder {
            color: #9ca3af;
        }

        .br-select option {
            color: #111827;
        }

        .br-select option[value=""] {
            color: #9ca3af;
        }

        /* =========================
        7. Tarjeta estudiante
        ========================= */
        .br-photo {
            width: 60px;
            height: 60px;
            border-radius: 999px;
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
            color: #8b0f1a;
            flex-shrink: 0;
        }

        .br-student-card {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr 1.1fr;
            gap: 22px;
            align-items: center;
        }

        .br-student-main {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .br-student-name {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .br-student-section {
            font-size: 13px;
            color: #374151;
            line-height: 1.55;
        }

        .br-section-title {
            font-size: 12px;
            font-weight: 600;
            color: #991b1b;
            margin-bottom: 4px;
        }

        @media (max-width: 1100px) {
            .br-student-card {
                grid-template-columns: 1fr;
            }
        }

        .br-photo {
            overflow: hidden;
        }

        .br-photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 999px;
        }

        /* =========================
        8. Bloque académico
        ========================= */
        .br-academic-grid {
            display: grid;
            grid-template-columns: 6fr 3fr 3fr;
            gap: 14px;
            align-items: stretch;
        }

        .br-card-notas,
        .br-card-desempenos,
        .br-card-mejoramiento {
            height: 340px;
            display: flex;
            flex-direction: column;
        }

        .br-card-notas .br-table-box,
        .br-card-desempenos .br-table-box,
        .br-card-mejoramiento .br-table-box {
            flex: 1;
            min-height: 0;

            overflow-y: auto;
            overflow-x: hidden;
        }

        .br-card-notas table,
        .br-card-desempenos table,
        .br-card-mejoramiento table {
            width: 100%;
        }

        .br-card-notas td,
        .br-card-desempenos td,
        .br-card-mejoramiento td {
            vertical-align: top;
        }
        @media (max-width: 1200px) {
            .br-academic-grid {
                grid-template-columns: 1fr;
            }
        }

        .br-badge-red {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        } 

        .br-badge-orange {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .br-badge-green {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .br-badge-dark-green {
            background: #bbf7d0;
            color: #166534;
            border: 1px solid #4ade80;
        }


        /* =========================
        9. Configuración final
        ========================= */
        .br-final-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 14px;
            align-items: stretch;
        }

        .br-final-card {
            min-height: 380px;
            display: flex;
            flex-direction: column;
        }

        .br-final-card .br-title {
            margin-bottom: 10px;
        }

        .br-final-card .flex {
            margin-bottom: 12px;
        }

        .br-final-scroll {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }

        .br-registro-box {
            margin-top: 14px;
        }

        @media (max-width: 1200px) {
            .br-final-grid {
                grid-template-columns: 1fr;
            }
        }

        .br-info-row {
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px solid #f1f3f5;
            font-size: 13px;
            align-items: center;
        }

        .br-info-row:last-child {
            border-bottom: none;
        }

        .br-info-label {
            font-weight: 600;
            color: #6b7280;
        }

        .br-info-value {
            color: #111827;
        }

        .br-observaciones-wrapper{
            position: relative;
        }

        .br-observaciones-textarea{
            width: 100%;
            min-height: 90px;
            max-height: 90px;
            resize: none;
            padding: 12px;
            padding-bottom: 28px;
            border: 1px solid #d9dce3;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.45;
        }

        .br-counter{
            position: absolute;
            right: 12px;
            bottom: 8px;
            font-size: 11px;
            color: #9ca3af;
            pointer-events: none;
            user-select: none;
        }

        /* =========================
        10. Avisos
        ========================= */
        .br-note {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            border-radius: 9px;
            padding: 10px 14px;
            font-size: 14px;
        }

        .br-error-text{
            margin-top:4px;
            font-size:12px;
            font-weight:600;
            color:#dc2626;
        }
    </style>

    <div class="br-page space-y-3">

        {{-- Botones superiores --}}
        <div class="flex w-full justify-end mb-3">

            <div class="br-actions-buttons">

                <button wire:click="guardarCambios" class="br-btn br-btn-save">
                    Guardar cambios
                </button>


                <button wire:click="generarPdfEstudiante" class="br-btn br-btn-pdf">
                    Generar PDF
                </button>

                <button
                    type="button"
                    wire:click="generarPdfCurso"
                    wire:loading.attr="disabled"
                    wire:target="generarPdfCurso"
                    class="br-btn br-btn-course"
                >
                    <span wire:loading.remove wire:target="generarPdfCurso">
                        PDF curso
                    </span>

                    <span wire:loading wire:target="generarPdfCurso">
                        Generando...
                    </span>
                </button>

                

            </div>

        </div>

        {{-- Filtros --}}
        <div class="br-card p-3">

            <div class="br-filters-row">

                <div>
                    <div class="br-label">Periodo lectivo <span class="required">*</span></div>
                    <select wire:model.live="periodoLectivoId" class="br-select">
                        @foreach($periodosLectivos as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="br-label">Periodo académico <span class="required">*</span></div>
                    <select wire:model.live="periodoAcademicoId"
                        class="br-select {{ empty($periodoAcademicoId) ? 'placeholder' : '' }}">

                        <option value="">Seleccione una opción</option>

                        @foreach($periodosAcademicos as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <div class="br-label">Grado <span class="required">*</span></div>
                    <select
                        wire:model.live="grado"
                        class="br-select {{ empty($grado) ? 'placeholder' : '' }}">
                        <option value="">Seleccione una opción</option>

                        @foreach($grados as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="br-label">Curso <span class="required">*</span></div>
                    <select
                        wire:model.live="courseId"
                        class="br-select {{ empty($courseId) ? 'placeholder' : '' }}">
                        <option value="">Seleccione una opción</option>

                        @foreach($cursos as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="br-student-row">
                <div class="br-label">Estudiante <span class="required">*</span></div>
                <select
                    wire:model.live="studentId"
                    class="br-select {{ empty($studentId) ? 'placeholder' : '' }}">
                    <option value="">Seleccione una opción</option>

                    @foreach($estudiantes as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>

        </div>
        

        {{-- Resumen estudiante --}}
        <div class="br-card p-3">
            <div class="br-student-card">

                <div class="br-student-main">

                    <div class="br-photo">
                        @if($this->fotoEstudianteUrl)
                            <img src="{{ $this->fotoEstudianteUrl }}" alt="Foto estudiante" class="br-photo-img">
                        @elseif($student)
                            {{ strtoupper(substr($student->primer_nombre, 0, 1)) }}
                            {{ strtoupper(substr($student->primer_apellido, 0, 1)) }}
                        @else
                            --
                        @endif
                    </div>

                    <div>
                        <div class="br-student-name">
                            {{ $student
                                ? trim(
                                    $student->primer_nombre . ' ' .
                                    $student->segundo_nombre . ' ' .
                                    $student->primer_apellido . ' ' .
                                    $student->segundo_apellido
                                )
                                : 'Seleccione un estudiante'
                            }}
                        </div>

                        @if($student)
                            <span class="br-badge br-badge-green">
                                {{ ucfirst($student->estado) }}
                            </span>
                        @endif
                    </div>

                </div>

                <div class="br-student-section">
                    <div class="br-section-title">Datos personales</div>

                    <div>
                        <strong>Documento:</strong>
                        {{ $student->documento ?? '-' }}
                    </div>

                    <div>
                        <strong>Fecha nacimiento:</strong>
                        {{ $student?->fecha_nacimiento?->format('d/m/Y') ?? '-' }}
                    </div>

                    <div>
                        <strong>Edad:</strong>
                        {{ $student?->fecha_nacimiento
                            ? $student->fecha_nacimiento->age . ' años'
                            : '-' }}
                    </div>
                </div>

                <div class="br-student-section">
                    <div class="br-section-title">Datos académicos</div>

                    <div>
                        <strong>Curso:</strong>
                        {{ $student?->course?->curso ?? '-' }}
                    </div>

                    <div>
                        <strong>Grado:</strong>
                        {{ $student?->course?->grado ?? '-' }}
                    </div>

                    <div>
                        <strong>Jornada:</strong>
                        {{ $student?->course?->jornada ?? '-' }}
                    </div>
                </div>

                <div class="br-student-section">
                    <div class="br-section-title">Periodo consultado</div>

                    <div>
                        <strong>Periodo lectivo:</strong>
                        {{ $periodosLectivos[$periodoLectivoId] ?? '-' }}
                    </div>

                    <div>
                        <strong>Periodo académico:</strong>
                        {{ $periodosAcademicos[$periodoAcademicoId] ?? '-' }}
                    </div>

                    <div>
                        <strong>Estado del período:</strong>

                        @if($periodoAcademicoSeleccionado)
                            <span class="{{ $periodoAcademicoSeleccionado->estado === 'abierto' ? 'br-status-open' : 'br-status-closed' }}">
                                {{ strtoupper($periodoAcademicoSeleccionado->estado) }}
                            </span>
                        @else
                            -
                        @endif
                    </div>

                </div>

            </div>
        </div>



        {{-- Consulta académica --}}
        <div class="br-academic-grid">

            {{-- Tabla notas --}}
            <div class="br-card p-3 br-card-notas">
                <h3 class="br-title mb-2">
                    Desempeño académico <span class="font-normal text-gray-500"></span>
                </h3>

                <div class="br-table-box">
                    <table class="br-table">
                        <thead>
                            <tr>
                                <th>Asig.</th>
                                <th>IH</th>
                                <th>Fallas</th>
                                <th>PGC</th>
                                <th>Nota</th>
                                <th>Desempeño</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($desempenoAcademico as $fila)
                                <tr>
                                    <td>{{ $fila['asignatura'] }}</td>
                                    <td>{{ $fila['ih'] }}</td>
                                    <td>{{ $fila['fallas'] }}</td>
                                    <td>{{ $fila['pgc'] }}</td>
                                    <td>{{ $fila['nota'] }}</td>
                                    <td>
                                        @if($fila['desempeno'] !== '-')

                                            @php
                                                $badge = match (mb_strtoupper($fila['desempeno'])) {
                                                    'BAJO' => 'br-badge-red',
                                                    'BÁSICO', 'BASICO' => 'br-badge-orange',
                                                    'ALTO' => 'br-badge-green',
                                                    'SUPERIOR' => 'br-badge-dark-green',
                                                    default => '',
                                                };
                                            @endphp

                                            <span class="br-badge {{ $badge }}">
                                                {{ $fila['desempeno'] }}
                                            </span>

                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-gray-500 py-4">
                                        Seleccione período académico, grado, curso y estudiante para consultar el desempeño académico.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                
            </div>

            {{-- Desempeños --}}
            <div class="br-card p-3 br-card-desempenos">
                <h3 class="br-title mb-2">
                    Desempeños por asignatura <span class="font-normal text-gray-500"></span>
                </h3>

                <div class="br-table-box">
                    <table class="br-table br-table-gray">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Asig.</th>
                                <th>Desempeños</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($desempenosPorAsignatura as $fila)
                                <tr>
                                    <td class="font-bold">{{ $fila['asignatura'] }}</td>
                                    <td>
                                        @if(count($fila['items']) > 0)
                                            @foreach($fila['items'] as $item)
                                                <div>• {{ $item }}</div>
                                            @endforeach
                                        @else
                                            <span class="text-gray-400">Sin desempeños registrados</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-gray-500 py-4">
                                        Seleccione estudiante para consultar desempeños.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>


            {{-- Mejoramiento --}}
            <div class="br-card p-3 br-card-mejoramiento">
                <h3 class="br-title mb-2">
                    Actividades de mejoramiento <span class="font-normal text-gray-500"></span>
                </h3>

                <div class="br-table-box">
                    <table class="br-table br-table-gray">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Asig.</th>
                                <th>Actividad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($actividadesMejoramiento as $actividad)
                                <tr>
                                    <td class="font-bold">
                                        {{ $actividad['asignatura'] }}
                                    </td>

                                    <td>
                                        <strong>{{ $actividad['codigo'] }}</strong>
                                        {{ $actividad['descripcion'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-gray-500 py-4">
                                        No hay actividades de mejoramiento registradas para este estudiante.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Configuración final --}}
        <div class="br-final-grid">

            {{-- Perfil Rembrandtino--}}
            <div class="br-card p-3 br-final-card">
                <h3 class="br-title">Perfil Rembrandtino</h3>
                

                <div class="mb-3 flex gap-2">
                    @foreach([0,1,2,3] as $i)
                        <input
                            type="text"
                            maxlength="3"
                            wire:model.defer="codigosPerfil.{{ $i }}"
                            class="br-code-input"
                        >
                    @endforeach
                </div>

                

                <div class="br-final-scroll">
                    <table class="br-table br-table-gray">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Código</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($catalogoPerfilRembrandtino as $item)
                                <tr>
                                    <td>{{ $item['codigo'] }}</td>
                                    <td>{{ $item['descripcion'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-gray-500 py-4">
                                        No hay códigos de perfil registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Acompañamiento --}}
            <div class="br-card p-3 br-final-card">
                <h3 class="br-title">Acompañamiento Familiar</h3>
                

                <div class="mb-3 flex gap-2">
                    @foreach([0,1,2,3] as $i)
                        <input
                            type="text"
                            maxlength="3"
                            wire:model.defer="codigosAcompanamiento.{{ $i }}"
                            class="br-code-input"
                        >
                    @endforeach
                </div>

                

                <div class="br-final-scroll">
                    <table class="br-table br-table-gray">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Código</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($catalogoAcompanamientoFamiliar as $item)
                                <tr>
                                    <td>{{ $item['codigo'] }}</td>
                                    <td>{{ $item['descripcion'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-gray-500 py-4">
                                        No hay códigos de acompañamiento registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Observaciones + registro --}}
            <div class="br-card p-3 br-final-card">
                <h3 class="br-title">Observaciones</h3>

                <div class="br-observaciones-wrapper">
                    <textarea
                        wire:model.live.debounce.250ms="observaciones"
                        maxlength="68"
                        class="br-observaciones-textarea"
                    ></textarea>
                    @if(strlen($observaciones ?? '') >= 68)
                        <div class="br-error-text">
                            Máximo 68 caracteres permitidos.
                        </div>
                    @endif

                    <div class="br-counter">
                        {{ strlen($observaciones ?? '') }}/68
                    </div>

                </div>

                <div class="br-registro-box rounded-lg border border-gray-200 bg-gray-50 p-3 mt-3">
                    <h4 class="text-sm font-bold text-gray-950 mb-2">Registro</h4>

                    <div class="br-info-row">
                        <div class="br-info-label">Estado</div>
                        <div class="br-info-value">
                            {{ $registroBoletin['estado'] }}
                        </div>
                    </div>

                    <div class="br-info-row">
                        <div class="br-info-label">Creado por</div>
                        <div class="br-info-value">
                            {{ $registroBoletin['creado_por'] }}
                        </div>
                    </div>

                    <div class="br-info-row">
                        <div class="br-info-label">Modificado por</div>
                        <div class="br-info-value">
                            {{ $registroBoletin['modificado_por'] }}
                        </div>
                    </div>

                    <div class="br-info-row">
                        <div class="br-info-label">Última modificación</div>
                        <div class="br-info-value">
                            {{ $registroBoletin['ultima_modificacion'] }}
                        </div>
                    </div>

                    <div class="br-info-row">
                        <div class="br-info-label">Último PDF</div>
                        <div class="br-info-value">
                            {{ $registroBoletin['ultimo_pdf'] }}
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <div class="br-note">
            El boletín se generará con la información académica actual y los códigos seleccionados para el estudiante.
        </div>

    </div>

    @script
    <script>
        $wire.on('abrir-pdf-boletin', (event) => {
            const url = Array.isArray(event) ? event[0] : event;

            if (url) {
                window.open(url, '_blank');
            }
        });
    </script>
    @endscript
</x-filament-panels::page>