<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otros_costos_estudiante', function (Blueprint $table) {

            $table->id();

            $table->foreignId('ficha_costo_estudiante_id')
                ->constrained('fichas_costos_estudiantes')
                ->cascadeOnDelete();

            $table->foreignId('concepto_cobro_id')
                ->constrained('concepto_cobros')
                ->restrictOnDelete();

            $table->string('nombre_concepto', 150);

            $table->unsignedBigInteger('valor_base')
                ->default(0);

            $table->unsignedBigInteger('valor_personalizado')
                ->default(0);

            $table->boolean('modificado_manual')
                ->default(false);

            $table->timestamps();

            $table->unique(
                ['ficha_costo_estudiante_id', 'concepto_cobro_id'],
                'otro_costo_estudiante_concepto_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otros_costos_estudiante');
    }
};