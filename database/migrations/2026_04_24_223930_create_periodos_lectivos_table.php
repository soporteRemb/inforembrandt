<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_lectivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained()->cascadeOnDelete();
            $table->string('nombre'); // Ej: 2026
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado')->default('abierto'); // abierto, en_cierre, cerrado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_lectivos');
    }
};