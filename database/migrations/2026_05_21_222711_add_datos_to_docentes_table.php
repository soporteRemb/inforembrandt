<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            if (! Schema::hasColumn('docentes', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('docentes', 'identificacion')) {
                $table->string('identificacion')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('docentes', 'apellidos')) {
                $table->string('apellidos')->after('identificacion');
            }

            if (! Schema::hasColumn('docentes', 'nombres')) {
                $table->string('nombres')->after('apellidos');
            }

            if (! Schema::hasColumn('docentes', 'telefono')) {
                $table->string('telefono')->nullable()->after('nombres');
            }

            if (! Schema::hasColumn('docentes', 'correo')) {
                $table->string('correo')->nullable()->after('telefono');
            }

            if (! Schema::hasColumn('docentes', 'direccion')) {
                $table->string('direccion')->nullable()->after('correo');
            }

            if (! Schema::hasColumn('docentes', 'especialidad')) {
                $table->string('especialidad')->nullable()->after('direccion');
            }

            if (! Schema::hasColumn('docentes', 'escalafon')) {
                $table->string('escalafon')->nullable()->after('especialidad');
            }

            if (! Schema::hasColumn('docentes', 'direccion_curso_id')) {
                $table->foreignId('direccion_curso_id')
                    ->nullable()
                    ->after('escalafon')
                    ->constrained('courses')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('docentes', 'estado')) {
                $table->string('estado')->default('activo')->after('direccion_curso_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            if (Schema::hasColumn('docentes', 'direccion_curso_id')) {
                $table->dropConstrainedForeignId('direccion_curso_id');
            }

            foreach ([
                'estado',
                'escalafon',
                'especialidad',
                'direccion',
                'correo',
                'telefono',
                'nombres',
                'apellidos',
                'identificacion',
            ] as $column) {
                if (Schema::hasColumn('docentes', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('docentes', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};