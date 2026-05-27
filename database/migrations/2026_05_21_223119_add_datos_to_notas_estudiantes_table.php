<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notas_estudiantes', function (Blueprint $table) {

            if (! Schema::hasColumn('notas_estudiantes', 'student_id')) {
                $table->foreignId('student_id')
                    ->after('id')
                    ->constrained('students')
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('notas_estudiantes', 'pensum_academico_id')) {
                $table->foreignId('pensum_academico_id')
                    ->after('student_id')
                    ->constrained('pensum_academicos')
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('notas_estudiantes', 'periodo')) {
                $table->integer('periodo')
                    ->after('pensum_academico_id');
            }

            if (! Schema::hasColumn('notas_estudiantes', 'nota')) {
                $table->decimal('nota', 5, 2)
                    ->nullable()
                    ->after('periodo');
            }

            if (! Schema::hasColumn('notas_estudiantes', 'fallas')) {
                $table->integer('fallas')
                    ->default(0)
                    ->after('nota');
            }

            if (! Schema::hasColumn('notas_estudiantes', 'mejoramiento')) {
                $table->decimal('mejoramiento', 5, 2)
                    ->nullable()
                    ->after('fallas');
            }

            if (! Schema::hasColumn('notas_estudiantes', 'observacion')) {
                $table->text('observacion')
                    ->nullable()
                    ->after('mejoramiento');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notas_estudiantes', function (Blueprint $table) {

            if (Schema::hasColumn('notas_estudiantes', 'pensum_academico_id')) {
                $table->dropConstrainedForeignId('pensum_academico_id');
            }

            if (Schema::hasColumn('notas_estudiantes', 'student_id')) {
                $table->dropConstrainedForeignId('student_id');
            }

            foreach ([
                'observacion',
                'mejoramiento',
                'fallas',
                'nota',
                'periodo',
            ] as $column) {

                if (Schema::hasColumn('notas_estudiantes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};