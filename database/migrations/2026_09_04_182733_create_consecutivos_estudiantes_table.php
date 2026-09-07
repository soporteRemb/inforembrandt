<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consecutivos_estudiantes', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('anio')
                ->unique();

            $table->unsignedBigInteger('ultimo_consecutivo')
                ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consecutivos_estudiantes');
    }
};