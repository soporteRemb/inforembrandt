<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pensum_academicos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sede_id')
                ->constrained('sedes')
                ->cascadeOnDelete();

            $table->foreignId('periodo_lectivo_id')
                ->constrained('periodos_lectivos')
                ->cascadeOnDelete();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->foreignId('docente_id')
                ->nullable()
                ->constrained('docentes')
                ->nullOnDelete();

            $table->string('codigo')->nullable();
            $table->integer('orden')->nullable();

            $table->string('nombre');
            $table->string('nombre_corto')->nullable();

            $table->string('tipo')->default('asignatura'); 
            $table->integer('intensidad_horaria')->nullable();

            $table->string('forma_evaluar')->default('bimestral');
            $table->string('estado')->default('activo');

            $table->timestamps();

            $table->index(['sede_id', 'periodo_lectivo_id']);
            $table->index(['course_id', 'docente_id']);
            $table->index('codigo');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pensum_academicos');
    }
};