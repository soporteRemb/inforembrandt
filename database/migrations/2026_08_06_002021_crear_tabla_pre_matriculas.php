<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_matriculas', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Control del formulario
            |--------------------------------------------------------------------------
            */
            $table->string('numero_formulario', 80)->unique();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('sede_id')
                ->constrained('sedes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('periodo_lectivo_id')
                ->constrained('periodos_lectivos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('estado', [
                'pendiente',
                'completado',
                'vencido',
            ])->default('pendiente');

            $table->dateTime('fecha_habilitacion');
            $table->dateTime('fecha_limite');
            $table->dateTime('fecha_envio')->nullable();

            $table->foreignId('creado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('actualizado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Datos del estudiante
            |--------------------------------------------------------------------------
            */
            $table->string('nombres', 150)->nullable();
            $table->string('apellidos', 150)->nullable();

            $table->string('tipo_documento', 60)->nullable();
            $table->string('documento', 50)->nullable();

            $table->string('ciudad_expedicion', 120)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->unsignedTinyInteger('edad')->nullable();
            $table->string('ciudad_nacimiento', 120)->nullable();

            $table->string('genero', 30)->nullable();
            $table->unsignedTinyInteger('numero_hermanos')
                ->nullable()
                ->default(0);

            $table->string('telefono', 40)->nullable();
            $table->string('correo', 180)->nullable();
            $table->string('direccion', 255)->nullable();

            $table->string('rh', 10)->nullable();

            $table->foreignId('eps_id')
                ->nullable()
                ->constrained('eps')
                ->nullOnDelete();

            $table->string('telefono_emergencia', 40)->nullable();

            $table->string('grado_aspira', 40)->nullable();
            $table->string('institucion_anterior', 180)->nullable();

            $table->enum('condicion_ingreso', [
                'nuevo',
                'antiguo',
                'repitente',
            ])->nullable();

            /*
            |--------------------------------------------------------------------------
            | Datos del padre
            |--------------------------------------------------------------------------
            */
            $table->string('padre_nombre', 180)->nullable();
            $table->string('padre_telefono', 40)->nullable();
            $table->string('padre_tipo_documento', 60)->nullable();
            $table->string('padre_documento', 50)->nullable();
            $table->string('padre_lugar_trabajo', 180)->nullable();
            $table->string('padre_correo', 180)->nullable();
            $table->string('padre_direccion', 255)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Datos de la madre
            |--------------------------------------------------------------------------
            */
            $table->string('madre_nombre', 180)->nullable();
            $table->string('madre_telefono', 40)->nullable();
            $table->string('madre_tipo_documento', 60)->nullable();
            $table->string('madre_documento', 50)->nullable();
            $table->string('madre_lugar_trabajo', 180)->nullable();
            $table->string('madre_correo', 180)->nullable();
            $table->string('madre_direccion', 255)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Datos del acudiente
            |--------------------------------------------------------------------------
            */
            $table->enum('acudiente_origen', [
                'padre',
                'madre',
                'otro',
            ])->nullable();

            $table->string('acudiente_parentesco', 80)->nullable();
            $table->string('acudiente_nombre', 180)->nullable();
            $table->string('acudiente_telefono', 40)->nullable();
            $table->string('acudiente_tipo_documento', 60)->nullable();
            $table->string('acudiente_documento', 50)->nullable();
            $table->string('acudiente_lugar_trabajo', 180)->nullable();
            $table->string('acudiente_correo', 180)->nullable();
            $table->string('acudiente_direccion', 255)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Uso administrativo
            |--------------------------------------------------------------------------
            */
            $table->text('observaciones')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */
            $table->index([
                'sede_id',
                'periodo_lectivo_id',
                'estado',
            ], 'pre_matriculas_contexto_estado_idx');

            $table->index('documento');
            $table->index('grado_aspira');
            $table->index('fecha_limite');
            $table->index('fecha_envio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_matriculas');
    }
};