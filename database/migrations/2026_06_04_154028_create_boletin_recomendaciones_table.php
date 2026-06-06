<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boletin_recomendaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnDelete();
            $table->foreignId('periodo_lectivo_id')->constrained('periodos_lectivos')->cascadeOnDelete();

            $table->string('grado', 50);
            $table->unsignedTinyInteger('periodo_academico');

            $table->foreignId('pensum_academico_id')
                ->constrained('pensum_academicos')
                ->cascadeOnDelete();

            $table->string('tipo', 50);
            $table->unsignedInteger('codigo');
            $table->string('descripcion', 100);

            $table->boolean('activo')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique([
                'sede_id',
                'periodo_lectivo_id',
                'grado',
                'periodo_academico',
                'pensum_academico_id',
                'tipo',
                'codigo',
            ], 'boletin_recomendaciones_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletin_recomendaciones');
    }
};