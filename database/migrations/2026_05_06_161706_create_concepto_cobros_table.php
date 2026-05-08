<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concepto_cobros', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sede_id')
                ->constrained('sedes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('periodo_lectivo_id')
                ->constrained('periodos_lectivos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unsignedTinyInteger('codigo');

            $table->string('descripcion', 150);

            $table->enum('tipo_movimiento', ['ingreso', 'egreso'])
                ->default('ingreso');

            $table->enum('control', ['interno', 'externo'])
                ->default('interno');

            $table->decimal('impuesto', 5, 2)
                ->default(0);

            $table->boolean('facturar')
                ->default(false);

            $table->boolean('obligatorio')
                ->default(false);

            $table->boolean('activo')
                ->default(true);

            $table->timestamps();

            $table->unique(
                ['sede_id', 'periodo_lectivo_id', 'codigo'],
                'concepto_cobros_sede_periodo_codigo_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concepto_cobros');
    }
};