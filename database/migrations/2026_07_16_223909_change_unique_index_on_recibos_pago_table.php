<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Índice de apoyo para la llave foránea sede_id
        |--------------------------------------------------------------------------
        | MySQL estaba usando el índice único compuesto como soporte de la FK.
        | Primero creamos un índice independiente para poder eliminarlo.
        */
        Schema::table('recibos_pago', function (Blueprint $table) {
            $table->index(
                'sede_id',
                'recibos_pago_sede_id_idx'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Permitir varias líneas con el mismo número de recibo
        |--------------------------------------------------------------------------
        */
        Schema::table('recibos_pago', function (Blueprint $table) {
            $table->dropUnique(
                'recibos_pago_sede_anio_numero_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 3. Crear índices normales para consultas
        |--------------------------------------------------------------------------
        */
        Schema::table('recibos_pago', function (Blueprint $table) {
            $table->index(
                ['sede_id', 'anio', 'numero_recibo'],
                'recibos_pago_sede_anio_numero_idx'
            );

            $table->index(
                ['operacion_pago_id', 'numero_recibo'],
                'recibos_pago_operacion_numero_idx'
            );
        });
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Eliminar los índices nuevos
        |--------------------------------------------------------------------------
        */
        Schema::table('recibos_pago', function (Blueprint $table) {
            $table->dropIndex(
                'recibos_pago_operacion_numero_idx'
            );

            $table->dropIndex(
                'recibos_pago_sede_anio_numero_idx'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Restaurar la restricción única anterior
        |--------------------------------------------------------------------------
        */
        Schema::table('recibos_pago', function (Blueprint $table) {
            $table->unique(
                ['sede_id', 'anio', 'numero_recibo'],
                'recibos_pago_sede_anio_numero_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 3. Quitar el índice auxiliar
        |--------------------------------------------------------------------------
        | La restricción única restaurada vuelve a servir de soporte para sede_id.
        */
        Schema::table('recibos_pago', function (Blueprint $table) {
            $table->dropIndex(
                'recibos_pago_sede_id_idx'
            );
        });
    }
};