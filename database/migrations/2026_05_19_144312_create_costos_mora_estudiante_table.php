<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('costos_mora_estudiante', function (Blueprint $table) {

            $table->id();

            $table->foreignId('ficha_costo_estudiante_id')
                ->constrained('fichas_costos_estudiantes')
                ->cascadeOnDelete();

            $table->string('mes', 20);

            $table->unsignedTinyInteger('mes_numero');

            $table->unsignedBigInteger('valor_mora')
                ->default(0);

            $table->boolean('modificado_manual')
                ->default(false);

            $table->timestamps();

            $table->unique(
                ['ficha_costo_estudiante_id', 'mes_numero'],
                'mora_estudiante_mes_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('costos_mora_estudiante');
    }
};