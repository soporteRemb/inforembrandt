<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formas_pago', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->boolean('requiere_referencia')->default(false);
            $table->boolean('requiere_fecha_consignacion')->default(false);
            $table->unsignedSmallInteger('orden')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('nombre', 'formas_pago_nombre_unique');
            $table->index(['activo', 'orden'], 'formas_pago_activo_orden_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formas_pago');
    }
};
