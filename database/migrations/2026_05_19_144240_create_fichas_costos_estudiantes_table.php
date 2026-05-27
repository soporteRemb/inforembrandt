<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('fichas_costos_estudiantes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('sede_id')->constrained('sedes')->restrictOnDelete();
            $table->foreignId('periodo_lectivo_id')->constrained('periodos_lectivos')->restrictOnDelete();

            $table->string('tipo_pago')->nullable();
            $table->string('mes_causado')->nullable();

            $table->unsignedBigInteger('saldo_anterior')->default(0);
            $table->unsignedBigInteger('matricula')->default(0);
            $table->unsignedBigInteger('costos_academicos')->default(0);
            $table->unsignedBigInteger('deudas')->default(0);
            $table->unsignedBigInteger('otras_deudas')->default(0);
            $table->unsignedBigInteger('abonos')->default(0);
            $table->unsignedBigInteger('total_deuda')->default(0);

            $table->unsignedBigInteger('pension_inicial')->default(0);
            $table->string('pagare_no')->nullable();

            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'periodo_lectivo_id'], 'ficha_costos_student_periodo_unique');
        });
    }    

    public function down(): void
    {
        Schema::dropIfExists('fichas_costos_estudiantes');
    }
};
