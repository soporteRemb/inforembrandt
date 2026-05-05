<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('students');

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained()->cascadeOnDelete();
            $table->foreignId('periodo_lectivo_id')->constrained('periodos_lectivos')->cascadeOnDelete();
            $table->unsignedBigInteger('course_id')->nullable();
            
            // Código / folio
            $table->string('codigo')->nullable();
            $table->string('folio')->nullable();

            // Foto
            $table->string('foto')->nullable();

            // Nombres y apellidos separados
            $table->string('primer_apellido');
            $table->string('segundo_apellido')->nullable();
            $table->string('primer_nombre');
            $table->string('segundo_nombre')->nullable();

            // Documento
            $table->string('tipo_documento');
            $table->string('documento')->unique();
            $table->string('ciudad_expedicion')->nullable();

            // Datos personales
            $table->string('sexo');
            $table->date('fecha_nacimiento')->nullable();
            $table->integer('edad')->nullable();
            $table->string('ciudad_nacimiento')->nullable();
            $table->integer('numero_hermanos')->nullable();

            // Contacto
            $table->string('correo')->nullable();
            $table->string('telefono_familiar')->nullable();
            $table->string('telefono_emergencia')->nullable();

            // Médico
            $table->string('eps')->nullable();
            $table->string('rh')->nullable();

            // Matrícula
            $table->string('estado')->default('activo');
            $table->date('fecha_matricula')->nullable();
            $table->string('control')->nullable();
            $table->string('referido')->nullable();
            $table->string('parentesco_matricula')->nullable();

            // Historial
            $table->string('ultimo_grado')->nullable();
            $table->string('anio_ultimo_grado')->nullable();
            $table->string('institucion_anterior')->nullable();

            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};