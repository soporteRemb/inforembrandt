<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impresiones_recibos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recibo_pago_id')->constrained('recibos_pago')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('tipo', 20)->default('original');
            $table->unsignedInteger('numero_reimpresion')->default(0);
            $table->string('medio', 30)->default('pdf');
            $table->string('ruta_pdf', 500)->nullable();
            $table->foreignId('generado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generado_en')->nullable();
            $table->timestamps();

            $table->unique(['recibo_pago_id', 'numero_reimpresion'], 'impresiones_recibo_numero_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impresiones_recibos');
    }
};
