<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_cartera_estudiante', function (Blueprint $table) {

            // Número de documento relacionado (recibo, factura, nota crédito, etc.)
            $table->string('documento_referencia', 100)
                ->nullable()
                ->after('referencia');

            // Fecha real del movimiento financiero
            $table->date('fecha_movimiento')
                ->nullable()
                ->after('documento_referencia');

            // Fecha de vencimiento del concepto
            $table->date('fecha_vencimiento')
                ->nullable()
                ->after('fecha_movimiento');

            // Observaciones internas para auditoría
            $table->text('nota_interna')
                ->nullable()
                ->after('observacion');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_cartera_estudiante', function (Blueprint $table) {

            $table->dropColumn([
                'documento_referencia',
                'fecha_movimiento',
                'fecha_vencimiento',
                'nota_interna',
            ]);
        });
    }
};