
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asignacion_concepto_vencimientos', function (Blueprint $table) {

            // Permitir que el mes sea opcional.
            // Conserva los valores existentes.
            $table->string('mes', 20)
                ->nullable()
                ->change();

            // Agregar el año como campo opcional.
            // Los registros históricos tendrán NULL.
            $table->unsignedSmallInteger('anio')
                ->nullable()
                ->after('mes');
        });
    }

    public function down(): void
    {
        Schema::table('asignacion_concepto_vencimientos', function (Blueprint $table) {

            // Eliminar el campo año.
            $table->dropColumn('anio');

            // Restaurar la definición original del mes.
            // Si existen meses NULL, convertirlos antes
            // de restaurar la restricción NOT NULL.
            $table->string('mes', 20)
                ->nullable(false)
                ->change();
        });
    }
};
