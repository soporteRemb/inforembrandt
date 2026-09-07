<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Agregar sede y periodo lectivo al detalle
        |--------------------------------------------------------------------------
        |
        | Se crean nullable temporalmente para poder copiar correctamente
        | la información de las órdenes que ya existen.
        |--------------------------------------------------------------------------
        */

        Schema::table('ordenes_pago_detalles', function (Blueprint $table) {
            $table->foreignId('sede_id')
                ->nullable()
                ->after('student_id');

            $table->foreignId('periodo_lectivo_id')
                ->nullable()
                ->after('sede_id');
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Copiar contexto institucional existente
        |--------------------------------------------------------------------------
        |
        | Las órdenes creadas antes de esta migración tienen sede y periodo
        | en la cabecera. Copiamos esos valores a cada detalle antes de
        | eliminar las columnas originales.
        |--------------------------------------------------------------------------
        */

        DB::statement("
            UPDATE ordenes_pago_detalles opd
            INNER JOIN ordenes_pago op
                ON op.id = opd.orden_pago_id
            SET
                opd.sede_id = op.sede_id,
                opd.periodo_lectivo_id = op.periodo_lectivo_id
        ");

        /*
        |--------------------------------------------------------------------------
        | 3. Crear foreign keys e índice en el detalle
        |--------------------------------------------------------------------------
        */

        Schema::table('ordenes_pago_detalles', function (Blueprint $table) {
            $table->foreign('sede_id')
                ->references('id')
                ->on('sedes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('periodo_lectivo_id')
                ->references('id')
                ->on('periodos_lectivos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(
                ['sede_id', 'periodo_lectivo_id'],
                'orden_detalles_sede_periodo_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 4. Eliminar sede y periodo de la cabecera
        |--------------------------------------------------------------------------
        |
        | IMPORTANTE:
        | primero se eliminan las foreign keys,
        | después el índice,
        | y por último las columnas.
        |--------------------------------------------------------------------------
        */

        Schema::table('ordenes_pago', function (Blueprint $table) {
            $table->dropForeign(['sede_id']);
            $table->dropForeign(['periodo_lectivo_id']);

            $table->dropIndex('ordenes_pago_sede_periodo_index');

            $table->dropColumn([
                'sede_id',
                'periodo_lectivo_id',
            ]);
        });
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Restaurar sede y periodo en la cabecera
        |--------------------------------------------------------------------------
        */

        Schema::table('ordenes_pago', function (Blueprint $table) {
            $table->foreignId('sede_id')
                ->nullable()
                ->after('guardian_id');

            $table->foreignId('periodo_lectivo_id')
                ->nullable()
                ->after('sede_id');
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Recuperar valores desde los detalles
        |--------------------------------------------------------------------------
        |
        | Para una reversión se toma el contexto del primer detalle
        | disponible de cada orden.
        |--------------------------------------------------------------------------
        */

        DB::statement("
            UPDATE ordenes_pago op
            SET
                op.sede_id = (
                    SELECT opd.sede_id
                    FROM ordenes_pago_detalles opd
                    WHERE opd.orden_pago_id = op.id
                    ORDER BY opd.id
                    LIMIT 1
                ),
                op.periodo_lectivo_id = (
                    SELECT opd.periodo_lectivo_id
                    FROM ordenes_pago_detalles opd
                    WHERE opd.orden_pago_id = op.id
                    ORDER BY opd.id
                    LIMIT 1
                )
        ");

        /*
        |--------------------------------------------------------------------------
        | 3. Restaurar foreign keys e índice de la cabecera
        |--------------------------------------------------------------------------
        */

        Schema::table('ordenes_pago', function (Blueprint $table) {
            $table->foreign('sede_id')
                ->references('id')
                ->on('sedes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('periodo_lectivo_id')
                ->references('id')
                ->on('periodos_lectivos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(
                ['sede_id', 'periodo_lectivo_id'],
                'ordenes_pago_sede_periodo_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 4. Eliminar sede y periodo del detalle
        |--------------------------------------------------------------------------
        |
        | Igual que en up():
        | primero foreign keys,
        | después índice,
        | y al final columnas.
        |--------------------------------------------------------------------------
        */

        Schema::table('ordenes_pago_detalles', function (Blueprint $table) {
            $table->dropForeign(['sede_id']);
            $table->dropForeign(['periodo_lectivo_id']);

            $table->dropIndex('orden_detalles_sede_periodo_index');

            $table->dropColumn([
                'sede_id',
                'periodo_lectivo_id',
            ]);
        });
    }
};