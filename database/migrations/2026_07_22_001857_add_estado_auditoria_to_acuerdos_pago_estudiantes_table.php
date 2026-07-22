<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'acuerdos_pago_estudiantes',
            function (Blueprint $table) {
                $table->foreignId('estado_modificado_por')
                    ->nullable()
                    ->after('estado')
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('estado_modificado_en')
                    ->nullable()
                    ->after('estado_modificado_por');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'acuerdos_pago_estudiantes',
            function (Blueprint $table) {
                $table->dropForeign([
                    'estado_modificado_por',
                ]);

                $table->dropColumn([
                    'estado_modificado_por',
                    'estado_modificado_en',
                ]);
            }
        );
    }
};