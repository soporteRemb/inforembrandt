<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
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

class RoleResource extends Resource
{
    use HasRolePermissions;

    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Roles y Permisos';
    protected static ?string $modelLabel = 'Rol';
    protected static ?string $pluralModelLabel = 'Roles y Permisos';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int $navigationSort = 3;

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
                fn($p) => str_starts_with($p->name, 'ver_')
            )?->id ?? '');

            $sections[] = Section::make($modulo)
                ->collapsible()
                ->schema([
                    CheckboxList::make($fieldName)
                        ->label('')
                        ->options($options)
                        ->columns(2)
                        ->gridDirection('row')
                        ->live()
                        ->afterStateUpdated(function (?array $state, Set $set) use ($fieldName, $verPermId) {
                            // Si "Ver" fue desmarcado, quitar todos los demás del módulo
                            if ($verPermId && !in_array($verPermId, array_map('strval', $state ?? []))) {
                                $set($fieldName, []);
                            }
                        }),
                ]);
        }

        return $form->schema([
            TextInput::make('name')
                ->label('Nombre del rol')
                ->required()
                ->maxLength(50)
                ->disabled(fn($record) => in_array($record?->name ?? '', ['superadmin', 'admin']))
                ->helperText('Los roles superadmin y admin no se pueden renombrar.'),

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
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        return $user->hasAnyRole(['superadmin', 'admin']) && $user->hasPermissionTo('ver_roles');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'edit'  => Pages\EditRole::route('/{record}/edit'),
        ];
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
        $mapa = [
            'ver_'      => 'Ver ',
            'crear_'    => 'Crear ',
            'editar_'   => 'Editar ',
            'eliminar_' => 'Eliminar ',
        ];

        $recursos = [
            'estudiantes'       => 'Estudiantes',
            'usuarios'          => 'Usuarios',
            'cursos'            => 'Cursos',
            'sedes'             => 'Sedes',
            'periodos'          => 'Períodos',
            'acudientes'        => 'Acudientes',
            'matriculas'        => 'Matrículas',
            'roles'             => 'Roles',
            'conceptos_cobro'   => 'Conceptos de Cobro',
            'asignacion_costos' => 'Asignación Costos',
        ];

        foreach ($mapa as $prefijo => $label) {
            if (str_starts_with($name, $prefijo)) {
                $recurso = substr($name, strlen($prefijo));
                return $label . ($recursos[$recurso] ?? ucfirst($recurso));
            }
        }

        return $name;
    }
}
