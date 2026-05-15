<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    // Permisos agrupados por módulo
    public static function permisos(): array
    {
        return [
            'Estudiantes'     => ['ver_estudiantes', 'crear_estudiantes', 'editar_estudiantes', 'eliminar_estudiantes'],
            'Usuarios'        => ['ver_usuarios', 'crear_usuarios', 'editar_usuarios', 'eliminar_usuarios'],
            'Cursos'          => ['ver_cursos', 'crear_cursos', 'editar_cursos', 'eliminar_cursos'],
            'Sedes'           => ['ver_sedes', 'crear_sedes', 'editar_sedes', 'eliminar_sedes'],
            'Periodos'        => ['ver_periodos', 'crear_periodos', 'editar_periodos', 'eliminar_periodos'],
            'Acudientes'      => ['ver_acudientes', 'crear_acudientes', 'editar_acudientes', 'eliminar_acudientes'],
            'Matrículas'      => ['ver_matriculas', 'crear_matriculas', 'editar_matriculas', 'eliminar_matriculas'],
            'Roles'              => ['ver_roles', 'editar_roles'],
            'Conceptos de Cobro' => ['ver_conceptos_cobro', 'crear_conceptos_cobro', 'editar_conceptos_cobro', 'eliminar_conceptos_cobro'],
            'Asignación Costos'  => ['ver_asignacion_costos', 'crear_asignacion_costos', 'editar_asignacion_costos', 'eliminar_asignacion_costos'],
        ];
    }

    public function run(): void
    {
        // Crear todos los permisos
        foreach (self::permisos() as $permisos) {
            foreach ($permisos as $permiso) {
                Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
            }
        }

        // Asignar permisos por defecto a cada rol
        $todosLosPermisos = Permission::all();

        $config = [
            'superadmin' => $todosLosPermisos, // todo

            'admin' => $todosLosPermisos, // todo

            'rector' => Permission::whereNotIn('name', [
                'eliminar_usuarios', 'eliminar_sedes', 'editar_roles', 'ver_roles',
            ])->get(),

            'coordinador_academico' => Permission::whereIn('name', [
                'ver_estudiantes', 'editar_estudiantes', 'crear_estudiantes',
                'ver_cursos', 'editar_cursos',
                'ver_periodos',
                'ver_acudientes', 'editar_acudientes',
                'ver_matriculas', 'crear_matriculas', 'editar_matriculas',
            ])->get(),

            'coordinador_convivencia' => Permission::whereIn('name', [
                'ver_estudiantes', 'editar_estudiantes',
                'ver_acudientes', 'editar_acudientes',
                'ver_cursos',
            ])->get(),

            'secretaria' => Permission::whereIn('name', [
                'ver_estudiantes', 'crear_estudiantes', 'editar_estudiantes',
                'ver_acudientes', 'crear_acudientes', 'editar_acudientes',
                'ver_matriculas', 'crear_matriculas', 'editar_matriculas',
                'ver_cursos',
                'ver_periodos',
            ])->get(),

            'director_grupo' => Permission::whereIn('name', [
                'ver_estudiantes',
                'ver_acudientes',
                'ver_cursos',
                'ver_matriculas',
            ])->get(),

            'docente' => Permission::whereIn('name', [
                'ver_estudiantes',
                'ver_cursos',
            ])->get(),

            'acudiente' => collect(),
        ];

        foreach ($config as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }
    }
}
