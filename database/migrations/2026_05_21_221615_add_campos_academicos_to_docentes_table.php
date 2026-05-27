<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            if (! Schema::hasColumn('docentes', 'direccion')) {
                $table->string('direccion')->nullable()->after('correo');
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
        });
    }

    public function down(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            if (Schema::hasColumn('docentes', 'direccion_curso_id')) {
                $table->dropConstrainedForeignId('direccion_curso_id');
            }

            if (Schema::hasColumn('docentes', 'escalafon')) {
                $table->dropColumn('escalafon');
            }

            if (Schema::hasColumn('docentes', 'direccion')) {
                $table->dropColumn('direccion');
            }
        });
    }
};