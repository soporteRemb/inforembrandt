<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Traits\ScopedBySedePeriodo;
use App\Models\Student;
use App\Models\Sede;
use App\Models\PeriodoLectivo;
use Filament\Forms\Components\Actions as FormActions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\Select as FormSelect;
use Illuminate\Database\Eloquent\Builder;
use App\Models\StudentDocumento;
use Illuminate\Support\HtmlString;
use App\Traits\HasRolePermissions;

class StudentResource extends Resource
{
    use HasRolePermissions;
    use ScopedBySedePeriodo;

    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Estudiantes';
    protected static ?string $modelLabel = 'Estudiante';
    protected static ?string $pluralModelLabel = 'Estudiantes';
    protected static ?string $navigationGroup = 'Académico';
    protected static ?int $navigationSort = 1;

    // ─── Campos de acudiente por tipo ────────────────────────────────────────
    private static function guardianSchema(string $tipo): array
    {
        return [
            TextInput::make("g_{$tipo}_nombre")->label('Nombre')->columnSpanFull(),
            TextInput::make("g_{$tipo}_telefono")->label('Teléfono'),
            Select::make("g_{$tipo}_tipo_documento")
                ->label('Tipo de documento')
                ->options([
                    'CC' => 'Cédula de Ciudadanía',
                    'CE' => 'Cédula Extranjería',
                    'PA' => 'Pasaporte',
                ]),
            TextInput::make("g_{$tipo}_documento")->label('Documento'),
            TextInput::make("g_{$tipo}_lugar_trabajo")->label('Lugar de trabajo'),
            TextInput::make("g_{$tipo}_correo")->label('Correo electrónico')->email()->columnSpanFull(),
            TextInput::make("g_{$tipo}_direccion")->label('Dirección')->columnSpanFull(),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── GRID PRINCIPAL: izquierda (2/3) + derecha (1/3) ──────────────
            Grid::make(['default' => 1, 'xl' => 3])->schema([

                // ═══════════════ COLUMNA IZQUIERDA ═══════════════════════════
                Group::make([

                    // ── Sede y Periodo ─────────────────────────────────────────
                    Section::make()->schema([
                        Grid::make(2)->schema([
                            Select::make('sede_id')
                                ->label('Sede')
                                ->options(\App\Models\Sede::where('activa', true)->pluck('nombre', 'id'))
                                ->default(fn() => session('sede_id'))
                                ->required()
                                ->reactive(),

                            Select::make('periodo_lectivo_id')
                                ->label('Periodo lectivo')
                                ->options(function (callable $get) {
                                    $sedeId = $get('sede_id') ?? session('sede_id');
                                    $query  = PeriodoLectivo::query();
                                    if ($sedeId) $query->where('sede_id', $sedeId);
                                    return $query->orderByDesc('nombre')->pluck('nombre', 'id');
                                })
                                ->default(function () {
                                    $sede = session('sede_id');
                                    $anio = session('anio', date('Y'));
                                    return PeriodoLectivo::where('sede_id', $sede)->where('nombre', $anio)->value('id');
                                })
                                ->reactive()
                                ->required(),
                        ]),
                    ])->compact(),

                    // ── Datos del Estudiante ───────────────────────────────────
                    Section::make(function (?Student $record) {
                        $codigo = $record?->codigo ?? '';
                        return new \Illuminate\Support\HtmlString(
                            '<div style="display:flex;align-items:baseline;gap:8px;">'
                            . '<span>Datos del Estudiante</span>'
                            . ($codigo ? '<span style="color:#cbd5e1;margin:0 4px;">·</span><span style="font-size:0.82rem;font-weight:500;color:#94a3b8;">' . e($codigo) . '</span>' : '')
                            . '</div>'
                        );
                    })->schema([
                        // ── Fila superior: Foto (1 col) + 12 campos (4 cols) ──────────
                        Grid::make(['default' => 1, 'lg' => 5])->schema([

                            // Foto + botón cámara (columna 1)
                            Group::make([
                                FileUpload::make('foto')                                  
                                    ->label('')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('students')
                                    ->avatar()
                                    ->imageResizeTargetWidth(600)
                                    ->imageResizeTargetHeight(600)
                                    ->imageResizeMode('cover')
                                    ->imageResizeUpscale(false)
                                    ->columnSpanFull(),
                                Placeholder::make('btn_camara')
                                    ->label('')
                                    ->content(new HtmlString('
                                        <button type="button"
                                            onclick="
                                                var inp = document.querySelector(\'.filepond--browser\');
                                                if(!inp) return;
                                                if(/Android|iPhone|iPad/i.test(navigator.userAgent)){
                                                    inp.setAttribute(\'capture\',\'environment\');
                                                } else {
                                                    inp.removeAttribute(\'capture\');
                                                }
                                                inp.click();
                                            "
                                            style="display:flex;align-items:center;gap:6px;padding:7px 16px;border:1px solid #d1d5db;border-radius:8px;background:white;cursor:pointer;font-size:0.875rem;color:#374151;width:100%;justify-content:center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                                            Tomar foto
                                        </button>
                                    '))
                                    ->columnSpanFull(),
                            ])->columnSpan(1),

                            // 12 campos en 4 columnas (3 filas × 4 cols)
                            Grid::make(4)->schema([
                                TextInput::make('apellidos')->label('Apellidos')->required()->columnSpan(2),
                                TextInput::make('nombres')->label('Nombres')->required()->columnSpan(2),

                                Select::make('tipo_documento')
                                    ->label('Tipo de doc.')
                                    ->options([
                                        'TI' => 'Tarjeta de Identidad',
                                        'CC' => 'Cédula de Ciudadanía',
                                        'RC' => 'Registro Civil',
                                        'CE' => 'Cédula Extranjería',
                                        'PA' => 'Pasaporte',
                                    ])
                                    ->required(),
                                TextInput::make('documento')->label('Documento')->required(),
                                TextInput::make('ciudad_expedicion')->label('C. expedición'),
                                TextInput::make('numero_hermanos')->label('No. Hermanos')->numeric(),

                                Select::make('sexo')
                                    ->options(['Masculino' => 'Masculino', 'Femenino' => 'Femenino'])
                                    ->required(),
                                DatePicker::make('fecha_nacimiento')->label('Fec. nacimiento')->native(false)->displayFormat('d/m/Y'),
                                TextInput::make('edad')->numeric()->required(),
                                TextInput::make('ciudad_nacimiento')->label('C. nacimiento')->required(),
                            ])->columnSpan(['default' => 1, 'lg' => 4]),

                        ]),

                        // ── Fila inferior: correo + rh + eps + teléfono (4 cols, ancho completo) ──
                        Grid::make(4)->schema([
                            TextInput::make('correo')->label('Correo')->email()->required()
                                ->columnSpan(2),
                            TextInput::make('correo_familiar')->label('Familiar')->email(),
                            Select::make('rh')->label('RH')
                                ->options([
                                    'O+' => 'O+', 'O-' => 'O-',
                                    'A+' => 'A+', 'A-' => 'A-',
                                    'B+' => 'B+', 'B-' => 'B-',
                                    'AB+' => 'AB+', 'AB-' => 'AB-',
                                ])
                                ->required(),

                            TextInput::make('eps')->label('EPS')->required(),
                            TextInput::make('telefono_emergencia')->label('Tel. emergencia')->required(),
                            TextInput::make('referido')->label('Referido'),
                            TextInput::make('parentesco_matricula')->label('Parentesco')->required(),
                        ]),

                    ]),

                    // ── Información Académica ───────────────────────────────────
                    Section::make('Información Académica')->schema([
                        Grid::make(5)->schema([
                            Select::make('grado_seleccionado')
                                ->label('Grado')
                                ->options(function (callable $get) {
                                    $sedeId = $get('sede_id') ?? session('sede_id');
                                    return \App\Models\Course::where('sede_id', $sedeId)
                                        ->distinct()->orderBy('grado')
                                        ->pluck('grado', 'grado')
                                        ->mapWithKeys(fn($v, $k) => [$k => $k . '°']);
                                })
                                ->reactive()
                                ->dehydrated(false),

                            Select::make('course_id')
                                ->label('Curso')
                                ->options(function (callable $get) {
                                    $grado  = $get('grado_seleccionado');
                                    $sedeId = $get('sede_id') ?? session('sede_id');
                                    $periodoId = $get('periodo_lectivo_id');
                                    $q = \App\Models\Course::where('sede_id', $sedeId);
                                    if ($periodoId) $q->where('periodo_lectivo_id', $periodoId);
                                    if ($grado) $q->where('grado', $grado);
                                    return $q->pluck('curso', 'id');
                                })
                                ->reactive(),

                            Select::make('estado')
                                ->options(['activo' => 'Activo', 'inactivo' => 'Inactivo', 'retirado' => 'Retirado'])
                                ->default('activo')
                                ->required(),

                            DatePicker::make('fecha_matricula')->label('Fecha matrícula')->native(false)->displayFormat('d/m/Y'),

                            Select::make('control')->label('Control')
                                ->options([
                                    'Estudiante nuevo'   => 'Estudiante nuevo',
                                    'Estudiante antiguo' => 'Estudiante antiguo',
                                    'Transferido'        => 'Transferido',
                                ]),
                        ]),

                        Grid::make(3)->schema([
                            Select::make('ultimo_grado')->label('Último grado cursado')
                                ->options([
                                    'Prejardín' => 'Prejardín', 'Jardín' => 'Jardín', 'Transición' => 'Transición',
                                    '1°' => '1°', '2°' => '2°', '3°' => '3°', '4°' => '4°', '5°' => '5°',
                                    '6°' => '6°', '7°' => '7°', '8°' => '8°', '9°' => '9°', '10°' => '10°', '11°' => '11°',
                                ]),
                            TextInput::make('anio_ultimo_grado')->label('Año'),
                            TextInput::make('institucion_anterior')->label('Institución educativa'),
                        ]),

                        // Botón Historial educativo
                        FormActions::make([
                            FormAction::make('historial_educativo')
                                ->label('Historial educativo')
                                ->icon('heroicon-o-academic-cap')
                                ->color('danger')
                                ->outlined()
                                ->modalHeading('Historial Educativo')
                                ->modalContent(fn(?Student $record) => view(
                                    'filament.modals.historial-educativo',
                                    ['student' => $record]
                                ))
                                ->modalSubmitAction(false)
                                ->modalCancelActionLabel('Cerrar')
                                ->modalWidth('lg')
                                ->hidden(fn(string $operation) => $operation === 'create'),
                        ])->columnSpanFull(),
                    ]),

                    // ── Documentos pendientes + PDFs (izquierda, sin título) ───
                    Section::make()->schema([
                        Placeholder::make('docs_fisicos_card')
                            ->label('')
                            ->content(function (?Student $record) {
                                if (!$record) return new HtmlString('');
                                $url      = url("/students/{$record->id}/documentos-fisicos");
                                $docs     = $record->documentos_fisicos ?? [];
                                $total    = 14;
                                $done     = count($docs);
                                $pendiente = $total - $done;
                                $badge = $pendiente === 0
                                    ? '<span style="background:#bbf7d0;color:#166534;padding:2px 10px;border-radius:99px;font-size:0.75rem;font-weight:700;">✓ Completo</span>'
                                    : '<span style="background:#fca5a5;color:#7f1d1d;padding:2px 10px;border-radius:99px;font-size:0.75rem;font-weight:700;">' . $pendiente . ' pendiente' . ($pendiente !== 1 ? 's' : '') . '</span>';
                                return new HtmlString('
                                    <a href="' . $url . '" target="_blank" style="text-decoration:none;display:block;margin-bottom:8px;">
                                        <div style="display:flex;align-items:center;justify-content:space-between;background:#fff1f2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background=\'#ffe4e6\'" onmouseout="this.style.background=\'#fff1f2\'">
                                            <div style="display:flex;align-items:center;gap:9px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#82211d" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V19.5a2.25 2.25 0 002.25 2.25h.75m0-3.75h3M3.75 18h.008v.008H3.75V18zm0-3h.008v.008H3.75V15zm0-3h.008v.008H3.75V12z"/></svg>
                                                <div>
                                                    <div style="font-size:0.85rem;font-weight:600;color:#82211d;">Documentos pendientes</div>
                                                    <div style="font-size:0.75rem;color:#9f1239;margin-top:1px;">' . $done . ' / ' . $total . ' documentos entregados</div>
                                                </div>
                                            </div>
                                            ' . $badge . '
                                        </div>
                                    </a>
                                ');
                            }),
                        Placeholder::make('btn_pre_matricula')
                            ->label('')
                            ->content(function (?Student $record) {
                                if (!$record) return new HtmlString('');
                                $url = url("/students/{$record->id}/pdf/pre-matricula");
                                $reg = $record->documentos()->where('tipo', 'pre_matricula')->first();
                                $fecha   = $reg ? \Carbon\Carbon::parse($reg->generado_at)->format('d/m/Y') : '';
                                $revisor = $reg?->generado_por ?? '';
                                $infoHtml = $fecha
                                    ? '<span style="font-size:0.78rem;color:#64748b;">Fecha</span>'
                                      . '<span style="font-size:0.78rem;color:#1e293b;font-weight:500;">' . $fecha . '</span>'
                                      . '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#94a3b8" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>'
                                      . ($revisor ? '<span style="font-size:0.75rem;color:#94a3b8;">' . e($revisor) . '</span>' : '')
                                    : '';
                                return new HtmlString('
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                        <a href="' . $url . '" target="_blank" onclick="setTimeout(()=>window.location.reload(),2000)" style="text-decoration:none;">
                                            <button type="button" style="display:flex;align-items:center;gap:5px;background:#dbeafe;color:#1e40af;border:1px solid #93c5fd;border-radius:6px;padding:5px 10px;font-weight:600;font-size:0.78rem;cursor:pointer;white-space:nowrap;">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                Pre matrícula
                                            </button>
                                        </a>
                                        <div style="display:flex;align-items:center;gap:5px;">' . $infoHtml . '</div>
                                    </div>
                                ');
                            }),
                        Placeholder::make('btn_hoja_matricula')
                            ->label('')
                            ->content(function (?Student $record) {
                                if (!$record) return new HtmlString('');
                                $url = url("/students/{$record->id}/pdf/hoja-matricula");
                                $reg = $record->documentos()->where('tipo', 'hoja_matricula')->first();
                                $fecha   = $reg ? \Carbon\Carbon::parse($reg->generado_at)->format('d/m/Y') : '';
                                $revisor = $reg?->generado_por ?? '';
                                $infoHtml = $fecha
                                    ? '<span style="font-size:0.78rem;color:#64748b;">Fecha</span>'
                                      . '<span style="font-size:0.78rem;color:#1e293b;font-weight:500;">' . $fecha . '</span>'
                                      . '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#94a3b8" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>'
                                      . ($revisor ? '<span style="font-size:0.75rem;color:#94a3b8;">' . e($revisor) . '</span>' : '')
                                    : '';
                                return new HtmlString('
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <a href="' . $url . '" target="_blank" onclick="setTimeout(()=>window.location.reload(),2000)" style="text-decoration:none;">
                                            <button type="button" style="display:flex;align-items:center;gap:5px;background:#dcfce7;color:#166534;border:1px solid #86efac;border-radius:6px;padding:5px 10px;font-weight:600;font-size:0.78rem;cursor:pointer;white-space:nowrap;">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                                                Hoja de matrícula
                                            </button>
                                        </a>
                                        <div style="display:flex;align-items:center;gap:5px;">' . $infoHtml . '</div>
                                    </div>
                                ');
                            }),
                    ])->compact()->hidden(fn(string $operation) => $operation === 'create'),

                ])->columnSpan(['xl' => 2]),

                // ═══════════════ COLUMNA DERECHA ═════════════════════════════
                Group::make([

                    // ── Acudientes por tipo ─────────────────────────────────
                    Section::make()->schema([
                        Tabs::make('Acudientes')
                            ->tabs([
                                Tab::make('Padre')
                                    ->schema(static::guardianSchema('padre'))
                                    ->columns(2),
                                Tab::make('Madre')
                                    ->schema(static::guardianSchema('madre'))
                                    ->columns(2),
                                Tab::make('Acudiente')
                                    ->schema(static::guardianSchema('acudiente'))
                                    ->columns(2),
                                Tab::make('Deudor Económico')
                                    ->schema(static::guardianSchema('deudor_economico'))
                                    ->columns(2),
                            ])
                            ->persistTabInQueryString(),
                    ])->compact(),

                    // ── Folio ───────────────────────────────────────────────
                    Section::make()->schema([
                        TextInput::make('folio')
                            ->label('Folio No.')
                            ->disabled()
                            ->placeholder('Se asigna al final del año'),
                    ])->compact(),

                    // ── Formatos de matrícula + Costos (derecha, sin título) ───
                    Section::make()->schema([
                        Placeholder::make('docs_digitales_card')
                            ->label('')
                            ->content(function (?Student $record) {
                                if (!$record) return new HtmlString('');

                                $url = url("/students/{$record->id}/documentos");
                                $todos = \App\Models\StudentDocumento::todos();
                                $entregados = $record->documentos->pluck('tipo')->toArray();
                                $done = count(array_intersect(array_keys($todos), $entregados));
                                $total = count($todos);
                                $pend = $total - $done;

                                $badge = $pend === 0
                                    ? '<span style="background:#bbf7d0;color:#166534;padding:2px 10px;border-radius:99px;font-size:0.75rem;font-weight:700;">✓ Completo</span>'
                                    : '<span style="background:#dcfce7;color:#166534;padding:2px 10px;border-radius:99px;font-size:0.75rem;font-weight:700;">' . $pend . ' pendiente' . ($pend !== 1 ? 's' : '') . '</span>';

                                return new HtmlString('
                                    <a href="' . $url . '" target="_blank" style="text-decoration:none;display:block;margin-bottom:8px;">
                                        <div style="display:flex;align-items:center;justify-content:space-between;background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:12px 16px;cursor:pointer;">
                                            
                                            <div style="display:flex;align-items:center;gap:9px;">
                                                
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    width="18"
                                                    height="18"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="#16a34a"
                                                    stroke-width="1.8">

                                                    <path stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5A3.375 3.375 0 0010.125 2.25H6.75A2.25 2.25 0 004.5 4.5v15A2.25 2.25 0 006.75 21.75h10.5A2.25 2.25 0 0019.5 19.5V18"/>
                                                </svg>

                                                <div>
                                                    <div style="font-size:0.85rem;font-weight:600;color:#166534;">
                                                        Formatos de matrícula
                                                    </div>

                                                    <div style="font-size:0.75rem;color:#16a34a;margin-top:1px;">
                                                        ' . $done . ' / ' . $total . ' formatos generados
                                                    </div>
                                                </div>
                                            </div>

                                            ' . $badge . '
                                        </div>
                                    </a>
                                ');
                            }),

                        Placeholder::make('btn_costos')
                            ->label('')
                            ->content(function (?Student $record) {

                                if (!$record) {
                                    return new HtmlString('');
                                }

                                $url = route('costos.estudiante', $record);

                                return new HtmlString('
                                    <a href="' . $url . '" target="_blank" style="text-decoration:none; width:100%; display:block;">
                                        
                                        <button type="button"
                                            style="
                                                display:flex;
                                                align-items:center;
                                                justify-content:flex-start;
                                                gap:8px;
                                                width:100%;
                                                padding:10px 14px;
                                                background:#d7f3f7;
                                                color:#256b75;
                                                border:1px solid #aee4ec;
                                                border-radius:10px;
                                                font-weight:600;
                                                font-size:0.90rem;
                                                cursor:pointer;
                                                transition:all .2s ease;
                                            "
                                        >
                                            
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke-width="1.8"
                                                stroke="currentColor"
                                                width="16"
                                                height="16">

                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>

                                            Costos
                                        </button>
                                    </a>
                                ');
                            }),
                    ])->compact()->hidden(fn(string $operation) => $operation === 'create'),

                ])->columnSpan(['xl' => 1]),

            ]), // fin Grid principal
        ]);
    }

    // ─── Tabla ───────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')->label('Código')->searchable(),
                TextColumn::make('documento')->searchable(),
                TextColumn::make('primer_apellido')
                    ->label('Apellidos')
                    ->formatStateUsing(fn($record) => $record->primer_apellido . ' ' . $record->segundo_apellido)
                    ->searchable()->sortable(),
                TextColumn::make('primer_nombre')
                    ->label('Nombres')
                    ->formatStateUsing(fn($record) => $record->primer_nombre . ' ' . $record->segundo_nombre)
                    ->searchable()->sortable(),
                TextColumn::make('course.curso')->label('Curso')->sortable(),
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'activo'   => 'success',
                        'inactivo' => 'warning',
                        'retirado' => 'danger',
                        default    => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options(['activo' => 'Activo', 'inactivo' => 'Inactivo', 'retirado' => 'Retirado']),

                Filter::make('grado')
                    ->form([
                        FormSelect::make('grado')->label('Grado')
                            ->options(fn() =>
                                \App\Models\Course::where('sede_id', session('sede_id'))
                                    ->distinct()->orderBy('grado')
                                    ->pluck('grado', 'grado')
                                    ->mapWithKeys(fn($v, $k) => [$k => $k . '°'])
                            )
                            ->placeholder('Todos los grados'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['grado'])) {
                            $query->whereHas('course', fn($q) => $q->where('grado', $data['grado']));
                        }
                    })
                    ->indicateUsing(fn(array $data) => !empty($data['grado']) ? 'Grado: ' . $data['grado'] . '°' : null),

                Filter::make('curso')
                    ->form([
                        FormSelect::make('course_id')->label('Curso')
                            ->options(function () {
                                $sedeId = session('sede_id');
                                $anio   = session('anio', date('Y'));
                                $periodo = \App\Models\PeriodoLectivo::where('sede_id', $sedeId)->where('nombre', $anio)->first();
                                if (!$periodo) return [];
                                return \App\Models\Course::where('sede_id', $sedeId)
                                    ->where('periodo_lectivo_id', $periodo->id)
                                    ->orderBy('curso')
                                    ->get()
                                    ->mapWithKeys(fn($c) => [$c->id => $c->grado . '° - ' . $c->curso]);
                            })
                            ->placeholder('Todos los cursos'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['course_id'])) {
                            $query->where('course_id', $data['course_id']);
                        }
                    })
                    ->indicateUsing(function (array $data) {
                        if (!empty($data['course_id'])) {
                            $curso = \App\Models\Course::find($data['course_id']);
                            return $curso ? 'Curso: ' . $curso->curso : null;
                        }
                        return null;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit'   => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
