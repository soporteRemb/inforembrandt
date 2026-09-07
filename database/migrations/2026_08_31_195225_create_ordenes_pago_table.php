<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_pago', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identificación de la orden
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->unsignedSmallInteger('anio');

            $table->unsignedBigInteger('consecutivo');

            /*
             * Referencia visible para acudiente, colegio,
             * conciliación e integración con proveedores.
             *
             * Ejemplo:
             * 2026-00000001235
             */
            $table->string('referencia', 30)
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | Quién creó la orden
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('guardian_id')
                ->constrained('guardians')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Contexto institucional
            |--------------------------------------------------------------------------
            |
            | La sede NO forma parte de la referencia visible.
            | Se conserva únicamente para trazabilidad interna.
            |--------------------------------------------------------------------------
            */

            $table->foreignId('sede_id')
                ->constrained('sedes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('periodo_lectivo_id')
                ->constrained('periodos_lectivos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Método y estado
            |--------------------------------------------------------------------------
            |
            | Inicialmente:
            | metodo_pago = pse
            |
            | Estados previstos:
            | pendiente
            | procesando
            | aprobada
            | rechazada
            | cancelada
            | expirada
            |--------------------------------------------------------------------------
            */

            $table->string('metodo_pago', 50)
                ->default('pse');

            $table->string('estado', 30)
                ->default('pendiente');

            /*
            |--------------------------------------------------------------------------
            | Valores monetarios
            |--------------------------------------------------------------------------
            |
            | Se almacenan SIEMPRE con decimales.
            | Visualmente se mostrarán sin decimales:
            |
            | BD:       630000.00
            | Pantalla: $ 630.000
            |--------------------------------------------------------------------------
            */

            $table->decimal('subtotal', 15, 2)
                ->default(0);

            $table->decimal('total', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Información del proveedor de pagos
            |--------------------------------------------------------------------------
            |
            | Se dejan genéricos para no amarrar la arquitectura
            | exclusivamente a PSE ni a un proveedor específico.
            |--------------------------------------------------------------------------
            */

            $table->string('proveedor', 100)
                ->nullable();

            $table->string('referencia_proveedor', 191)
                ->nullable();

            $table->string('transaccion_proveedor', 191)
                ->nullable();

            $table->json('respuesta_proveedor')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Fechas del ciclo de vida
            |--------------------------------------------------------------------------
            */

            $table->timestamp('iniciada_en')
                ->nullable();

            $table->timestamp('confirmada_en')
                ->nullable();

            $table->timestamp('rechazada_en')
                ->nullable();

            $table->timestamp('expira_en')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Restricciones e índices
            |--------------------------------------------------------------------------
            */

            /*
             * El consecutivo se controla por año.
             *
             * Ejemplo:
             * 2026 + 1235 = 2026-00000001235
             */
            $table->unique(
                [
                    'anio',
                    'consecutivo',
                ],
                'ordenes_pago_anio_consecutivo_unique'
            );

            /*
             * Facilita consultar las órdenes activas o históricas
             * creadas por una cuenta del portal.
             */
            $table->index(
                [
                    'user_id',
                    'estado',
                ],
                'ordenes_pago_user_estado_index'
            );

            /*
             * Facilita consultar las órdenes asociadas
             * directamente al acudiente.
             */
            $table->index(
                [
                    'guardian_id',
                    'estado',
                ],
                'ordenes_pago_guardian_estado_index'
            );

            /*
             * Aunque la numeración es global y no contiene sede,
             * internamente conservamos sede y periodo para trazabilidad.
             */
            $table->index(
                [
                    'sede_id',
                    'periodo_lectivo_id',
                ],
                'ordenes_pago_sede_periodo_index'
            );

            /*
             * Útil para procesos de seguimiento,
             * conciliación y expiración de órdenes.
             */
            $table->index(
                [
                    'estado',
                    'created_at',
                ],
                'ordenes_pago_estado_fecha_index'
            );

            /*
             * Índices preparados para consultas contra
             * el proveedor de pagos.
             */
            $table->index(
                'referencia_proveedor',
                'ordenes_pago_ref_proveedor_index'
            );

            $table->index(
                'transaccion_proveedor',
                'ordenes_pago_transaccion_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_pago');
    }
};