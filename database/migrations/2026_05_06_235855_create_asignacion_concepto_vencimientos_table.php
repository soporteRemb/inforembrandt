<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignacion_concepto_vencimientos', function (Blueprint $table) {

            $table->id();

            // ASIGNACION
            $table->foreignId('asignacion_concepto_id')
                ->constrained('asignacion_conceptos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // MES
            $table->string('mes', 20);

            // FECHA VENCIMIENTO
            $table->date('fecha_vencimiento')->nullable();

            // PORCENTAJE
            $table->unsignedTinyInteger('porcentaje')->default(0);

            // DIAS
            $table->unsignedTinyInteger('dias')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_concepto_vencimientos');
    }
};