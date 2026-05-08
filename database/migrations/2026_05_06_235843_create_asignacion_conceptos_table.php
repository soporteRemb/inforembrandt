<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignacion_conceptos', function (Blueprint $table) {

            $table->id();

            // SEDE
            $table->foreignId('sede_id')
                ->constrained('sedes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // PERIODO LECTIVO
            $table->foreignId('periodo_lectivo_id')
                ->constrained('periodos_lectivos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // GRADO
            $table->string('grado', 100);

            // CONCEPTO
            $table->foreignId('concepto_cobro_id')
                ->constrained('concepto_cobros')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // TARIFAS
            $table->unsignedBigInteger('tarifa_ordinaria')->default(0);

            $table->unsignedBigInteger('tarifa_extemporanea')->default(0);

            // ORDEN DE COBRO
            $table->unsignedTinyInteger('orden')->default(0);

            // ESTADO
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_conceptos');
    }
};