<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('periodos_academicos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('periodo_lectivo_id')
                ->constrained('periodos_lectivos')
                ->cascadeOnDelete();

            $table->string('numero', 1); // 1,2,3,4

            $table->string('nombre'); // Primer periodo, Segundo periodo...

            $table->enum('estado', ['abierto', 'cerrado'])
                ->default('abierto');

            $table->timestamps();

            $table->unique([
                'periodo_lectivo_id',
                'numero',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos_academicos');
    }
};
