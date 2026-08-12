<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_matricula_documentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pre_matricula_id')
                ->constrained('pre_matriculas')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Tipo documental
            |--------------------------------------------------------------------------
            |
            | Código interno estable. Ejemplos:
            | registro_civil
            | documento_estudiante
            | certificado_medico
            | documento_padre
            |
            */
            $table->string('tipo_documento', 100);

            /*
            |--------------------------------------------------------------------------
            | Datos del archivo
            |--------------------------------------------------------------------------
            */
            $table->string('nombre_original', 255);
            $table->string('ruta_archivo', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamano')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Origen
            |--------------------------------------------------------------------------
            |
            | temporal       = cargado por padre/acudiente
            | administrativo = cargado posteriormente por el colegio
            |
            */
            $table->enum('origen', [
                'temporal',
                'administrativo',
            ])->default('temporal');

            /*
            |--------------------------------------------------------------------------
            | Usuario que realizó la carga
            |--------------------------------------------------------------------------
            */
            $table->foreignId('subido_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */
            $table->index(
                ['pre_matricula_id', 'tipo_documento'],
                'pre_matricula_documentos_tipo_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_matricula_documentos');
    }
};
