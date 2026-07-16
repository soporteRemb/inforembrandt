<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibos_formas_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recibo_pago_id')->constrained('recibos_pago')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('forma_pago_id')->constrained('formas_pago')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('valor', 15, 2);
            $table->string('numero_referencia', 150)->nullable();
            $table->date('fecha_consignacion')->nullable();
            $table->timestamps();

            $table->index(['recibo_pago_id', 'forma_pago_id'], 'recibos_formas_pago_recibo_forma_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recibos_formas_pago');
    }
};
