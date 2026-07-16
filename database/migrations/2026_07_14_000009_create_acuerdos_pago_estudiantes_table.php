<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acuerdos_pago_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('periodo_lectivo_id')->constrained('periodos_lectivos')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->restrictOnDelete();
            $table->longText('texto_acuerdo');
            $table->string('persona_acuerdo', 180)->nullable();
            $table->string('parentesco', 100)->nullable();
            $table->date('fecha_compromiso')->nullable();
            $table->decimal('valor_comprometido', 15, 2)->nullable();
            $table->string('estado', 30)->default('vigente');
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('registrado_en')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('anulado_en')->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'periodo_lectivo_id', 'estado'], 'acuerdos_pago_estudiante_periodo_estado_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acuerdos_pago_estudiantes');
    }
};
