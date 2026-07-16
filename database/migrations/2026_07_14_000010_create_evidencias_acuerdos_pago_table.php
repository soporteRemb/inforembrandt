<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidencias_acuerdos_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acuerdo_pago_estudiante_id')->constrained('acuerdos_pago_estudiantes')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nombre_original', 255);
            $table->string('ruta', 500);
            $table->string('tipo_archivo', 150)->nullable();
            $table->unsignedBigInteger('tamano')->nullable();
            $table->foreignId('cargado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cargado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidencias_acuerdos_pago');
    }
};
