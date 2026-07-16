<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_cartera_estudiante', function (Blueprint $table) {
            $table->text('motivo_reversion')
                ->nullable()
                ->after('reversado_en');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_cartera_estudiante', function (Blueprint $table) {
            $table->dropColumn('motivo_reversion');
        });
    }
};