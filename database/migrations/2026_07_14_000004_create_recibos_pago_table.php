<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibos_pago', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('operacion_pago_id')->constrained('operaciones_pago')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('periodo_lectivo_id')->constrained('periodos_lectivos')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('concepto_cobro_id')->nullable()->constrained('concepto_cobros')->cascadeOnUpdate()->nullOnDelete();

            $table->unsignedBigInteger('numero_recibo');
            $table->unsignedSmallInteger('anio');
            $table->string('tipo_registro', 30)->default('obligacion');
            $table->string('mes', 20)->nullable();
            $table->unsignedTinyInteger('mes_numero')->nullable();

            $table->decimal('valor_ordinario', 15, 2)->default(0);
            $table->foreignId('tipo_limite_extemporaneo_id')->nullable()->constrained('tipo_limite_extemporaneos')->cascadeOnUpdate()->nullOnDelete();
            $table->decimal('valor_vigente', 15, 2)->default(0);
            $table->decimal('penalizacion', 15, 2)->default(0);
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('valor_recibido', 15, 2)->default(0);
            $table->decimal('valor_aplicado', 15, 2)->default(0);
            $table->decimal('saldo_favor_generado', 15, 2)->default(0);

            $table->string('recibido_de', 180)->nullable();
            $table->string('detalle', 500)->nullable();
            $table->string('estado', 30)->default('confirmado');
            $table->foreignId('recibido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_pago')->nullable();

            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('anulado_en')->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->timestamps();

            $table->unique(['sede_id', 'anio', 'numero_recibo'], 'recibos_pago_sede_anio_numero_unique');
            $table->index(['student_id', 'periodo_lectivo_id', 'estado'], 'recibos_pago_estudiante_periodo_estado_idx');
            $table->index(['concepto_cobro_id', 'mes_numero'], 'recibos_pago_concepto_mes_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recibos_pago');
    }
};
