<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasRolePermissions
{
    /*
    |--------------------------------------------------------------------------
    | Permisos declarativos del Resource
    |--------------------------------------------------------------------------
    |
    | Cada Resource nuevo podrá declarar:
    |
    | protected static ?string $viewPermission   = 'ver_xxx';
    | protected static ?string $createPermission = 'crear_xxx';
    | protected static ?string $editPermission   = 'editar_xxx';
    | protected static ?string $deletePermission = 'eliminar_xxx';
    |
    | Si todavía no los declara, se conserva temporalmente el comportamiento
    | anterior para no romper módulos existentes.
    |
    */

    protected static function permisoConfigurado(
        string $propiedad
    ): ?string {
        if (! property_exists(static::class, $propiedad)) {
            return null;
        }

        $permiso = static::${$propiedad};

        return filled($permiso)
            ? (string) $permiso
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Ver pantalla / menú lateral
    |--------------------------------------------------------------------------
    */

    public static function canViewAny(): bool
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return false;
        }

        $permiso = static::permisoConfigurado(
            'viewPermission'
        );

        /*
        |--------------------------------------------------------------------------
        | Compatibilidad con Resources todavía no migrados
        |--------------------------------------------------------------------------
        */

        if (! $permiso) {
            return true;
        }

        return $usuario->can($permiso);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    /*
    |--------------------------------------------------------------------------
    | Crear
    |--------------------------------------------------------------------------
    */

    public static function canCreate(): bool
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return false;
        }

        $permiso = static::permisoConfigurado(
            'createPermission'
        );

        /*
        | Resource todavía no migrado:
        | mantenemos el comportamiento actual.
        */
        if (! $permiso) {
            return true;
        }

        return $usuario->can($permiso);
    }

    /*
    |--------------------------------------------------------------------------
    | Editar
    |--------------------------------------------------------------------------
    */

    public static function canEdit(Model $record): bool
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return false;
        }

        $permiso = static::permisoConfigurado(
            'editPermission'
        );

        if (! $permiso) {
            return true;
        }

        return $usuario->can($permiso);
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar
    |--------------------------------------------------------------------------
    */

    public static function canDelete(Model $record): bool
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return false;
        }

        $permiso = static::permisoConfigurado(
            'deletePermission'
        );

        /*
        |--------------------------------------------------------------------------
        | Compatibilidad histórica
        |--------------------------------------------------------------------------
        |
        | Hasta que el Resource sea migrado, conservamos la regla antigua:
        | solamente admin/superadmin eliminan.
        |
        */

        if (! $permiso) {
            return static::userIsAdmin();
        }

        return $usuario->can($permiso);
    }

    public static function canDeleteAny(): bool
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return false;
        }

        $permiso = static::permisoConfigurado(
            'deletePermission'
        );

        if (! $permiso) {
            return static::userIsAdmin();
        }

        return $usuario->can($permiso);
    }

    public static function canForceDelete(
        Model $record
    ): bool {
        return static::canDelete($record);
    }

    public static function canForceDeleteAny(): bool
    {
        return static::canDeleteAny();
    }

    /*
    |--------------------------------------------------------------------------
    | Helper histórico
    |--------------------------------------------------------------------------
    */

    protected static function userIsAdmin(): bool
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return false;
        }

        return $usuario->hasAnyRole([
            'superadmin',
            'admin',
        ]);
    }
}