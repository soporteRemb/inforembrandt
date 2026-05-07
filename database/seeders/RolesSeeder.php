<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'superadmin',
            'admin',
            'rector',
            'coordinador_academico',
            'coordinador_convivencia',
            'secretaria',
            'director_grupo',
            'docente',
            'acudiente',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Asignar permisos por defecto
        $this->call(PermissionsSeeder::class);
    }
}
