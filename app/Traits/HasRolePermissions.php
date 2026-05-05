<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Aplica restricciones de rol a los Resources de Filament.
 *
 * superadmin / admin  → acceso completo (CRUD)
 * rector / docente    → solo ver, crear y editar (sin borrar)
 */
trait HasRolePermissions
{
    // ── Solo admin/superadmin pueden borrar ──────────────────────────────────
    public static function canDelete(Model $record): bool
    {
        return static::userIsAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return static::userIsAdmin();
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::userIsAdmin();
    }

    public static function canForceDeleteAny(): bool
    {
        return static::userIsAdmin();
    }

    // ── Sedes y Usuarios: solo admin/superadmin los ven ──────────────────────
    // (Se sobreescribe en los resources que corresponda)

    // ── Helper ───────────────────────────────────────────────────────────────
    protected static function userIsAdmin(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Comprueba tipo_usuario O rol de Spatie
        if (in_array($user->tipo_usuario ?? '', ['superadmin', 'admin'])) {
            return true;
        }

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole(['superadmin', 'admin']);
        }

        return false;
    }
}
