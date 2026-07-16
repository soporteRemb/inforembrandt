<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consecutivos_recibos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedSmallInteger('anio');
            $table->unsignedBigInteger('ultimo_numero')->default(0);
            $table->timestamps();

            $table->unique(['sede_id', 'anio'], 'consecutivos_recibos_sede_anio_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consecutivos_recibos');
    }
};
