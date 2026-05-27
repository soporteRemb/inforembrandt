<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rangos_desempeno_notas', function (Blueprint $table) {

            $table->id();

            $table->string('nombre', 30);

            $table->decimal('desde', 5, 2);

            $table->decimal('hasta', 5, 2);

            $table->integer('orden')->default(1);

            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rango_desempeno_notas');
    }
};
