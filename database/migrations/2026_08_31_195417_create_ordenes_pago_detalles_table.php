<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_pago_detalles', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Orden de pago
            |--------------------------------------------------------------------------
            */

            $table->foreignId('orden_pago_id')
                ->constrained('ordenes_pago')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Estudiante
            |--------------------------------------------------------------------------
            |
            | Una orden puede contener obligaciones de varios estudiantes.
            | Cada detalle siempre pertenece a un estudiante específico.
            |--------------------------------------------------------------------------
            */

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Obligación real de cartera
            |--------------------------------------------------------------------------
            |
            | Este campo permite saber exactamente qué obligación de la
            | cartera seleccionó el acudiente.
            |--------------------------------------------------------------------------
            */

            $table->foreignId('movimiento_cartera_estudiante_id')
                ->constrained('movimientos_cartera_estudiante')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Concepto de cobro
            |--------------------------------------------------------------------------
            |
            | Se conserva también directamente para facilitar consultas,
            | conciliación e historial.
            |
            | Si el concepto maestro deja de existir, la orden histórica
            | conserva su snapshot y este campo pasa a NULL.
            |--------------------------------------------------------------------------
            */

            $table->foreignId('concepto_cobro_id')
                ->nullable()
                ->constrained('concepto_cobros')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Snapshot de la obligación
            |--------------------------------------------------------------------------
            |
            | Guardamos una copia de la información visible al momento
            | de crear la orden.
            |
            | Si posteriormente cambia el nombre del concepto o alguna
            | información maestra, la orden histórica conserva lo que
            | realmente seleccionó el acudiente.
            |--------------------------------------------------------------------------
            */

            $table->string('concepto', 191);

            $table->string('descripcion', 255)
                ->nullable();

            $table->string('tipo_concepto', 50)
                ->nullable();

            $table->string('mes', 30)
                ->nullable();

            $table->unsignedTinyInteger('mes_numero')
                ->nullable();

            $table->date('fecha_vencimiento')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Valores monetarios
            |--------------------------------------------------------------------------
            |
            | Siempre se almacenan con dos decimales.
            |
            | Ejemplo BD:
            | 300000.00
            |
            | Visual:
            | $ 300.000
            |--------------------------------------------------------------------------
            */

            $table->decimal('valor_obligacion', 15, 2)
                ->default(0);

            $table->decimal('saldo_al_seleccionar', 15, 2)
                ->default(0);

            $table->decimal('valor_a_pagar', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Estado del detalle
            |--------------------------------------------------------------------------
            |
            | pendiente = todavía no existe confirmación real del pago
            | aplicado  = el dinero fue confirmado y aplicado a cartera
            | cancelado = el detalle dejó de hacer parte de una operación válida
            |--------------------------------------------------------------------------
            */

            $table->string('estado', 30)
                ->default('pendiente');

            /*
            |--------------------------------------------------------------------------
            | Aplicación posterior
            |--------------------------------------------------------------------------
            |
            | Se deja preparado para saber qué aplicación real de pago
            | terminó liquidando esta obligación.
            |
            | Permanece NULL mientras la orden esté pendiente,
            | procesando, rechazada, cancelada o expirada.
            |--------------------------------------------------------------------------
            */

            $table->foreignId('aplicacion_pago_id')
                ->nullable()
                ->constrained('aplicaciones_pago')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Restricciones e índices
            |--------------------------------------------------------------------------
            */

            /*
             * La misma obligación no puede aparecer dos veces
             * dentro de una misma orden.
             *
             * Sí puede aparecer nuevamente en otra orden distinta,
             * por ejemplo si la anterior expiró o fue rechazada.
             */
            $table->unique(
                [
                    'orden_pago_id',
                    'movimiento_cartera_estudiante_id',
                ],
                'orden_detalle_movimiento_unique'
            );

            $table->index(
                [
                    'orden_pago_id',
                    'student_id',
                ],
                'orden_detalles_orden_student_index'
            );

            $table->index(
                [
                    'student_id',
                    'estado',
                ],
                'orden_detalles_student_estado_index'
            );

            $table->index(
                [
                    'movimiento_cartera_estudiante_id',
                    'estado',
                ],
                'orden_detalles_movimiento_estado_index'
            );

            $table->index(
                'concepto_cobro_id',
                'orden_detalles_concepto_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_pago_detalles');
    }
};