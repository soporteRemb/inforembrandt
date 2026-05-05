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
        Schema::create('student_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('tipo');          // pre_matricula, hoja_matricula, pagare, etc.
            $table->timestamp('generado_at');
            $table->string('generado_por'); // nombre del usuario que lo generó
            $table->unique(['student_id', 'tipo']); // un registro por tipo por estudiante
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_documentos');
    }
};
