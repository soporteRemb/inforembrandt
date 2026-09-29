<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignacion_costos_estudiantes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('sede_id')
                ->constrained('sedes')
                ->cascadeOnDelete();

            $table->foreignId('periodo_lectivo_id')
                ->constrained('periodos_lectivos')
                ->cascadeOnDelete();

            $table->decimal('saldo_anterior', 15, 2)->default(0);
            $table->decimal('matricula', 15, 2)->default(0);
            $table->decimal('costos_academicos', 15, 2)->default(0);

            $table->decimal('pension_febrero', 15, 2)->default(0);
            $table->decimal('pension_marzo', 15, 2)->default(0);
            $table->decimal('pension_abril', 15, 2)->default(0);
            $table->decimal('pension_mayo', 15, 2)->default(0);
            $table->decimal('pension_junio', 15, 2)->default(0);
            $table->decimal('pension_julio', 15, 2)->default(0);
            $table->decimal('pension_agosto', 15, 2)->default(0);
            $table->decimal('pension_septiembre', 15, 2)->default(0);
            $table->decimal('pension_octubre', 15, 2)->default(0);
            $table->decimal('pension_noviembre', 15, 2)->default(0);

            $table->decimal('otros_costos_academicos', 15, 2)->default(0);

            $table->timestamps();

            $table->unique(
                ['student_id', 'periodo_lectivo_id'],
                'asignacion_costos_estudiante_periodo_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_costos_estudiantes');
    }
};