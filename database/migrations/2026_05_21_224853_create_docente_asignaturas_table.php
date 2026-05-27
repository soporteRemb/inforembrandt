<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente_asignaturas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('docente_id')
                ->constrained('docentes')
                ->cascadeOnDelete();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->foreignId('pensum_academico_id')
                ->constrained('pensum_academicos')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['docente_id', 'course_id', 'pensum_academico_id'],
                'docente_asignatura_unica'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente_asignaturas');
    }
};