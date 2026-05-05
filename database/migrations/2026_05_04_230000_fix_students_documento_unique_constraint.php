<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Quitar el unique global en documento
            $table->dropUnique('students_documento_unique');

            // Nuevo unique compuesto: mismo documento puede existir en distintos periodos
            $table->unique(['documento', 'periodo_lectivo_id'], 'students_documento_periodo_unique');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_documento_periodo_unique');
            $table->unique('documento', 'students_documento_unique');
        });
    }
};
