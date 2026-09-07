<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pre_matriculas', function (Blueprint $table) {
            $table->foreignId('periodo_destino_id')
                ->nullable()
                ->after('periodo_lectivo_id')
                ->constrained('periodos_lectivos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('codigo', 50)
                ->nullable()
                ->after('documento');

            $table->index(
                [
                    'sede_id',
                    'periodo_destino_id',
                    'documento',
                ],
                'pre_matriculas_campania_documento_idx'
            );

            $table->index(
                'codigo',
                'pre_matriculas_codigo_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('pre_matriculas', function (Blueprint $table) {
            $table->dropIndex(
                'pre_matriculas_campania_documento_idx'
            );

            $table->dropIndex(
                'pre_matriculas_codigo_idx'
            );

            $table->dropForeign(
                ['periodo_destino_id']
            );

            $table->dropColumn([
                'periodo_destino_id',
                'codigo',
            ]);
        });
    }
};