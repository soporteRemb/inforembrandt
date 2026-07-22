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
        Schema::table('sedes', function (Blueprint $table) {

            $table->text('pie_documentos')->nullable()->after('logo');

            $table->string('representante_legal')->nullable()->after('pie_documentos');

            $table->string('cargo_representante')->nullable()->after('representante_legal');

            $table->text('informacion_legal')->nullable()->after('cargo_representante');

            $table->string('prefijo_documentos',20)->nullable()->after('informacion_legal');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sedes', function (Blueprint $table) {

            $table->dropColumn([
                'pie_documentos',
                'representante_legal',
                'cargo_representante',
                'informacion_legal',
                'prefijo_documentos',
            ]);

        });
    }
};
