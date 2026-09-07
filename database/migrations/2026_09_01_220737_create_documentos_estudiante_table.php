<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_estudiante', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->string('tipo_documento', 100);

            $table->string('nombre_original');

            $table->string('ruta_archivo');

            $table->string('mime_type', 150)->nullable();

            $table->unsignedBigInteger('tamano')->nullable();

            $table->string('origen', 30)->default('administrativo');

            $table->foreignId('subido_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('pre_matricula_documento_id')
                ->nullable()
                ->constrained('pre_matricula_documentos')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                [
                    'student_id',
                    'tipo_documento',
                    'pre_matricula_documento_id',
                ],
                'documentos_estudiante_origen_unique'
            );

            $table->index(
                [
                    'student_id',
                    'tipo_documento',
                ],
                'documentos_estudiante_tipo_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_estudiante');
    }
};