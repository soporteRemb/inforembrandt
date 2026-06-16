<x-filament-panels::page>
    <style>
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

        .br-label {
            font-size: 14px;
            font-weight: 500;
            color: #747a88;
            margin-bottom: 4px;
        }

        .br-select {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 7px 32px 7px 9px;
            font-size: 14px;
            background-color: #ffffff;
            color: #111827;
            outline: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 14px;
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

        .br-btn-pdf {
            background: #dff7e7;
            color: #0b7a3a;
            border: 1px solid #86efac;
        }

        .br-btn-pdf:hover {
            background: #c8f0d6;
        }

        .br-filters-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 12px;
            align-items: end;
        }

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
            overflow: hidden;
        }

        .br-photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 999px;
        }

        .br-student-card {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr 1fr;
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

        .br-badge-dark-green {
            background: #bbf7d0;
            color: #166534;
            border: 1px solid #4ade80;
        }

        .br-status-open {
            color: #15803d;
            font-weight: 700;
        }

        .br-status-closed {
            color: #b91c1c;
            font-weight: 700;
        }

        .br-academic-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 14px;
            align-items: stretch;
        }

        .br-card-notas,
        .br-card-mejoramiento {
            height: 540px;
            display: flex;
            flex-direction: column;
        }

        .br-table-box {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

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

        .br-table tbody td {
            padding: 5px 7px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .br-table tbody tr:hover {
            background: #fafafa;
        }

        .br-note {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            border-radius: 9px;
            padding: 10px 14px;
            font-size: 14px;
        }

        @media (max-width: 1200px) {
            .br-academic-grid,
            .br-student-card,
            .br-filters-row {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="br-page space-y-3">

            <div class="mb-1 -mt-3">
                <p class="text-sm text-gray-500">
                    Consulta el desempeño académico de tu hijo(a).
                </p>
            </div>

        {{-- Filtros --}}
        <div class="br-card p-3">
            <div class="br-filters-row">

                <div>
                    <div class="br-label">Estudiante</div>
                    <select wire:model.live="studentId" class="br-select">
                        <option value="">Seleccione una opción</option>

                        @foreach($estudiantes as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
            
            
                <div>
                    <div class="br-label">Periodo académico</div>
                    <select wire:model.live="periodoAcademicoId" class="br-select">
                        <option value="">Seleccione una opción</option>

                        @foreach($periodosAcademicos as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

                
                <div>
                    <button
                        type="button"
                        wire:click="descargarPdf"
                        wire:loading.attr="disabled"
                        wire:target="descargarPdf"
                        class="br-btn br-btn-pdf"
                    >
                        <span wire:loading.remove wire:target="descargarPdf">
                            Descargar PDF
                        </span>

                        <span wire:loading wire:target="descargarPdf">
                            Generando...
                        </span>
                    </button>
                </div>

            </div>
        </div>

        {{-- Tarjeta estudiante --}}
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

                

            </div>
        </div>

        {{-- Consulta académica --}}
        <div class="br-academic-grid">

            {{-- Desempeño académico --}}
            <div class="br-card p-3 br-card-notas">
                <h3 class="br-title mb-2">
                    Desempeño académico
                </h3>

                <div class="br-table-box">
                    <table class="br-table">
                        <thead>
                            <tr>
                                <th>Asignatura</th>
                                <th>Intens.H</th>
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
                                        Seleccione periodo académico y estudiante para consultar el desempeño académico.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Actividades de mejoramiento --}}
            <div class="br-card p-3 br-card-mejoramiento">
                <h3 class="br-title mb-2">
                    Actividades de mejoramiento
                </h3>

                <div class="br-table-box">
                    <table class="br-table">
                        <thead>
                            <tr>
                                <th>Asignatura</th>
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

                <div class="br-note mt-3">
                    Las actividades de mejoramiento te ayudarán a seguir fortaleciendo tus aprendizajes.
                </div>
            </div>

        </div>

    </div>

    @script
    <script>
        $wire.on('abrir-pdf-boletin', (event) => {
            const url = Array.isArray(event) ? event[0] : event;

            if (url) {
                const link = document.createElement('a');
                link.href = url;
                link.download = '';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
    </script>
    @endscript
</x-filament-panels::page>