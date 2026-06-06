<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boletin_recomendaciones_estudiante', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('boletin_recomendacion_id');

            $table->unsignedTinyInteger('periodo_academico');
            $table->unsignedTinyInteger('orden')->default(1);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->foreign('student_id', 'bre_student_fk')
                ->references('id')
                ->on('students')
                ->cascadeOnDelete();

            $table->foreign('boletin_recomendacion_id', 'bre_recomendacion_fk')
                ->references('id')
                ->on('boletin_recomendaciones')
                ->cascadeOnDelete();

            $table->unique([
                'student_id',
                'boletin_recomendacion_id',
                'periodo_academico',
            ], 'bre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletin_recomendaciones_estudiante');
    }
};