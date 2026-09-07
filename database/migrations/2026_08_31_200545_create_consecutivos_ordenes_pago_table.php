<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consecutivos_ordenes_pago', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Año contable
            |--------------------------------------------------------------------------
            |
            | Cada año mantiene su propio consecutivo.
            |
            | Ejemplo:
            | 2026 -> 1235
            | 2027 -> 1
            |--------------------------------------------------------------------------
            */

            $table->unsignedSmallInteger('anio')
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | Último número utilizado
            |--------------------------------------------------------------------------
            |
            | Se guarda únicamente el número.
            |
            | La referencia visible se construirá como:
            |
            | 2026-00000001235
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('ultimo_numero')
                ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consecutivos_ordenes_pago');
    }
};