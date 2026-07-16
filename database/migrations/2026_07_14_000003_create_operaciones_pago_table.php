<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operaciones_pago', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('periodo_lectivo_id')->constrained('periodos_lectivos')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('recibido_de', 180)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total_descuentos', 15, 2)->default(0);
            $table->decimal('total_recibido', 15, 2)->default(0);
            $table->string('estado', 30)->default('confirmada');
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('registrado_en')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('anulado_en')->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'periodo_lectivo_id', 'estado'], 'operaciones_pago_estudiante_periodo_estado_idx');
            $table->index(['sede_id', 'registrado_en'], 'operaciones_pago_sede_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operaciones_pago');
    }
};
