<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notas_estudiantes', function (Blueprint $table) {
            if (! Schema::hasColumn('notas_estudiantes', 'mejoramiento_01')) {
                $table->string('mejoramiento_01')->nullable()->after('mejoramiento');
            }

            if (! Schema::hasColumn('notas_estudiantes', 'mejoramiento_02')) {
                $table->string('mejoramiento_02')->nullable()->after('mejoramiento_01');
            }

            if (! Schema::hasColumn('notas_estudiantes', 'mejoramiento_03')) {
                $table->string('mejoramiento_03')->nullable()->after('mejoramiento_02');
            }

            if (! Schema::hasColumn('notas_estudiantes', 'mejoramiento_04')) {
                $table->string('mejoramiento_04')->nullable()->after('mejoramiento_03');
            }

            if (! Schema::hasColumn('notas_estudiantes', 'pgc')) {
                $table->string('pgc')->nullable()->after('mejoramiento_04');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notas_estudiantes', function (Blueprint $table) {
            foreach ([
                'pgc',
                'mejoramiento_04',
                'mejoramiento_03',
                'mejoramiento_02',
                'mejoramiento_01',
            ] as $column) {
                if (Schema::hasColumn('notas_estudiantes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};