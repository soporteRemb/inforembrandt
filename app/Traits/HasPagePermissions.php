<?php

namespace App\Traits;

trait HasPagePermissions
{
    /*
    |--------------------------------------------------------------------------
    | Permiso principal de la Page
    |--------------------------------------------------------------------------
    |
    | La Page debe declarar:
    |
    | protected static ?string $viewPermission = 'ver_xxx';
    |
    */

    protected static function permisoPagina(): ?string
    {
        if (
            ! property_exists(
                static::class,
                'viewPermission'
            )
        ) {
            return null;
        }

        $permiso = static::$viewPermission;

        return filled($permiso)
            ? (string) $permiso
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Acceso
    |--------------------------------------------------------------------------
    */

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return false;
        }

        $permiso = static::permisoPagina();

        /*
        |--------------------------------------------------------------------------
        | Compatibilidad con Pages antiguas
        |--------------------------------------------------------------------------
        |
        | Si todavía no se ha migrado la pantalla, no cambiamos su acceso.
        |
        */

        if (! $permiso) {
            return true;
        }

        return $usuario->can($permiso);
    }

    /*
    |--------------------------------------------------------------------------
    | Menú
    |--------------------------------------------------------------------------
    */

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}