<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_estudiantes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('pensum_academico_id')
                ->constrained('pensum_academicos')
                ->cascadeOnDelete();

            $table->integer('periodo');

            $table->decimal('nota', 5, 2)->nullable();
            $table->integer('fallas')->default(0);
            $table->string('mejoramiento')->nullable();
            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->unique(
                ['student_id', 'pensum_academico_id', 'periodo'],
                'nota_estudiante_unica'
            );

            $table->index('student_id');
            $table->index('pensum_academico_id');
            $table->index('periodo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_estudiantes');
    }
};