<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saldos_favor_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('periodo_lectivo_id')->constrained('periodos_lectivos')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('saldo_disponible', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['sede_id', 'periodo_lectivo_id', 'student_id'], 'saldos_favor_sede_periodo_estudiante_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldos_favor_estudiantes');
    }
};
