<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_matricula_historiales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pre_matricula_id')
                ->constrained('pre_matriculas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('accion', 80);

            $table->text('descripcion')->nullable();

            $table->string('campo', 120)->nullable();

            $table->text('valor_anterior')->nullable();

            $table->text('valor_nuevo')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index([
                'pre_matricula_id',
                'created_at',
            ], 'pre_matricula_historial_fecha_idx');

            $table->index('accion');
            $table->index('campo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_matricula_historiales');
    }
};