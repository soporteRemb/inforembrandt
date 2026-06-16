<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rangos_desempeno_notas', function (Blueprint $table) {

            $table->string('convencion', 10)
                ->nullable()
                ->after('nombre');

            $table->string('descripcion_convencion', 255)
                ->nullable()
                ->after('convencion');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rangos_desempeno_notas', function (Blueprint $table) {

            $table->dropColumn([
                'convencion',
                'descripcion_convencion',
            ]);

        });
    }
};