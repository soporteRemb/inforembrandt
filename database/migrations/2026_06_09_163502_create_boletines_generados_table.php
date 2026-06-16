<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boletines_generados', function (Blueprint $table) {
            $table->id();

            $table->foreignId('periodo_lectivo_id')->constrained('periodos_lectivos')->cascadeOnDelete();
            $table->foreignId('periodo_academico_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();

            $table->json('codigos_perfil')->nullable();
            $table->json('codigos_acompanamiento')->nullable();

            $table->text('observaciones')->nullable();

            $table->string('estado', 30)->default('borrador');
            $table->string('pdf_path')->nullable();

            $table->timestamp('generado_en')->nullable();
            $table->timestamp('publicado_en')->nullable();

            $table->timestamps();

            $table->unique(
                ['periodo_lectivo_id', 'periodo_academico_id', 'course_id', 'student_id'],
                'boletin_generado_unico_estudiante_periodo'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletines_generados');
    }
};