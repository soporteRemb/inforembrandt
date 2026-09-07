<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_pago_detalles', function (Blueprint $table) {
            $table->foreignId('sede_id')
                ->nullable(false)
                ->change();

            $table->foreignId('periodo_lectivo_id')
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_pago_detalles', function (Blueprint $table) {
            $table->foreignId('sede_id')
                ->nullable()
                ->change();

            $table->foreignId('periodo_lectivo_id')
                ->nullable()
                ->change();
        });
    }
};