<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Forms\Get;
use Filament\Forms\Set;


use App\Models\PeriodoLectivo;
use App\Models\PensumAcademico;
use App\Models\BoletinDesempeno;
use App\Models\PeriodoAcademico;
use App\Models\BoletinRecomendacion;

use Illuminate\Support\Facades\Auth;


class DesempenosAcademicos extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationLabel = 'Desempeños';
    protected static ?string $title = 'Desempeños Académicos';
    protected static ?string $navigationGroup = 'Académico';
    protected static ?int $navigationSort = 40;
    protected static string $view = 'filament.pages.desempenos-academicos';


    public ?array $data = [];
    public array $duplicarPendiente = [];


    public string $asignaturaSeleccionada = '';
    public string $resumenFiltro = '';
    public ?string $ultimaModificacionNombre = null;
    public ?string $ultimaModificacionFecha = null;




    public int $limiteCaracteres = 60;


    public int $totalAsignaturas = 0;
    public int $totalCompletas = 0;
    public int $totalIncompletas = 0;
    public int $totalPendientes = 0;
    public int $codigoSugerido = 1;
    public int $totalCodigos = 0;
    public int $totalCodigosActivos = 0;
    public int $totalCodigosInactivos = 0;
    public int $totalAsignaturasConCodigos = 0;


    public bool $periodoAcademicoCerrado = false;
    public bool $destinoDuplicacionCerrado = false;



    public array $desempenos = ['', '', '', ''];

    public array $asignaturasAvance = [];


    public array $codigos = [
        [
            'codigo' => '01',
            'descripcion' => 'Demuestra valores rembrandtinos en su comportamiento diario.',
            'activo' => '1',
        ],
        [
            'codigo' => '02',
            'descripcion' => 'Participa activamente en actividades institucionales.',
            'activo' => true,
        ],
        [
            'codigo' => '03',
            'descripcion' => 'Respeta las normas y a todas las personas de la comunidad educativa.',
            'activo' => true,
        ],
    ];




    public ?int $codigoEditandoId = null;

    public string $codigoModalTitulo = 'Nuevo código';

    public array $codigoForm = [
        'codigo' => '',
        'descripcion' => '',
        'activo' => true,
    ];




    public function mount(): void
    {
        $this->form->fill();
    }

    public function verificarPeriodoAcademicoCerrado(): void
    {
        $periodoLectivoId = $this->data['periodo_lectivo_id'] ?? null;
        $periodoAcademico = $this->data['periodo_academico'] ?? null;

        if (! $periodoLectivoId || ! $periodoAcademico) {
            $this->periodoAcademicoCerrado = false;
            return;
        }

        $this->periodoAcademicoCerrado = PeriodoAcademico::query()
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('numero', $periodoAcademico)
            ->where('estado', 'cerrado')
            ->exists();
    }

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->compact()
                    ->schema([
                        

                        Forms\Components\Select::make('periodo_lectivo_id')
                            ->label('Periodo lectivo')
                            ->options(fn () => PeriodoLectivo::query()
                                ->with('sede')
                                ->orderByDesc('id')
                                ->get()
                                ->mapWithKeys(fn ($periodo) => [
                                    $periodo->id => ($periodo->sede?->nombre ?? 'Sin sede') . ' - ' . $periodo->nombre,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(function () {
                                $sedeId = session('sede_id');
                                $anio = session('anio');

                                if (! $sedeId || ! $anio) {
                                    return null;
                                }

                                return PeriodoLectivo::query()
                                    ->where('sede_id', $sedeId)
                                    ->where('estado', 'abierto')
                                    ->where('nombre', 'like', "%{$anio}%")
                                    ->value('id')
                                    ?? PeriodoLectivo::query()
                                        ->where('sede_id', $sedeId)
                                        ->where('nombre', 'like', "%{$anio}%")
                                        ->value('id');
                            })
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('grado', null);
                                $set('periodo_academico', null);
                                $set('pensum_academico_id', null);

                                $this->asignaturasAvance = [];

                                $this->desempenos = ['', '', '', ''];
                                $this->asignaturaSeleccionada = '';
                                $this->asegurarAsignaturaEspecial();

                                $this->ultimaModificacionNombre = null;
                                $this->ultimaModificacionFecha = null;

                                $this->actualizarResumenFiltro();
                                $this->verificarPeriodoAcademicoCerrado();
                                $this->cargarCodigos();
                                $this->cargarResumenCodigos();
                            }),

                        Forms\Components\Select::make('grado')
                            ->label('Grado')
                            ->options(function (Get $get) {
                                $periodoLectivoId = $get('periodo_lectivo_id');

                                if (! $periodoLectivoId) {
                                    return [];
                                }

                                return PensumAcademico::query()
                                    ->where('periodo_lectivo_id', $periodoLectivoId)
                                    ->whereNotNull('grado')
                                    ->select('grado')
                                    ->distinct()
                                    ->orderBy('grado')
                                    ->pluck('grado', 'grado')
                                    ->toArray();
                            })
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->afterStateUpdated(function (Set $set) {
                                $set('pensum_academico_id', null);

                                $this->asignaturaSeleccionada = '';
                                $this->desempenos = ['', '', '', ''];

                                $this->ultimaModificacionNombre = null;
                                $this->ultimaModificacionFecha = null;

                                $this->cargarAsignaturasAvance();
                                $this->actualizarResumenFiltro();
                                $this->asegurarAsignaturaEspecial();
                                $this->cargarCodigos();
                                $this->cargarResumenCodigos();
                            }),

                        Forms\Components\Select::make('periodo_academico')
                            ->label('Periodo Académico')
                            ->options([
                                '1' => 'Primer periodo',
                                '2' => 'Segundo periodo',
                                '3' => 'Tercer periodo',
                                '4' => 'Cuarto periodo',
                            ])
                            ->native(false)
                            ->live()
                            ->required()
                            ->afterStateUpdated(function () {
                                $this->cargarAsignaturasAvance();
                                $this->cargarDesempenos();
                                $this->actualizarResumenFiltro();
                                $this->verificarPeriodoAcademicoCerrado();
                                $this->cargarCodigos();
                                $this->cargarResumenCodigos();
                            }),

                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'desempeno' => 'Desempeño',
                                'perfil' => 'Perfil Rembrandtino',
                                'acompanamiento' => 'Acompañamiento Familiar',
                                'mejoramiento' => 'Actividades de Mejoramiento',
                            ])
                            ->native(false)
                            ->live()
                            ->default('desempeno')
                            ->required()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if (in_array($state, ['perfil', 'acompanamiento'])) {
                                    $this->asegurarAsignaturaEspecial();
                                } else {
                                    $set('pensum_academico_id', null);
                                    $this->asignaturaSeleccionada = '';
                                }

                                $this->cargarAsignaturasAvance();
                                $this->cargarDesempenos();
                                $this->cargarCodigos();
                                $this->cargarResumenCodigos();
                                $this->actualizarResumenFiltro();
                            }),

                        Forms\Components\Select::make('pensum_academico_id')
                            ->label('Asignatura')
                            ->options(function (Get $get) {
                                $periodoLectivoId = $get('periodo_lectivo_id');
                                $grado = $get('grado');

                                if (! $periodoLectivoId || ! $grado) {
                                    return [];
                                }

                                $query = PensumAcademico::query()
                                    ->where('periodo_lectivo_id', $periodoLectivoId)
                                    ->where('grado', $grado)
                                    ->where('estado', 'activo');

                                if (in_array(($get('tipo') ?? 'desempeno'), ['desempeno', 'mejoramiento'])) {
                                    $query->whereNotIn('nombre', [
                                        'Perfil Rembrandtino',
                                        'Acompañamiento familiar',
                                    ]);
                                }

                                return $query
                                    ->orderBy('orden')
                                    ->orderBy('nombre')
                                    ->pluck('nombre', 'id')
                                    ->toArray();
                            })
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->disabled(fn (Get $get) => in_array($get('tipo'), ['perfil', 'acompanamiento']))
                            ->afterStateUpdated(function ($state) {
                                $this->desempenos = ['', '', '', ''];
                                $this->ultimaModificacionNombre = null;
                                $this->ultimaModificacionFecha = null;

                                $pensum = PensumAcademico::find($state);

                                $this->asignaturaSeleccionada = $pensum?->nombre ?? '';

                                $this->cargarDesempenos();
                                $this->cargarCodigos();
                                $this->cargarResumenCodigos();
                            }),

                        

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('copiar')
                                ->label('Duplicar información')
                                ->color('gray')
                                ->icon('heroicon-o-document-duplicate')
                                ->modalHeading('Duplicar información')
                                ->modalDescription('Seleccione la configuración de origen y el destino donde desea copiar la información.')
                                ->modalSubmitActionLabel('Duplicar')
                                ->modalCancelActionLabel('Cancelar')
                                
                                ->form([
                                    Forms\Components\Section::make('Origen')
                                        ->description('Seleccione de dónde se tomará la información.')
                                        ->extraAttributes([
                                            'style' => 'border:1px solid #cfd4dc; box-shadow:0 2px 8px rgba(16,24,40,.05); border-radius:14px;',
                                        ])
                                        ->schema([
                                            Forms\Components\Select::make('origen_periodo_lectivo_id')
                                                ->label('Periodo lectivo origen')
                                                ->options(fn () => PeriodoLectivo::query()
                                                    ->with('sede')
                                                    ->orderByDesc('nombre')
                                                    ->orderByDesc('id')
                                                    ->get()
                                                    ->mapWithKeys(fn ($periodo) => [
                                                        $periodo->id => ($periodo->sede?->nombre ?? 'Sin sede') . ' - ' . $periodo->nombre,
                                                    ])
                                                    ->toArray())
                                                ->default(function () {
                                                    $sedeId = session('sede_id');
                                                    $anio = session('anio');

                                                    if (! $sedeId || ! $anio) {
                                                        return null;
                                                    }

                                                    return PeriodoLectivo::query()
                                                        ->where('sede_id', $sedeId)
                                                        ->where('estado', 'abierto')
                                                        ->where('nombre', 'like', "%{$anio}%")
                                                        ->value('id')
                                                        ?? PeriodoLectivo::query()
                                                            ->where('sede_id', $sedeId)
                                                            ->where('nombre', 'like', "%{$anio}%")
                                                            ->value('id');
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function (Set $set) {
                                                    $set('origen_grado', null);
                                                    $set('origen_pensum_academico_id', null);
                                                }),

                                            Forms\Components\Select::make('origen_grado')
                                                ->label('Grado origen')
                                                ->options(function (Get $get) {
                                                    $periodoLectivoId = $get('origen_periodo_lectivo_id');

                                                    if (! $periodoLectivoId) {
                                                        return [];
                                                    }

                                                    return PensumAcademico::query()
                                                        ->where('periodo_lectivo_id', $periodoLectivoId)
                                                        ->whereNotNull('grado')
                                                        ->select('grado')
                                                        ->distinct()
                                                        ->orderBy('grado')
                                                        ->pluck('grado', 'grado')
                                                        ->toArray();
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->live()
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Selección obligatoria.',
                                                ])
                                                ->afterStateUpdated(function (Set $set) {
                                                    $set('origen_pensum_academico_id', null);
                                                }),

                                            Forms\Components\Select::make('origen_periodo_academico')
                                                ->label('Periodo origen')
                                                ->options([
                                                    '1' => 'Primer periodo',
                                                    '2' => 'Segundo periodo',
                                                    '3' => 'Tercer periodo',
                                                    '4' => 'Cuarto periodo',
                                                ])
                                                ->native(false)
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Selección obligatoria.',
                                                ]),
                                            
                                            
                                            Forms\Components\Select::make('origen_tipo')
                                                ->label('Tipo origen')
                                                ->options([
                                                    'desempeno' => 'Desempeño',
                                                    'perfil' => 'Perfil Rembrandtino',
                                                    'acompanamiento' => 'Acompañamiento Familiar',
                                                    'mejoramiento' => 'Actividades de Mejoramiento',
                                                ])
                                                ->native(false)
                                                ->default('desempeno')
                                                ->live()
                                                ->afterStateUpdated(function (Set $set) {
                                                    $set('origen_pensum_academico_id', null);
                                                })
                                                ->required(),    

                                            Forms\Components\Select::make('origen_pensum_academico_id')
                                                ->label('Asignatura origen')
                                                ->options(function (Get $get) {
                                                    $periodoLectivoId = $get('origen_periodo_lectivo_id');
                                                    $grado = $get('origen_grado');
                                                    $tipo = $get('origen_tipo');

                                                    if (! $periodoLectivoId || ! $grado) {
                                                        return [];
                                                    }

                                                    $query = PensumAcademico::query()
                                                        ->where('periodo_lectivo_id', $periodoLectivoId)
                                                        ->where('grado', $grado)
                                                        ->where('estado', 'activo');

                                                    if ($tipo === 'perfil') {
                                                        $query->where('nombre', 'Perfil Rembrandtino');
                                                    }

                                                    elseif ($tipo === 'acompanamiento') {
                                                        $query->where('nombre', 'Acompañamiento familiar');
                                                    }

                                                    elseif (in_array($tipo, ['desempeno', 'mejoramiento'])) {
                                                        $query->whereNotIn('nombre', [
                                                            'Perfil Rembrandtino',
                                                            'Acompañamiento familiar',
                                                        ]);
                                                    }

                                                    return $query
                                                        ->orderBy('orden')
                                                        ->orderBy('nombre')
                                                        ->pluck('nombre', 'id')
                                                        ->toArray();
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Selección obligatoria.',
                                                ]),
                                            
                                        ])
                                        ->columns(2),

                                    Forms\Components\Section::make('Destino')
                                        ->description('Seleccione dónde se copiará la información.')
                                       ->extraAttributes([
                                            'style' => 'border:1px solid #cfd4dc; box-shadow:0 2px 8px rgba(16,24,40,.05); border-radius:14px;',
                                        ])
                                        ->schema([
                                            Forms\Components\Select::make('destino_periodo_lectivo_id')
                                                ->label('Periodo lectivo destino')
                                                ->options(fn () => PeriodoLectivo::query()
                                                    ->with('sede')
                                                    ->orderByDesc('nombre')
                                                    ->orderByDesc('id')
                                                    ->get()
                                                    ->mapWithKeys(fn ($periodo) => [
                                                        $periodo->id => ($periodo->sede?->nombre ?? 'Sin sede') . ' - ' . $periodo->nombre,
                                                    ])
                                                    ->toArray())
                                                ->default(function () {
                                                    $sedeId = session('sede_id');
                                                    $anio = session('anio');

                                                    if (! $sedeId || ! $anio) {
                                                        return null;
                                                    }

                                                    return PeriodoLectivo::query()
                                                        ->where('sede_id', $sedeId)
                                                        ->where('estado', 'abierto')
                                                        ->where('nombre', 'like', "%{$anio}%")
                                                        ->value('id')
                                                        ?? PeriodoLectivo::query()
                                                            ->where('sede_id', $sedeId)
                                                            ->where('nombre', 'like', "%{$anio}%")
                                                            ->value('id');
                                                })    
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function (Set $set, Get $get) {
                                                    $set('destino_grado', null);
                                                    $set('destino_pensum_academico_id', null);

                                                    $this->verificarDestinoDuplicacionCerrado(
                                                        $get('destino_periodo_lectivo_id'),
                                                        $get('destino_periodo_academico')
                                                    );
                                                }),

                                            Forms\Components\Select::make('destino_grado')
                                                ->label('Grado destino')
                                                ->options(function (Get $get) {
                                                    $periodoLectivoId = $get('destino_periodo_lectivo_id');

                                                    if (! $periodoLectivoId) {
                                                        return [];
                                                    }

                                                    return PensumAcademico::query()
                                                        ->where('periodo_lectivo_id', $periodoLectivoId)
                                                        ->whereNotNull('grado')
                                                        ->select('grado')
                                                        ->distinct()
                                                        ->orderBy('grado')
                                                        ->pluck('grado', 'grado')
                                                        ->toArray();
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->live()
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Selección obligatoria.',
                                                ])
                                                ->afterStateUpdated(function (Set $set) {
                                                    $set('destino_pensum_academico_id', null);
                                                }),

                                            Forms\Components\Select::make('destino_periodo_academico')
                                                ->label('Periodo destino')
                                                ->options([
                                                    '1' => 'Primer periodo',
                                                    '2' => 'Segundo periodo',
                                                    '3' => 'Tercer periodo',
                                                    '4' => 'Cuarto periodo',
                                                ])
                                                ->native(false)
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Selección obligatoria.',
                                                ])
                                                ->live()
                                                ->afterStateUpdated(function (Get $get) {
                                                    $this->verificarDestinoDuplicacionCerrado(
                                                        $get('destino_periodo_lectivo_id'),
                                                        $get('destino_periodo_academico')
                                                    );
                                                }),

                                            Forms\Components\Select::make('destino_tipo')
                                                ->label('Tipo destino')
                                                ->options([
                                                    'desempeno' => 'Desempeño',
                                                    'perfil' => 'Perfil Rembrandtino',
                                                    'acompanamiento' => 'Acompañamiento Familiar',
                                                    'mejoramiento' => 'Actividades de Mejoramiento',
                                                ])
                                                ->native(false)
                                                ->default('desempeno')
                                                ->live()
                                                ->afterStateUpdated(function (Set $set) {
                                                    $set('destino_pensum_academico_id', null);
                                                })
                                                ->required(), 
                                            
                                            Forms\Components\Placeholder::make('aviso_destino_cerrado')
                                                ->label('')
                                                ->content('⚠ El periodo académico destino está cerrado. No se permitirá realizar la duplicación.')
                                                ->visible(function (Get $get) {
                                                    $periodoLectivoId = $get('destino_periodo_lectivo_id');
                                                    $periodoAcademico = $get('destino_periodo_academico');

                                                    if (! $periodoLectivoId || ! $periodoAcademico) {
                                                        return false;
                                                    }

                                                    return PeriodoAcademico::query()
                                                        ->where('periodo_lectivo_id', $periodoLectivoId)
                                                        ->where('numero', $periodoAcademico)
                                                        ->where('estado', 'cerrado')
                                                        ->exists();
                                                })
                                                ->extraAttributes([
                                                    'style' => 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:12px;padding:12px 14px;font-weight:700;',
                                                ])
                                                ->columnSpanFull(),

                                            Forms\Components\Select::make('destino_pensum_academico_id')
                                                ->label('Asignatura destino')
                                                ->options(function (Get $get) {
                                                    $periodoLectivoId = $get('destino_periodo_lectivo_id');
                                                    $grado = $get('destino_grado');
                                                    $tipo = $get('destino_tipo') ?? 'desempeno';

                                                    if (! $periodoLectivoId || ! $grado) {
                                                        return [];
                                                    }

                                                    $query = PensumAcademico::query()
                                                        ->where('periodo_lectivo_id', $periodoLectivoId)
                                                        ->where('grado', $grado)
                                                        ->where('estado', 'activo');

                                                    if ($tipo === 'perfil') {
                                                        $query->where('nombre', 'Perfil Rembrandtino');
                                                    } elseif ($tipo === 'acompanamiento') {
                                                        $query->where('nombre', 'Acompañamiento familiar');
                                                    } elseif (in_array($tipo, ['desempeno', 'mejoramiento'])) {
                                                        $query->whereNotIn('nombre', [
                                                            'Perfil Rembrandtino',
                                                            'Acompañamiento familiar',
                                                        ]);
                                                    }

                                                    return $query
                                                        ->orderBy('orden')
                                                        ->orderBy('nombre')
                                                        ->pluck('nombre', 'id')
                                                        ->toArray();
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Selección obligatoria.',
                                                ]),
                                               
                                        ])
                                        ->columns(2),

                                    
                                ])
                                ->action(function (array $data): void {
                                    $this->duplicarInformacion($data);
                                }),
                        ])
                            ->columnSpanFull()
                            ->alignEnd(),
                    ])
                    ->columns(4),
            ])
            ->statePath('data');
    }

    public function seleccionarAsignatura(int $pensumId): void
    {
        $pensum = PensumAcademico::find($pensumId);

        if (! $pensum) {
            return;
        }

        $this->data['pensum_academico_id'] = $pensum->id;
        $this->asignaturaSeleccionada = $pensum->nombre;

        $this->desempenos = ['', '', '', ''];
        $this->ultimaModificacionNombre = null;
        $this->ultimaModificacionFecha = null;

        $this->cargarDesempenos();
    }

    public function estadoAsignatura(array $asignatura): string
    {
        return $asignatura['estado'];
    }

   

    public function guardarDesempenos(): void
    {
        $periodoLectivoId = $this->data['periodo_lectivo_id'] ?? null;
        $grado = $this->data['grado'] ?? null;
        $periodoAcademico = $this->data['periodo_academico'] ?? null;
        $pensumAcademicoId = $this->data['pensum_academico_id'] ?? null;

        if (! $periodoLectivoId || ! $grado || ! $periodoAcademico || ! $pensumAcademicoId) {
            Notification::make()
                ->title('Complete los filtros')
                ->body('Debe seleccionar periodo lectivo, grado, periodo académico y asignatura.')
                ->warning()
                ->send();

            return;
        }

        $this->verificarPeriodoAcademicoCerrado();

        if ($this->periodoAcademicoCerrado) {
            Notification::make()
                ->title('Periodo académico cerrado')
                ->body('No es posible guardar cambios en un periodo académico cerrado.')
                ->danger()
                ->send();

            return;
        }

        foreach ($this->desempenos as $index => $desempeno) {
            if (mb_strlen(trim((string) $desempeno)) > $this->limiteCaracteres) {
                Notification::make()
                    ->title('Texto demasiado largo')
                    ->body('El desempeño ' . ($index + 1) . ' supera el máximo de ' . $this->limiteCaracteres . ' caracteres.')
                    ->danger()
                    ->send();

                return;
            }
        }

        $desempenosLimpios = collect($this->desempenos)
            ->map(fn ($valor) => trim((string) $valor))
            ->values()
            ->toArray();

        $cantidadLlenos = collect($desempenosLimpios)
            ->filter(fn ($valor) => filled($valor))
            ->count();

        if ($cantidadLlenos < 1) {
            Notification::make()
                ->title('Debe registrar al menos un desempeño')
                ->body('La asignatura debe tener mínimo 1 desempeño y máximo 4.')
                ->warning()
                ->send();

            return;
        }

        $desempenosLlenos = 0;

        foreach ($this->desempenos as $index => $desempeno) {

            $texto = trim((string) $desempeno);

            if ($texto !== '') {
                $desempenosLlenos++;
            }

            if (mb_strlen($texto) > $this->limiteCaracteres) {

                Notification::make()
                    ->title('Límite excedido')
                    ->body('El desempeño ' . ($index + 1) . ' supera los ' . $this->limiteCaracteres . ' caracteres permitidos.')
                    ->danger()
                    ->send();

                return;
            }
        }

        if ($desempenosLlenos === 0) {

            Notification::make()
                ->title('Debe registrar al menos un desempeño')
                ->body('La asignatura debe tener mínimo un desempeño para poder guardarse.')
                ->warning()
                ->send();

            return;
        }

        $pensum = PensumAcademico::find($pensumAcademicoId);

        BoletinDesempeno::updateOrCreate(
            [
                'periodo_lectivo_id' => $periodoLectivoId,
                'grado' => $grado,
                'periodo_academico' => $periodoAcademico,
                'pensum_academico_id' => $pensumAcademicoId,
            ],
            [
                'sede_id' => $pensum?->sede_id,
                'desempeno_1' => $desempenosLimpios[0] ?: null,
                'desempeno_2' => $desempenosLimpios[1] ?: null,
                'desempeno_3' => $desempenosLimpios[2] ?: null,
                'desempeno_4' => $desempenosLimpios[3] ?: null,
                'updated_by' => Auth::id(),
                'created_by' => Auth::id(),
            ]
        );

        $this->cargarAsignaturasAvance();
        $this->cargarDesempenos();

        Notification::make()
            ->title('Desempeños guardados')
            ->success()
            ->send();
    }

    public function esTipoDesempeno(): bool
    {
        return ($this->data['tipo'] ?? 'desempeno') === 'desempeno';
    }

    public function guardarCodigos(): void
    {
        Notification::make()
            ->title('Códigos guardados')
            ->success()
            ->send();
    }


    public function duplicarInformacion(array $data): void
    {
        $origenTipo = $data['origen_tipo'] ?? null;
        $destinoTipo = $data['destino_tipo'] ?? null;

        if ($origenTipo !== $destinoTipo) {
            Notification::make()
                ->title('Tipos diferentes')
                ->body('El tipo de origen y destino deben ser iguales para duplicar la información.')
                ->warning()
                ->send();

            return;
        }

        $destinoCerrado = PeriodoAcademico::query()
            ->where('periodo_lectivo_id', $data['destino_periodo_lectivo_id'])
            ->where('numero', $data['destino_periodo_academico'])
            ->where('estado', 'cerrado')
            ->exists();

        if ($destinoCerrado) {
            Notification::make()
                ->title('Periodo académico cerrado')
                ->body('No es posible duplicar información hacia un periodo académico cerrado.')
                ->danger()
                ->send();

            return;
        }

        if ($origenTipo === 'desempeno') {
            $this->validarDuplicacionDesempenos($data);
            return;
        }

        if (in_array($origenTipo, ['mejoramiento', 'perfil', 'acompanamiento'])) {
            $this->validarDuplicacionCodigos($data);
            return;
        }

        Notification::make()
            ->title('Tipo no válido')
            ->body('No fue posible identificar el tipo de información a duplicar.')
            ->warning()
            ->send();
    }



    public function confirmarSobrescribirDuplicacion(): void
    {
        if (empty($this->duplicarPendiente)) {
            return;
        }

        $tipo = $this->duplicarPendiente['origen_tipo'] ?? 'desempeno';

        if ($tipo === 'desempeno') {
            $this->ejecutarDuplicacionDesempenos($this->duplicarPendiente);
        } else {
            $this->ejecutarDuplicacionCodigos($this->duplicarPendiente);
        }

        $this->duplicarPendiente = [];

        $this->dispatch('close-modal', id: 'modal-confirmar-sobrescribir');
    }

    private function ejecutarDuplicacionDesempenos(array $data): void
    {
        $origen = BoletinDesempeno::query()
            ->where('periodo_lectivo_id', $data['origen_periodo_lectivo_id'])
            ->where('grado', $data['origen_grado'])
            ->where('periodo_academico', $data['origen_periodo_academico'])
            ->where('pensum_academico_id', $data['origen_pensum_academico_id'])
            ->first();

        if (! $origen) {
            return;
        }

        $destinoCerrado = PeriodoAcademico::query()
            ->where('periodo_lectivo_id', $data['destino_periodo_lectivo_id'])
            ->where('numero', $data['destino_periodo_academico'])
            ->where('estado', 'cerrado')
            ->exists();

        if ($destinoCerrado) {
            Notification::make()
                ->title('Periodo académico cerrado')
                ->body('No es posible duplicar información hacia un periodo académico cerrado.')
                ->danger()
                ->send();

            return;
        }

        BoletinDesempeno::updateOrCreate(
            [
                'periodo_lectivo_id' => $data['destino_periodo_lectivo_id'],
                'grado' => $data['destino_grado'],
                'periodo_academico' => $data['destino_periodo_academico'],
                'pensum_academico_id' => $data['destino_pensum_academico_id'],
            ],
            [
                'sede_id' => PensumAcademico::find($data['destino_pensum_academico_id'])?->sede_id,
                'desempeno_1' => $origen->desempeno_1,
                'desempeno_2' => $origen->desempeno_2,
                'desempeno_3' => $origen->desempeno_3,
                'desempeno_4' => $origen->desempeno_4,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]
        );

        $this->cargarAsignaturasAvance();
        $this->cargarDesempenos();

        Notification::make()
            ->title('Información duplicada')
            ->body('Los desempeños fueron copiados correctamente.')
            ->success()
            ->send();
    }


    private function validarDuplicacionDesempenos(array $data): void
    {
        $origen = BoletinDesempeno::query()
            ->where('periodo_lectivo_id', $data['origen_periodo_lectivo_id'])
            ->where('grado', $data['origen_grado'])
            ->where('periodo_academico', $data['origen_periodo_academico'])
            ->where('pensum_academico_id', $data['origen_pensum_academico_id'])
            ->first();

        if (! $origen) {
            Notification::make()
                ->title('No hay información para duplicar')
                ->body('La configuración de origen no tiene desempeños guardados.')
                ->warning()
                ->send();

            return;
        }

        $destinoExistente = BoletinDesempeno::query()
            ->where('periodo_lectivo_id', $data['destino_periodo_lectivo_id'])
            ->where('grado', $data['destino_grado'])
            ->where('periodo_academico', $data['destino_periodo_academico'])
            ->where('pensum_academico_id', $data['destino_pensum_academico_id'])
            ->first();

        if ($destinoExistente) {
            $this->duplicarPendiente = $data;
            $this->dispatch('open-modal', id: 'modal-confirmar-sobrescribir');
            return;
        }

        $this->ejecutarDuplicacionDesempenos($data);
    }


    private function validarDuplicacionCodigos(array $data): void
    {
        $origenTipo = $data['origen_tipo'];

        $origen = BoletinRecomendacion::query()
            ->where('periodo_lectivo_id', $data['origen_periodo_lectivo_id'])
            ->where('grado', $data['origen_grado'])
            ->where('periodo_academico', $data['origen_periodo_academico'])
            ->where('pensum_academico_id', $data['origen_pensum_academico_id'])
            ->where('tipo', $origenTipo)
            ->exists();

        if (! $origen) {
            Notification::make()
                ->title('No hay información para duplicar')
                ->body('La configuración de origen no tiene códigos guardados.')
                ->warning()
                ->send();

            return;
        }

        $destinoExistente = BoletinRecomendacion::query()
            ->where('periodo_lectivo_id', $data['destino_periodo_lectivo_id'])
            ->where('grado', $data['destino_grado'])
            ->where('periodo_academico', $data['destino_periodo_academico'])
            ->where('pensum_academico_id', $data['destino_pensum_academico_id'])
            ->where('tipo', $origenTipo)
            ->exists();

        if ($destinoExistente) {
            $this->duplicarPendiente = $data;
            $this->dispatch('open-modal', id: 'modal-confirmar-sobrescribir');
            return;
        }

        $this->ejecutarDuplicacionCodigos($data);
    }



    private function ejecutarDuplicacionCodigos(array $data): void
    {
        $tipo = $data['origen_tipo'];

        $origenes = BoletinRecomendacion::query()
            ->where('periodo_lectivo_id', $data['origen_periodo_lectivo_id'])
            ->where('grado', $data['origen_grado'])
            ->where('periodo_academico', $data['origen_periodo_academico'])
            ->where('pensum_academico_id', $data['origen_pensum_academico_id'])
            ->where('tipo', $tipo)
            ->orderByRaw('CAST(codigo AS UNSIGNED)')
            ->get();

        if ($origenes->isEmpty()) {
            return;
        }

        BoletinRecomendacion::query()
            ->where('periodo_lectivo_id', $data['destino_periodo_lectivo_id'])
            ->where('grado', $data['destino_grado'])
            ->where('periodo_academico', $data['destino_periodo_academico'])
            ->where('pensum_academico_id', $data['destino_pensum_academico_id'])
            ->where('tipo', $tipo)
            ->delete();

        $pensumDestino = PensumAcademico::find($data['destino_pensum_academico_id']);

        foreach ($origenes as $origen) {
            BoletinRecomendacion::create([
                'sede_id' => $pensumDestino?->sede_id,
                'periodo_lectivo_id' => $data['destino_periodo_lectivo_id'],
                'grado' => $data['destino_grado'],
                'periodo_academico' => $data['destino_periodo_academico'],
                'pensum_academico_id' => $data['destino_pensum_academico_id'],
                'tipo' => $tipo,
                'codigo' => $origen->codigo,
                'descripcion' => $origen->descripcion,
                'activo' => $origen->activo,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        }

        $this->cargarAsignaturasAvance();
        $this->cargarDesempenos();
        $this->cargarCodigos();
        $this->cargarResumenCodigos();

        Notification::make()
            ->title('Información duplicada')
            ->body('Los códigos fueron copiados correctamente.')
            ->success()
            ->send();
    }


    public function abrirModalCodigo(): void
    {
        $this->codigoEditandoId = null;
        $this->codigoModalTitulo = 'Nuevo código';

        $this->codigoSugerido = $this->obtenerSiguienteCodigoDisponible();

        $this->codigoForm = [
            'codigo' => '',
            'descripcion' => '',
            'activo' => '1',
        ];

        $this->dispatch('open-modal', id: 'modal-codigo-boletin');
    }


    public function obtenerSiguienteCodigoDisponible(): int
    {
        $periodoLectivoId = $this->data['periodo_lectivo_id'] ?? null;
        $grado = $this->data['grado'] ?? null;
        $periodoAcademico = $this->data['periodo_academico'] ?? null;
        $pensumAcademicoId = $this->data['pensum_academico_id'] ?? null;
        $tipo = $this->data['tipo'] ?? null;

        if (! $periodoLectivoId || ! $grado || ! $periodoAcademico || ! $pensumAcademicoId || ! $tipo || $tipo === 'desempeno') {
            return 1;
        }

        $ultimoCodigo = BoletinRecomendacion::query()
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('grado', $grado)
            ->where('periodo_academico', $periodoAcademico)
            ->where('pensum_academico_id', $pensumAcademicoId)
            ->where('tipo', $tipo)
            ->max('codigo');

        return ((int) $ultimoCodigo) + 1;
    }

    public function editarCodigo(int $id): void
    {
        $codigo = BoletinRecomendacion::find($id);

        if (! $codigo) {
            return;
        }

        $this->codigoEditandoId = $codigo->id;
        $this->codigoModalTitulo = 'Editar código';

        $this->codigoForm = [
            'codigo' => $codigo->codigo,
            'descripcion' => $codigo->descripcion,
            'activo' => $codigo->activo ? '1' : '0',
        ];

        $this->dispatch('open-modal', id: 'modal-codigo-boletin');
    }

    public function guardarCodigoModal(): void
    {
        $periodoLectivoId = $this->data['periodo_lectivo_id'] ?? null;
        $grado = $this->data['grado'] ?? null;
        $periodoAcademico = $this->data['periodo_academico'] ?? null;
        $pensumAcademicoId = $this->data['pensum_academico_id'] ?? null;
        $tipo = $this->data['tipo'] ?? null;

        if (! $periodoLectivoId || ! $grado || ! $periodoAcademico || ! $pensumAcademicoId || ! $tipo || $tipo === 'desempeno') {
            Notification::make()
                ->title('Complete los filtros')
                ->body('Debe seleccionar periodo lectivo, grado, periodo académico, asignatura y tipo.')
                ->warning()
                ->send();

            return;
        }

        $this->verificarPeriodoAcademicoCerrado();

        if ($this->periodoAcademicoCerrado) {
            Notification::make()
                ->title('Periodo académico cerrado')
                ->body('No es posible modificar códigos en un periodo académico cerrado.')
                ->danger()
                ->send();

            return;
        }

        $pensum = PensumAcademico::find($pensumAcademicoId);

        $codigo = trim((string) $this->codigoForm['codigo']);

        $descripcion = trim((string) $this->codigoForm['descripcion']);

        if ($codigo === '') {
            Notification::make()
                ->title('Código obligatorio')
                ->body('Debe ingresar el número de código.')
                ->warning()
                ->send();

            return;
        }

        if ($descripcion === '') {
            Notification::make()
                ->title('Descripción obligatoria')
                ->body('Debe ingresar la descripción del código.')
                ->warning()
                ->send();

            return;
        }


        if (in_array($tipo, ['perfil', 'acompanamiento']) && mb_strlen($descripcion) > 60) {
            Notification::make()
                ->title('Descripción demasiado larga')
                ->body('Para Perfil Rembrandtino y Acompañamiento Familiar la descripción debe tener máximo 60 caracteres.')
                ->warning()
                ->send();

            return;
        }

        if (! preg_match('/^\d{1,3}$/', $codigo)) {
            Notification::make()
                ->title('Código inválido')
                ->body('El código debe ser numérico, Ejemplo: 1, 2, 01, 02.')
                ->warning()
                ->send();

            return;
        }

        $codigoDuplicado = BoletinRecomendacion::query()
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('grado', $grado)
            ->where('periodo_academico', $periodoAcademico)
            ->where('pensum_academico_id', $pensumAcademicoId)
            ->where('tipo', $tipo)
            ->where('codigo', $codigo)
            ->when($this->codigoEditandoId, function ($query) {
                $query->where('id', '!=', $this->codigoEditandoId);
            })
            ->exists();

        if ($codigoDuplicado) {
            Notification::make()
                ->title('Código duplicado')
                ->body('Ya existe un código con ese número para esta configuración.')
                ->warning()
                ->send();

            return;
        }

        BoletinRecomendacion::updateOrCreate(
            [
                'id' => $this->codigoEditandoId,
            ],
            [
                'sede_id' => $pensum?->sede_id,
                'periodo_lectivo_id' => $periodoLectivoId,
                'grado' => $grado,
                'periodo_academico' => $periodoAcademico,
                'pensum_academico_id' => $pensumAcademicoId,
                'tipo' => $tipo,
                'codigo' => $codigo,
                'descripcion' => $descripcion,
                'activo' => in_array((string) $this->codigoForm['activo'], ['1', 'true'], true),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]
        );

        $this->codigoEditandoId = null;

        $this->cargarCodigos();
        $this->cargarResumenCodigos();

        $this->dispatch('close-modal', id: 'modal-codigo-boletin');

        Notification::make()
            ->title('Código guardado')
            ->success()
            ->send();
    }


    public function cargarAsignaturasAvance(): void
    {
        $periodoLectivoId = $this->data['periodo_lectivo_id'] ?? null;
        $grado = $this->data['grado'] ?? null;
        $periodoAcademico = $this->data['periodo_academico'] ?? null;

        if (! $periodoLectivoId || ! $grado || ! $periodoAcademico) {
            $this->asignaturasAvance = [];

            $this->totalAsignaturas = 0;
            $this->totalCompletas = 0;
            $this->totalIncompletas = 0;
            $this->totalPendientes = 0;

            return;
        }

        $pensums = PensumAcademico::query()
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('grado', $grado)
            ->where('estado', 'activo')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->whereNotIn('nombre', [
                'Perfil Rembrandtino',
                'Acompañamiento familiar',
            ])
            ->get();

        $this->asignaturasAvance = $pensums
            ->map(function ($pensum) use ($periodoLectivoId, $grado, $periodoAcademico) {
                $registro = BoletinDesempeno::query()
                    ->where('periodo_lectivo_id', $periodoLectivoId)
                    ->where('grado', $grado)
                    ->where('periodo_academico', $periodoAcademico)
                    ->where('pensum_academico_id', $pensum->id)
                    ->first();

                $cantidad = 0;

                if ($registro) {
                    $cantidad = collect([
                        $registro->desempeno_1,
                        $registro->desempeno_2,
                        $registro->desempeno_3,
                        $registro->desempeno_4,
                    ])
                        ->filter(fn ($valor) => filled($valor))
                        ->count();
                }

                $estado = match (true) {
                    $cantidad >= 4 => 'completo',
                    $cantidad >= 1 => 'incompleto',
                    default => 'pendiente',
                };

                return [
                    'id' => $pensum->id,
                    'nombre' => $pensum->nombre,
                    'estado' => $estado,
                ];
            })
            ->toArray();

        $this->totalAsignaturas = count($this->asignaturasAvance);

        $this->totalCompletas = collect($this->asignaturasAvance)
            ->where('estado', 'completo')
            ->count();

        $this->totalIncompletas = collect($this->asignaturasAvance)
            ->where('estado', 'incompleto')
            ->count();

        $this->totalPendientes = collect($this->asignaturasAvance)
            ->where('estado', 'pendiente')
            ->count();
    }
    




    public function actualizarResumenFiltro(): void
    {
        $periodoLectivoId = $this->data['periodo_lectivo_id'] ?? null;
        $grado = $this->data['grado'] ?? null;
        $periodoAcademico = $this->data['periodo_academico'] ?? null;

        if (! $periodoLectivoId) {
            $this->resumenFiltro = '';
            return;
        }

        $periodoLectivo = PeriodoLectivo::with('sede')->find($periodoLectivoId);

        if (! $periodoLectivo) {
            $this->resumenFiltro = '';
            return;
        }

        $texto = ($periodoLectivo->sede?->nombre ?? '')
            . ' - '
            . $periodoLectivo->nombre;

        if ($grado) {
            $texto .= ' · Grado ' . $grado . '°';
        }

        if ($periodoAcademico) {
            $texto .= ' · Periodo ' . $periodoAcademico;
        }

        $this->resumenFiltro = $texto;
    }


    public function cargarDesempenos(): void
    {
        $periodoLectivoId = $this->data['periodo_lectivo_id'] ?? null;
        $grado = $this->data['grado'] ?? null;
        $periodoAcademico = $this->data['periodo_academico'] ?? null;
        $pensumAcademicoId = $this->data['pensum_academico_id'] ?? null;

        if (! $periodoLectivoId || ! $grado || ! $periodoAcademico || ! $pensumAcademicoId) {
            $this->desempenos = ['', '', '', ''];
            return;
        }

        $pensum = PensumAcademico::find($pensumAcademicoId);

        $registro = BoletinDesempeno::query()
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('grado', $grado)
            ->where('periodo_academico', $periodoAcademico)
            ->where('pensum_academico_id', $pensumAcademicoId)
            ->first();

            if ($registro) {
                $usuario = \App\Models\User::find($registro->updated_by ?? $registro->created_by);

                $this->ultimaModificacionNombre = $usuario?->name ?? 'Usuario';
                $this->ultimaModificacionFecha = $registro->updated_at?->format('d/m/Y H:i');
            } else {
                $this->ultimaModificacionNombre = null;
                $this->ultimaModificacionFecha = null;
            }

        $this->desempenos = [
            $registro?->desempeno_1 ?? '',
            $registro?->desempeno_2 ?? '',
            $registro?->desempeno_3 ?? '',
            $registro?->desempeno_4 ?? '',
        ];

        $this->asignaturaSeleccionada = $pensum?->nombre ?? '';
    }

    public function verificarDestinoDuplicacionCerrado(?int $periodoLectivoId = null, ?string $periodoAcademico = null): void
    {
        if (! $periodoLectivoId || ! $periodoAcademico) {
            $this->destinoDuplicacionCerrado = false;
            return;
        }

        $this->destinoDuplicacionCerrado = PeriodoAcademico::query()
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('numero', $periodoAcademico)
            ->where('estado', 'cerrado')
            ->exists();
    }



    public function cargarCodigos(): void
    {
        $periodoLectivoId = $this->data['periodo_lectivo_id'] ?? null;
        $grado = $this->data['grado'] ?? null;
        $periodoAcademico = $this->data['periodo_academico'] ?? null;
        $pensumAcademicoId = $this->data['pensum_academico_id'] ?? null;
        $tipo = $this->data['tipo'] ?? 'desempeno';

        if ($tipo === 'desempeno') {
            $this->codigos = [];
            $this->ultimaModificacionNombre = null;
            $this->ultimaModificacionFecha = null;
            return;
        }

        if (! $periodoLectivoId || ! $grado || ! $periodoAcademico || ! $pensumAcademicoId) {
            $this->codigos = [];
            $this->ultimaModificacionNombre = null;
            $this->ultimaModificacionFecha = null;
            return;
        }

        $registros = BoletinRecomendacion::query()
            ->with('actualizadoPor')
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('grado', $grado)
            ->where('periodo_academico', $periodoAcademico)
            ->where('pensum_academico_id', $pensumAcademicoId)
            ->where('tipo', $tipo)
            ->orderByRaw('CAST(codigo AS UNSIGNED)')
            ->get();

        $this->codigos = $registros
            ->map(fn ($codigo) => [
                'id' => $codigo->id,
                'codigo' => $codigo->codigo,
                'descripcion' => $codigo->descripcion,
                'activo' => $codigo->activo ? '1' : '0',
            ])
            ->toArray();

        $ultimo = $registros->sortByDesc('updated_at')->first();

        $this->ultimaModificacionNombre = $ultimo?->actualizadoPor?->name;
        $this->ultimaModificacionFecha = $ultimo?->updated_at?->format('d/m/Y H:i');
    }


    public function seleccionarAsignaturaEspecial(string $tipo, Set $set): void
    {
        $periodoLectivoId = $this->data['periodo_lectivo_id'] ?? null;
        $grado = $this->data['grado'] ?? null;

        if (! $periodoLectivoId || ! $grado) {
            return;
        }

        $nombre = match ($tipo) {
            'perfil' => 'Perfil Rembrandtino',
            'acompanamiento' => 'Acompañamiento familiar',
            default => null,
        };

        if (! $nombre) {
            return;
        }

        $pensum = PensumAcademico::query()
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('grado', $grado)
            ->where('estado', 'activo')
            ->where('nombre', 'like', "%{$nombre}%")
            ->first();

        if (! $pensum) {
            Notification::make()
                ->title('Asignatura no encontrada')
                ->body("No existe {$nombre} en el pensum del grado seleccionado.")
                ->warning()
                ->send();

            $set('pensum_academico_id', null);
            $this->asignaturaSeleccionada = '';

            return;
        }

        $set('pensum_academico_id', $pensum->id);
        $this->asignaturaSeleccionada = $pensum->nombre;
    }


    public function asegurarAsignaturaEspecial(): void
    {
        $tipo = $this->data['tipo'] ?? null;

        if (! in_array($tipo, ['perfil', 'acompanamiento'])) {
            return;
        }

        $periodoLectivoId = $this->data['periodo_lectivo_id'] ?? null;
        $grado = $this->data['grado'] ?? null;

        if (! $periodoLectivoId || ! $grado) {
            $this->data['pensum_academico_id'] = null;
            $this->asignaturaSeleccionada = '';
            return;
        }

        $nombre = $tipo === 'perfil'
            ? 'Perfil Rembrandtino'
            : 'Acompañamiento familiar';

        $pensum = PensumAcademico::query()
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('grado', $grado)
            ->where('estado', 'activo')
            ->where('nombre', 'like', "%{$nombre}%")
            ->first();

        $this->data['pensum_academico_id'] = $pensum?->id;
        $this->asignaturaSeleccionada = $pensum?->nombre ?? '';
    }



    public function cargarResumenCodigos(): void
    {
        $periodoLectivoId = $this->data['periodo_lectivo_id'] ?? null;
        $grado = $this->data['grado'] ?? null;
        $periodoAcademico = $this->data['periodo_academico'] ?? null;
        $pensumAcademicoId = $this->data['pensum_academico_id'] ?? null;
        $tipo = $this->data['tipo'] ?? 'desempeno';

        if ($tipo === 'desempeno') {
            $this->totalCodigos = 0;
            $this->totalCodigosActivos = 0;
            $this->totalCodigosInactivos = 0;
            $this->totalAsignaturasConCodigos = 0;
            return;
        }

        if (! $periodoLectivoId || ! $grado || ! $periodoAcademico || ! $pensumAcademicoId) {
            $this->totalCodigos = 0;
            $this->totalCodigosActivos = 0;
            $this->totalCodigosInactivos = 0;
            $this->totalAsignaturasConCodigos = 0;
            return;
        }

        $query = BoletinRecomendacion::query()
            ->where('periodo_lectivo_id', $periodoLectivoId)
            ->where('grado', $grado)
            ->where('periodo_academico', $periodoAcademico)
            ->where('pensum_academico_id', $pensumAcademicoId)
            ->where('tipo', $tipo);

        $this->totalCodigos = (clone $query)->count();

        $this->totalCodigosActivos = (clone $query)
            ->where('activo', true)
            ->count();

        $this->totalCodigosInactivos = (clone $query)
            ->where('activo', false)
            ->count();

        $this->totalAsignaturasConCodigos = (clone $query)
            ->distinct('pensum_academico_id')
            ->count('pensum_academico_id');
    }


}