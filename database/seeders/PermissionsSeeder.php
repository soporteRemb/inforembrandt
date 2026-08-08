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

            'Estudiantes' => [
                'ver_estudiantes',
                'crear_estudiantes',
                'editar_estudiantes',
                'eliminar_estudiantes',
            ],

            'Usuarios' => [
                'ver_usuarios',
                'crear_usuarios',
                'editar_usuarios',
                'eliminar_usuarios',
            ],

            'Cursos' => [
                'ver_cursos',
                'crear_cursos',
                'editar_cursos',
                'eliminar_cursos',
            ],

            'Sedes' => [
                'ver_sedes',
                'crear_sedes',
                'editar_sedes',
                'eliminar_sedes',
            ],

            'Periodos' => [
                'ver_periodos',
                'crear_periodos',
                'editar_periodos',
                'eliminar_periodos',
            ],

            'Acudientes' => [
                'ver_acudientes',
                'crear_acudientes',
                'editar_acudientes',
                'eliminar_acudientes',
            ],

            'Matrículas' => [
                'ver_matriculas',
                'crear_matriculas',
                'editar_matriculas',
                'eliminar_matriculas',
            ],

            'Pre-matrículas' => [
                'diligenciar_formulario_pre_matricula',
                'ver_pre_matriculas',
                'editar_pre_matriculas',
                'exportar_pre_matriculas',
                'ver_historial_pre_matriculas',
            ],

            'Roles' => [
                'ver_roles',
                'editar_roles',
            ],

            'Conceptos de Cobro' => [
                'ver_conceptos_cobro',
                'crear_conceptos_cobro',
                'editar_conceptos_cobro',
                'eliminar_conceptos_cobro',
            ],

            'Asignación Costos' => [
                'ver_asignacion_costos',
                'crear_asignacion_costos',
                'editar_asignacion_costos',
                'eliminar_asignacion_costos',
            ],

            'Notas' => [
                'ver_notas',
            ],

            'Pensum Académico' => [
                'ver_pensum',
            ],

            'Docentes' => [
                'ver_docentes',
            ],

            'Desempeños' => [
                'ver_desempenos',
            ],

            'Boletines Administrativos' => [
                'ver_boletines_administrativos',
            ],

            'Causación de Costos' => [
                'ver_causacion_costos',
            ],

            'Pagos' => [
                'ver_pagos',
            ],

            'Otros Parámetros' => [
                'ver_otros_parametros',
            ],

            'Importación de Datos' => [
                'ver_importacion_datos',
            ],

            'Boletines Acudientes' => [
                'ver_boletines_acudientes',
            ],

        ];
    }

    public function run(): void
    {
        foreach (self::permisos() as $permisos) {
            foreach ($permisos as $permiso) {
                Permission::firstOrCreate([
                    'name' => $permiso,
                    'guard_name' => 'web',
                ]);
            }
        }
    }
}
