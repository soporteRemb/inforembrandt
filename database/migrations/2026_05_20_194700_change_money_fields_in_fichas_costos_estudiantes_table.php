<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fichas_costos_estudiantes', function (Blueprint $table) {
            $table->decimal('saldo_anterior', 15, 2)->default(0)->change();
            $table->decimal('matricula', 15, 2)->default(0)->change();
            $table->decimal('costos_academicos', 15, 2)->default(0)->change();
            $table->decimal('deudas', 15, 2)->default(0)->change();
            $table->decimal('otras_deudas', 15, 2)->default(0)->change();
            $table->decimal('abonos', 15, 2)->default(0)->change();
            $table->decimal('total_deuda', 15, 2)->default(0)->change();
            $table->decimal('pension_inicial', 15, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('fichas_costos_estudiantes', function (Blueprint $table) {
            $table->unsignedBigInteger('saldo_anterior')->default(0)->change();
            $table->unsignedBigInteger('matricula')->default(0)->change();
            $table->unsignedBigInteger('costos_academicos')->default(0)->change();
            $table->unsignedBigInteger('deudas')->default(0)->change();
            $table->unsignedBigInteger('otras_deudas')->default(0)->change();
            $table->unsignedBigInteger('abonos')->default(0)->change();
            $table->unsignedBigInteger('total_deuda')->default(0)->change();
            $table->unsignedBigInteger('pension_inicial')->default(0)->change();
        });
    }
};