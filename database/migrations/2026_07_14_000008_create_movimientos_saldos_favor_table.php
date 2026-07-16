<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_saldos_favor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saldo_favor_estudiante_id')->constrained('saldos_favor_estudiantes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('recibo_pago_id')->nullable()->constrained('recibos_pago')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('aplicacion_pago_id')->nullable()->constrained('aplicaciones_pago')->cascadeOnUpdate()->nullOnDelete();
            $table->string('tipo_movimiento', 30);
            $table->decimal('valor', 15, 2);
            $table->decimal('saldo_anterior', 15, 2)->default(0);
            $table->decimal('saldo_posterior', 15, 2)->default(0);
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('registrado_en')->nullable();
            $table->string('detalle', 500)->nullable();
            $table->timestamps();

            $table->index(['saldo_favor_estudiante_id', 'registrado_en'], 'mov_saldo_favor_estudiante_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_saldos_favor');
    }
};
