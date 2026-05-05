<div style="padding: 4px 0;">
    @if($student)
    <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
        <thead>
            <tr style="border-bottom:2px solid #c1121f;">
                <th style="text-align:left; padding:8px 12px; color:#c1121f;">Grado</th>
                <th style="text-align:left; padding:8px 12px; color:#c1121f;">Año</th>
                <th style="text-align:left; padding:8px 12px; color:#c1121f;">Institución Educativa</th>
            </tr>
        </thead>
        <tbody>
            @php
                $rows = [];

                // Años anteriores en Rembrandt
                $previos = \App\Models\Student::where('documento', $student->documento)
                    ->where('id', '!=', $student->id)
                    ->with(['course', 'periodoLectivo'])
                    ->orderBy('id')
                    ->get();

                foreach ($previos as $prev) {
                    $rows[] = [
                        'grado'      => $prev->course ? ($prev->course->grado . '° - ' . $prev->course->curso) : ($prev->ultimo_grado ?? '-'),
                        'anio'       => $prev->periodoLectivo?->nombre ?? '-',
                        'institucion' => 'Colegio Rembrandt',
                        'rembrandt'  => true,
                    ];
                }

                // Institución anterior
                if (!empty($student->institucion_anterior)) {
                    $rows[] = [
                        'grado'      => $student->ultimo_grado ?? '-',
                        'anio'       => $student->anio_ultimo_grado ?? '-',
                        'institucion' => $student->institucion_anterior,
                        'rembrandt'  => false,
                    ];
                }
            @endphp

            @forelse($rows as $row)
            <tr style="border-bottom:1px solid #e5e7eb; {{ $loop->odd ? 'background:#f9fafb;' : '' }}">
                <td style="padding:8px 12px;">{{ $row['grado'] }}</td>
                <td style="padding:8px 12px;">{{ $row['anio'] }}</td>
                <td style="padding:8px 12px;">
                    {{ $row['institucion'] }}
                    @if($row['rembrandt'])
                        <span style="background:#fef2f2; color:#c1121f; font-size:0.75rem; padding:1px 6px; border-radius:99px; margin-left:4px;">Rembrandt</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="padding:16px 12px; text-align:center; color:#6b7280;">
                    Sin historial académico registrado.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif
</div>
