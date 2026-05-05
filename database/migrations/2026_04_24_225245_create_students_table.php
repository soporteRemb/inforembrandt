<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained()->cascadeOnDelete();
            $table->foreignId('periodo_lectivo_id')->constrained('periodos_lectivos')->cascadeOnDelete();

            $table->string('codigo')->nullable();
            $table->string('tipo_documento');
            $table->string('documento')->unique();
            $table->string('apellidos');
            $table->string('nombres');
            $table->string('sexo');

            $table->date('fecha_nacimiento')->nullable();
            $table->string('ciudad_nacimiento')->nullable();
            $table->integer('numero_hermanos')->nullable();
            $table->string('correo')->nullable();

            $table->string('eps')->nullable();
            $table->string('rh')->nullable();

            $table->string('estado')->default('activo');
            $table->date('fecha_matricula')->nullable();

            $table->string('ultimo_grado')->nullable();
            $table->string('institucion_anterior')->nullable();
            $table->string('foto')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
