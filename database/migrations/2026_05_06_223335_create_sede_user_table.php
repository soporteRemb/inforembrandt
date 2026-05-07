<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sede_user')) {
            Schema::create('sede_user', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sede_id')->constrained()->cascadeOnDelete();
                $table->primary(['user_id', 'sede_id']);
            });
        }

        // Migrar datos existentes: mover sede_id actual a la tabla pivot
        DB::table('users')->whereNotNull('sede_id')->each(function ($user) {
            DB::table('sede_user')->insertOrIgnore([
                'user_id' => $user->id,
                'sede_id' => $user->sede_id,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sede_user');
    }
};
