<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_cartera_estudiante', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
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

            $table->foreignId('course_id')
                ->nullable()
                ->constrained('courses')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('concepto_cobro_id')
                ->nullable()
                ->constrained('concepto_cobros')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('grado', 100)->nullable();

            $table->string('tipo_movimiento', 30)->default('causacion');
            $table->string('tipo_concepto', 30)->nullable();

            $table->string('mes', 20)->nullable();
            $table->unsignedTinyInteger('mes_numero')->nullable();

            $table->decimal('valor_base', 15, 2)->default(0);
            $table->decimal('valor_personalizado', 15, 2)->default(0);
            $table->decimal('valor', 15, 2)->default(0);

            $table->string('estado', 30)->default('activo');

            $table->string('descripcion', 255)->nullable();
            $table->string('referencia', 100)->nullable();

            $table->foreignId('causado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('causado_en')->nullable();

            $table->foreignId('reversado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reversado_en')->nullable();
            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->index([
                'student_id',
                'periodo_lectivo_id',
                'concepto_cobro_id',
                'mes_numero',
                'estado',
            ], 'mov_cartera_estudiante_concepto_mes_estado_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_cartera_estudiante');
    }
};