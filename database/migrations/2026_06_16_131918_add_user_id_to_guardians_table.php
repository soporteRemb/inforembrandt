<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guardians', function (Blueprint $table) {

            $table->foreignId('user_id')
                ->nullable()
                ->after('student_id')
                ->constrained()
                ->nullOnDelete();

            // Optimiza las búsquedas del portal de acudientes
            $table->index(['user_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::table('guardians', function (Blueprint $table) {

            $table->dropIndex(['user_id', 'estado']);

            $table->dropForeign(['user_id']);

            $table->dropColumn('user_id');

        });
    }
};