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
        | 1. Agregar campos nuevos solamente si aún no existen
        |--------------------------------------------------------------------------
        | La ejecución anterior pudo haber creado estas columnas antes de fallar.
        */
        if (! Schema::hasColumn('impresiones_recibos', 'operacion_pago_id')) {
            Schema::table('impresiones_recibos', function (Blueprint $table) {
                $table->foreignId('operacion_pago_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('operaciones_pago')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('impresiones_recibos', 'motivo')) {
            Schema::table('impresiones_recibos', function (Blueprint $table) {
                $table->text('motivo')
                    ->nullable()
                    ->after('ruta_pdf');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Crear índice auxiliar para la llave foránea antigua
        |--------------------------------------------------------------------------
        | MySQL estaba usando el índice único compuesto como soporte de
        | recibo_pago_id. Este índice permite eliminar el anterior.
        */
        Schema::table('impresiones_recibos', function (Blueprint $table) {
            $table->index(
                'recibo_pago_id',
                'impresiones_recibos_recibo_pago_id_idx'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 3. Eliminar la unicidad anterior por línea de ReciboPago
        |--------------------------------------------------------------------------
        */
        Schema::table('impresiones_recibos', function (Blueprint $table) {
            $table->dropUnique(
                'impresiones_recibo_numero_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 4. Crear la unicidad correcta por operación completa
        |--------------------------------------------------------------------------
        | 0 = impresión original
        | 1, 2, 3... = R1, R2, R3...
        */
        Schema::table('impresiones_recibos', function (Blueprint $table) {
            $table->unique(
                ['operacion_pago_id', 'numero_reimpresion'],
                'impresiones_operacion_numero_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 5. Mantener recibo_pago_id solo como relación histórica opcional
        |--------------------------------------------------------------------------
        */
        Schema::table('impresiones_recibos', function (Blueprint $table) {
            $table->unsignedBigInteger('recibo_pago_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('impresiones_recibos', function (Blueprint $table) {
            $table->dropUnique(
                'impresiones_operacion_numero_unique'
            );
        });

        /*
         * Para regresar al diseño anterior, recibo_pago_id no puede tener null.
         * Este rollback solo funcionará si no existen registros nuevos con null.
         */
        Schema::table('impresiones_recibos', function (Blueprint $table) {
            $table->unsignedBigInteger('recibo_pago_id')
                ->nullable(false)
                ->change();
        });

        Schema::table('impresiones_recibos', function (Blueprint $table) {
            $table->unique(
                ['recibo_pago_id', 'numero_reimpresion'],
                'impresiones_recibo_numero_unique'
            );
        });

        Schema::table('impresiones_recibos', function (Blueprint $table) {
            $table->dropIndex(
                'impresiones_recibos_recibo_pago_id_idx'
            );
        });

        if (Schema::hasColumn('impresiones_recibos', 'operacion_pago_id')) {
            Schema::table('impresiones_recibos', function (Blueprint $table) {
                $table->dropForeign(['operacion_pago_id']);
                $table->dropColumn('operacion_pago_id');
            });
        }

        if (Schema::hasColumn('impresiones_recibos', 'motivo')) {
            Schema::table('impresiones_recibos', function (Blueprint $table) {
                $table->dropColumn('motivo');
            });
        }
    }
};