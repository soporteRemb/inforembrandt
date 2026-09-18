<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'firma_electronica_estudiantes',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->foreignId('student_id')
                    ->constrained('students')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('iniciada_por')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->string('proveedor', 50)
                    ->default('click_and_sign');

                $table
                    ->string('estado', 50)
                    ->default('pendiente');

                $table
                    ->string('request_id', 100)
                    ->nullable()
                    ->unique();

                $table
                    ->string('contract_id', 150)
                    ->nullable()
                    ->unique();

                $table
                    ->string('signature_id', 150)
                    ->nullable()
                    ->index();

                $table
                    ->unsignedBigInteger('config_id')
                    ->nullable();

                $table
                    ->string('nombre_archivo_original')
                    ->nullable();

                $table
                    ->string('nombre_archivo_firmado')
                    ->nullable();

                $table
                    ->text('ultimo_error')
                    ->nullable();

                $table
                    ->json('respuesta_inicio')
                    ->nullable();

                $table
                    ->json('respuesta_estado')
                    ->nullable();

                $table
                    ->timestamp('iniciada_en')
                    ->nullable();

                $table
                    ->timestamp('firmada_en')
                    ->nullable();

                $table
                    ->timestamp('rechazada_en')
                    ->nullable();

                $table
                    ->timestamp('expirada_en')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'student_id',
                    'estado',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'firma_electronica_estudiantes'
        );
    }
};