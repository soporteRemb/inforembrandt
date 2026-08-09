<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\UserResource;

use App\Traits\HasRolePermissions;

use Database\Seeders\PermissionsSeeder;


use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View as ViewField;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;



use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class RoleResource extends Resource
{
    use HasRolePermissions;


    protected static ?string $viewPermission =
        'ver_roles';

    protected static ?string $editPermission =
        'editar_roles';

    



    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Roles y Permisos';
    protected static ?string $modelLabel = 'Rol';
    protected static ?string $pluralModelLabel = 'Roles y Permisos';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int $navigationSort = 3;



    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Excepción de arranque:
        // SuperAdmin siempre puede entrar a Roles y Permisos.
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Para todos los demás usuarios,
        // aplica el sistema normal de permisos.
        return $user->can('ver_roles');
    }


    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Excepción de arranque:
        // SuperAdmin siempre puede entrar a Roles y Permisos.
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->can('ver_roles');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $usuarioActual = auth()->user();

        if (! $usuarioActual) {
            return $query->whereRaw('1 = 0');
        }

        if ($usuarioActual->hasAnyRole([
            'admin',
            'superadmin',
        ])) {
            return $query;
        }

        return $query->whereNotIn(
            'name',
            [
                'admin',
                'superadmin',
            ]
        );
    }


    public static function form(Form $form): Form
    {
        $grupos = PermissionsSeeder::permisos();

        $sections = [];
        foreach ($grupos as $modulo => $permisos) {
            $fieldName = 'perm_' . Str::slug($modulo, '_');

            $permRecords = Permission::whereIn('name', $permisos)->get();

            $options = $permRecords
                ->pluck('name', 'id')
                ->map(fn($name) => self::etiqueta($name))
                ->toArray();

            // ID del permiso "ver_" de este módulo (puede no existir en todos)
            $verPermId = (string) ($permRecords->firstWhere(
                fn ($permiso) => str_starts_with($permiso->name, 'ver_')
            )?->id ?? '');

            $permisosIndependientes = $permRecords
                ->filter(fn ($permiso) => in_array($permiso->name, [
                    'diligenciar_formulario_pre_matricula',
                ]))
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();

            $sections[] = Section::make($modulo)
                ->collapsible()
                ->schema([
                    CheckboxList::make($fieldName)
                        ->label('')
                        ->options($options)
                        ->columns(2)
                        ->gridDirection('row'),
                ]);
        }

        return $form->schema([
            TextInput::make('name')
                ->label('Nombre del rol')
                ->required()
                ->maxLength(50)

                // Siempre guardar el nombre normalizado
                ->dehydrateStateUsing(
                    fn ($state) => UserResource::normalizarNombreRol($state)
                )

                // No permitir renombrar los roles principales
                ->disabled(
                    fn ($record) => in_array(
                        UserResource::normalizarNombreRol($record?->name),
                        ['superadmin', 'admin'],
                        true
                    )
                )

                ->helperText(
                    'Los roles superadmin y admin no se pueden renombrar.'
                ),

            ViewField::make('filament.forms.role-filter-toolbar')
                ->label('')
                ->columnSpanFull(),

            ...$sections,
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Rol')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('permissions_count')
                    ->label('Permisos asignados')
                    ->counts('permissions')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts('users')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar permisos'),
            ])
            ->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        $usuario = auth()->user();

        return $usuario
            && $usuario->hasAnyRole(['superadmin', 'admin'])
            && $usuario->hasPermissionTo('editar_roles');
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    

    

    /** Devuelve los nombres de los campos de permisos para un registro de role */
    public static function permFieldsFromRecord(Role $role): array
    {
        $currentIds = $role->permissions->pluck('id')->toArray();
        $data = [];
        foreach (PermissionsSeeder::permisos() as $modulo => $permisos) {
            $fieldName = 'perm_' . Str::slug($modulo, '_');
            $ids = Permission::whereIn('name', $permisos)
                ->whereIn('id', $currentIds)
                ->pluck('id')
                ->toArray();
            $data[$fieldName] = $ids;
        }
        return $data;
    }

    /** Recoge los IDs seleccionados de todos los campos de permisos */
    public static function collectPermIds(array $data): array
    {
        $all = [];
        foreach (array_keys(PermissionsSeeder::permisos()) as $modulo) {
            $fieldName = 'perm_' . Str::slug($modulo, '_');
            $all = array_merge($all, $data[$fieldName] ?? []);
        }
        return array_unique(array_map('intval', $all));
    }

    private static function etiqueta(string $name): string
    {
        $etiquetasEspeciales = [
            'diligenciar_formulario_pre_matricula' => 'Diligenciar formulario',
            'ver_pre_matriculas' => 'Ver pre-matrículas',
            'editar_pre_matriculas' => 'Editar pre-matrículas',
            'exportar_pre_matriculas' => 'Exportar pre-matrículas',
            'ver_historial_pre_matriculas' => 'Ver historial',
        ];

        if (isset($etiquetasEspeciales[$name])) {
            return $etiquetasEspeciales[$name];
        }

        $mapa = [
            'ver_'      => 'Ver ',
            'crear_'    => 'Crear ',
            'editar_'   => 'Editar ',
            'eliminar_' => 'Eliminar ',
        ];

        $recursos = [
            'estudiantes'        => 'Estudiantes',
            'usuarios'           => 'Usuarios',
            'cursos'             => 'Cursos',
            'sedes'              => 'Sedes',
            'periodos'           => 'Períodos',
            'acudientes'         => 'Acudientes',
            'matriculas'         => 'Matrículas',
            'roles'              => 'Roles',
            'conceptos_cobro'    => 'Conceptos de Cobro',
            'asignacion_costos'  => 'Asignación Costos',

            'notas'              => 'Notas',
            'pensum'             => 'Pensum Académico',
            'docentes'           => 'Docentes',
            'desempenos'         => 'Desempeños',

            'boletines_administrativos' => 'Boletines Administrativos',
            'causacion_costos'           => 'Causación de Costos',
            'pagos'                      => 'Pagos',
            'otros_parametros'           => 'Otros Parámetros',
            'importacion_datos'          => 'Importación de Datos',

            'boletines_acudientes' => 'Boletines Acudientes',

        ];

        foreach ($mapa as $prefijo => $label) {
            if (str_starts_with($name, $prefijo)) {
                $recurso = substr($name, strlen($prefijo));

                return $label . ($recursos[$recurso] ?? ucfirst($recurso));
            }
        }

        return $name;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
