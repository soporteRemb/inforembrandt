<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asignacion_concepto_vencimientos', function (Blueprint $table) {
            if (! Schema::hasColumn('asignacion_concepto_vencimientos', 'valor')) {
                $table->decimal('valor', 12, 2)
                    ->default(0)
                    ->after('tipo_limite_extemporaneo_id');
            }

            $table->foreign('tipo_limite_extemporaneo_id', 'acv_tipo_limite_fk')
                ->references('id')
                ->on('tipo_limite_extemporaneos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asignacion_concepto_vencimientos', function (Blueprint $table) {
            $table->dropForeign('acv_tipo_limite_fk');

            $table->dropColumn([
                'tipo_limite_extemporaneo_id',
                'valor',
            ]);
        });
    }
};