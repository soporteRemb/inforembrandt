<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_pago', function (Blueprint $table) {
            $table->dropForeign(['guardian_id']);
            $table->dropIndex('ordenes_pago_guardian_estado_index');

            $table->dropColumn('guardian_id');
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_pago', function (Blueprint $table) {
            $table->foreignId('guardian_id')
                ->nullable()
                ->after('user_id');

            $table->foreign('guardian_id')
                ->references('id')
                ->on('guardians')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(
                ['guardian_id', 'estado'],
                'ordenes_pago_guardian_estado_index'
            );
        });
    }
};