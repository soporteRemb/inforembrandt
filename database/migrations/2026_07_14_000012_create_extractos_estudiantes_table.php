<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extractos_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('periodo_lectivo_id')->constrained('periodos_lectivos')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('fecha_corte');
            $table->string('ruta_pdf', 500)->nullable();
            $table->foreignId('generado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generado_en')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'periodo_lectivo_id', 'fecha_corte'], 'extractos_estudiante_periodo_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extractos_estudiantes');
    }
};
