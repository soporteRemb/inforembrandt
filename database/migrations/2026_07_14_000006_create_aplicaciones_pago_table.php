<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aplicaciones_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recibo_pago_id')->constrained('recibos_pago')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('movimiento_cartera_estudiante_id')
                ->constrained('movimientos_cartera_estudiante')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->decimal('valor_aplicado', 15, 2);
            $table->decimal('saldo_anterior', 15, 2)->default(0);
            $table->decimal('saldo_posterior', 15, 2)->default(0);
            $table->timestamps();

            $table->index('movimiento_cartera_estudiante_id', 'aplicaciones_pago_movimiento_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aplicaciones_pago');
    }
};
