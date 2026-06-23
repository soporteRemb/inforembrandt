<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_student', function (Blueprint $table) {
            $table->id();

            $table->foreignId('guardian_id')
                ->constrained('guardians')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->string('tipo', 50); // padre, madre, acudiente, deudor_economico
            $table->string('estado', 20)->default('activo');

            $table->timestamps();

            $table->unique(['guardian_id', 'student_id', 'tipo'], 'guardian_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_student');
    }
};