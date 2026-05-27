<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pensum_academicos', function (Blueprint $table) {
            if (! Schema::hasColumn('pensum_academicos', 'sede_id')) {
                $table->foreignId('sede_id')->after('id')->constrained('sedes')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('pensum_academicos', 'periodo_lectivo_id')) {
                $table->foreignId('periodo_lectivo_id')->after('sede_id')->constrained('periodos_lectivos')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('pensum_academicos', 'course_id')) {
                $table->foreignId('course_id')->after('periodo_lectivo_id')->constrained('courses')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('pensum_academicos', 'docente_id')) {
                $table->foreignId('docente_id')->nullable()->after('course_id')->constrained('docentes')->nullOnDelete();
            }

            if (! Schema::hasColumn('pensum_academicos', 'codigo')) {
                $table->string('codigo')->after('docente_id');
            }

            if (! Schema::hasColumn('pensum_academicos', 'orden')) {
                $table->integer('orden')->after('codigo');
            }

            if (! Schema::hasColumn('pensum_academicos', 'nombre')) {
                $table->string('nombre')->after('orden');
            }

            if (! Schema::hasColumn('pensum_academicos', 'nombre_corto')) {
                $table->string('nombre_corto')->after('nombre');
            }

            if (! Schema::hasColumn('pensum_academicos', 'tipo')) {
                $table->string('tipo')->default('asignatura')->after('nombre_corto');
            }

            if (! Schema::hasColumn('pensum_academicos', 'intensidad_horaria')) {
                $table->integer('intensidad_horaria')->after('tipo');
            }

            if (! Schema::hasColumn('pensum_academicos', 'forma_evaluar')) {
                $table->string('forma_evaluar')->default('bimestral')->after('intensidad_horaria');
            }

            if (! Schema::hasColumn('pensum_academicos', 'estado')) {
                $table->string('estado')->default('activo')->after('forma_evaluar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pensum_academicos', function (Blueprint $table) {
            if (Schema::hasColumn('pensum_academicos', 'docente_id')) {
                $table->dropConstrainedForeignId('docente_id');
            }

            if (Schema::hasColumn('pensum_academicos', 'course_id')) {
                $table->dropConstrainedForeignId('course_id');
            }

            if (Schema::hasColumn('pensum_academicos', 'periodo_lectivo_id')) {
                $table->dropConstrainedForeignId('periodo_lectivo_id');
            }

            if (Schema::hasColumn('pensum_academicos', 'sede_id')) {
                $table->dropConstrainedForeignId('sede_id');
            }

            foreach ([
                'estado',
                'forma_evaluar',
                'intensidad_horaria',
                'tipo',
                'nombre_corto',
                'nombre',
                'orden',
                'codigo',
            ] as $column) {
                if (Schema::hasColumn('pensum_academicos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};