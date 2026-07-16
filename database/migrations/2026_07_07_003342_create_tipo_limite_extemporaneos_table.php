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
        Schema::create('tipo_limite_extemporaneos', function (Blueprint $table) {
            $table->id();

            // Identificador interno del sistema
            $table->string('codigo', 30)->unique();

            // Nombre que verá el usuario
            $table->string('nombre', 60);

            // Orden de visualización (1, 2, 3...)
            $table->unsignedSmallInteger('orden');

            // Permite ocultarlo sin eliminarlo
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_limite_extemporaneos');
    }
};
